#!/bin/bash

TARGET="/etc"
OUT_LOG="/var/log/checketc.log"
STATE_FILE="/var/lib/checketc.state"
ADMIN_EMAIL="root@localhost"
CURR_STATE="/tmp/checketc.state"
EMAIL_CONTENT="/tmp/email_content.tmp"

touch $OUT_LOG

find "$TARGET" -type f -exec md5sum {} + 2>/dev/null | sort > "$CURR_STATE"

if [ ! -f "$STATE_FILE" ]; then
	mv "$CURR_STATE" "$STATE_FILE"
	echo "$(date) Khoi tao log." >> $OUT_LOG
	exit 0
fi

HAS_CHANGES=false

echo "Report - Date $(date)" > $EMAIL_CONTENT
echo "---" >> $EMAIL_CONTENT
NEW_FILES=$(comm -13 <(awk '{print $2}' "$STATE_FILE") <(awk '{print $2}' "$CURR_STATE"))
if [ ! -z "$NEW_FILES" ]; then
	HAS_CHANGES=true
	echo -e "\nNew files: " >> $EMAIL_CONTENT
	echo "$(date): New files detected." >> $OUT_LOG
	for file in $NEW_FILES; do
		echo "- $file" >> $EMAIL_CONTENT
		echo -e "\t$file" >> $OUT_LOG
		if file "$file" | grep -qE 'text'; then
			head -n 10 "$file" >> $EMAIL_CONTENT
			head -n 10 "$file" >> $OUT_LOG
		fi
	done
fi

CHANGED=$(join -1 2 -2 2 "$STATE_FILE" "$CURR_STATE" | awk '$2 != $3 {print $1}')

if [ ! -z "$CHANGED" ]; then
	echo -e "\nFiles has changed: " >> $EMAIL_CONTENT
	echo "$CHANGED" >> $EMAIL_CONTENT
	echo "$(date): Files modified: $CHANGED" >> "$OUT_LOG"
fi

DELETED=$(comm -23 <(awk '{print $2}' "$STATE_FILE") <(awk '{print $2}' "$CURR_STATE"))
if [ ! -z "$DELETED" ]; then
	HAS_CHANGES=true
	echo -e "\nFiles deleted: " >> $EMAIL_CONTENT
	echo "$DELETED" >> $EMAIL_CONTENT
	echo "$(date): File deleted: $DELETED." >> $OUT_LOG
fi

if [ "$HAS_CHANGES" = true ]; then
	mail -s "Detected changes on /etc of $(hostname)" "$ADMIN_EMAIL" < $EMAIL_CONTENT
	cp "$CURR_STATE" "$STATE_FILE"
fi

rm -f "$CURR_STATE" "$EMAIL_CONTENT" 
