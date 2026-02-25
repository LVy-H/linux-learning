#!/bin/bash

TARGET="/etc"
OUT_LOG="/var/log/checketc.log"
STATE_FILE="/var/lib/checketc.state"
ADMIN_EMAIL="root@localhost"

CURR_STATE=$(mktemp)
CHANGES_TMP=$(mktemp)
FINAL_EMAIL=$(mktemp)

trap 'rm -f "$CURR_STATE" "$CHANGES_TMP" "$FINAL_EMAIL"' EXIT

find "$TARGET" -type f -exec md5sum {} + 2>/dev/null | sort > "$CURR_STATE"

if [ ! -f "$STATE_FILE" ]; then
	mv "$CURR_STATE" "$STATE_FILE"
	echo "$(date '+%Y-%m-%d %H:%M:%S')|INIT|Khoi tao log." >> $OUT_LOG
	exit 0
fi

awk '
{
	hash = $1
    	sub(/^\\/, "", hash)

    	file = $0
    	sub(/^\\?[0-9a-f]{32} [ *]/, "", file)
}

FNR==NR { old[file] = hash; next }
{
	if (!(file in old)) {
		print "NEW|" file
	} else {
		if (old[file] != hash) {print "MODIFIED|" file }
		delete old[file]
	}
}
END {
	for (f in old) {print "DELETED|" f} 
}' "$STATE_FILE" "$CURR_STATE" > "$CHANGES_TMP"

if [ -s "$CHANGES_TMP" ]; then
	echo "Thu muc /etc bi thay doi" > "$FINAL_EMAIL"
	echo "Thoi gian: $(date '+%Y-%m-%d %H:%M:%S')" >> "$FINAL_EMAIL"
	echo "Server: $(hostname)" >> "$FINAL_EMAIL"
	echo "---" >> $FINAL_EMAIL

	while IFS="|" read -r STATUS FILE; do
		echo "$(date '+%Y-%m-%d %H:%M:%S') | $STATUS | $FILE" >> "$OUT_LOG"
		echo "$STATUS - $FILE" >> $FINAL_EMAIL
		if [ "$STATUS" = "NEW" ] && [ -f "$FILE" ] && file "$FILE" | grep -qE 'text'; then
			head -n 10 -- "$FILE" | sed 's/^/>/' >> "$FINAL_EMAIL"
		fi
	done < "$CHANGES_TMP"
	mail -s "Thay doi tai $TARGET tren $(hostname)" "$ADMIN_EMAIL" < "$FINAL_EMAIL"

	cp "$CURR_STATE" "$STATE_FILE"
fi
