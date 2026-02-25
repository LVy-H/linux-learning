#!/bin/bash

ADMIN_EMAIL="root@localhost"
CURSOR_FILE="/var/lib/sshmonitor.cursor"
TMP_LOG=$(mktemp)
FINAL_EMAIL=$(mktemp)

trap 'rm -f "$TMP_LOG" "$FINAL_EMAIL"' EXIT

journalctl -u ssh -u sshd --cursor-file="$CURSOR_FILE" | grep "Accepted" > "$TMP_LOG"

if [ -s "$TMP_LOG" ]; then
	echo "PHAT HIEN DANG NHAP MOI QUA SSH" > "$FINAL_EMAIL"
	echo "$(date '+%Y-%m-%d %H:%M:%S') - $(hostname)" >> "$FINAL_EMAIL"
	echo "---" >> "$FINAL_EMAIL"
	cat "$TMP_LOG" >> "$FINAL_EMAIL"

	mail -s "DANG NHAP SSH MOI TREN $(hostname)" "$ADMIN_EMAIL" < "$FINAL_EMAIL"
fi

