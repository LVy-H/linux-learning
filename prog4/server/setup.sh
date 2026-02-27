sssss
WP="/usr/local/bin/wp --allow-root --path=/var/www/html"

echo "[setup] Waiting for wp-config.php to appear..."
for i in $(seq 1 60); do
    if [ -f /var/www/html/wp-config.php ]; then
        echo "[setup] wp-config.php found."
        break
    fi
    echo "[setup] Attempt $i/60 – wp-config.php not ready, retrying in 5s..."
    sleep 5
done

if [ ! -f /var/www/html/wp-config.php ]; then
    echo "[setup] ERROR: wp-config.php never appeared. Exiting."
    exit 1
fi

echo "[setup] Waiting for MySQL to be reachable..."
echo "[setup] DB_HOST=${WORDPRESS_DB_HOST}  DB_NAME=${WORDPRESS_DB_NAME}  DB_USER=${WORDPRESS_DB_USER}"
for i in $(seq 1 60); do
    echo "[setup] --- Attempt $i/60 ---"
    OUTPUT=$($WP db query "SELECT 1" 2>&1)
    RC=$?
    echo "[setup] exit_code=$RC"
    echo "[setup] output: $OUTPUT"
    if [ $RC -eq 0 ]; then
        echo "[setup] MySQL is reachable."
        break
    fi
    echo "[setup] Retrying in 5s..."
    sleep 5
done

if ! $WP core is-installed 2>/dev/null; then
    echo "[setup] Running wp core install..."
    $WP core install \
        --url="http://localhost:8080" \
        --title="anhtudsyk4" \
        --admin_user="admin" \
        --admin_password="admin123QWE@AD" \
        --admin_email="admin@example.com" \
        --skip-email
    echo "[setup] Core install complete."
else
    echo "[setup] WordPress already installed, skipping core install."
fi

$WP option update blogname "anhtudsyk4"

create_user() {
    local login="$1" pass="$2" role="$3" email="$4"
    if $WP user get "$login" --field=ID >/dev/null 2>&1; then
        echo "[setup] User '$login' already exists – updating password."
        $WP user update "$login" --user_pass="$pass"
    else
        echo "[setup] Creating user '$login' (role: $role)."
        $WP user create "$login" "$email" \
            --user_pass="$pass" \
            --role="$role" \
            --display_name="$login"
    fi
}

create_user "test"     "test123QWE@AD"  "author"      "test@example.com"
create_user "teacher1" "123456a@A"      "editor"      "teacher1@example.com"
create_user "teacher2" "123456a@A"      "editor"      "teacher2@example.com"
create_user "student1" "123456a@A"      "contributor" "student1@example.com"
create_user "student2" "123456a@A"      "contributor" "student2@example.com"

$WP option update permalink_structure "/%postname%/"
$WP rewrite flush

echo ""
echo "[setup] ✓ Done!  Site is ready at http://localhost:8080"
echo "[setup]   admin    / admin123QWE@AD"
echo "[setup]   test     / test123QWE@AD"
echo "[setup]   teacher1 / 123456a@A"
echo "[setup]   student1 / 123456a@A"
