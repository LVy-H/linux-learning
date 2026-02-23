#!/bin/bash

HOSTNAME=$(hostname)

if [ -f /etc/os-release ]; then
	. /etc/os-release
	DISTRONAME=$PRETTY_NAME
fi

# CPU Info
ARCH=$(lscpu | grep "CPU op-mode(s):" | awk -F ':' '{print $2}' | xargs)
mapfile -t cpu_hz_array < <(lscpu -p=Modelname,mhz | grep -v '^#' | awk -F',' '{ printf "%s %fMHz\n",$1, $2 }')


echo "Hostname: $HOSTNAME"
echo "Distro Name: $DISTRONAME"

echo "CPU Opcode(s): $ARCH"
for i in "${!cpu_hz_array[@]}"; do
	echo "Core $i: ${cpu_hz_array[$i]}"
done
echo ""

# RAM
free -h | awk '/^Mem:/ {print "RAM: "$3"/"$2", free: "$7}'
echo ""

# Disk statistic
echo "-------Disk space-------"
df -h --output=source,used,size,avail,pcent,target | grep "^/dev/" | while read disk used total avail pcent mounted; do
	echo "Disk name: $disk"
	echo "Free Space: $avail/$total ($used used - $pcent full)"
	echo "Mounted on: $mounted"
	echo ""
done

# IP
echo "-------IP-------"
ip -br addr show up | awk '{print $3 " - " $1}'
echo ""

# Users
echo "-------Users-------" 
getent passwd | cut -d: -f1 | sort -f | xargs
echo ""

# Procs
echo "-------Processes-------"
ps -u root -o comm= |sort |uniq|xargs # Use -u instead of -U, because -u show effective user (permission), while -U show the user started the process.
echo ""

# Ports
echo "-------Ports-------"
ss -tulpn | awk 'NR==1 {print "-1 " $0;next} {n=split($5,a,":");print a[n],$0}' | sort -n | cut -d ' ' -f2-
echo ""


# Public writable folders
echo "-------Public Writable-------"
find / -type d -perm -0002 2>/dev/null
echo ""

# Installed packages
echo "-------Installed packages-------"
apt list --installed
echo ""
