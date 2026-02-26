import socket
from urllib.parse import urlparse, urlencode
from argparse import ArgumentParser


def main():
    parser = ArgumentParser(description='POST login credentials to a WordPress site')
    parser.add_argument('--url', required=True, help='Base URL, e.g. http://localhost:8080/')
    parser.add_argument('--user', required=True, help='Username')
    parser.add_argument('--password', required=True, help='Password')
    args = parser.parse_args()

    url  = urlparse(args.url)
    host = url.hostname
    port = url.port if url.port else 80

    login_path = '/wp-login.php'

    body = urlencode({
        'log':         args.user,
        'pwd':         args.password,
        'wp-submit':   'Log In',
        'redirect_to': '/wp-admin/',
        'testcookie':  '1',
    }).encode()

    request = (
        f"POST {login_path} HTTP/1.1\r\n"
        f"Host: {host}:{port}\r\n"
        f"Connection: close\r\n"
        f"Content-Type: application/x-www-form-urlencoded\r\n"
        f"Cookie: wordpress_test_cookie=WP+Cookie+check\r\n"
        f"Content-Length: {len(body)}\r\n"
        f"\r\n"
    ).encode() + body

    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.connect((host, port))
        s.sendall(request)

        response = b""
        while True:
            data = s.recv(1024)
            if not data:
                break
            response += data

    header_block, _, _ = response.partition(b'\r\n\r\n')
    status_line = header_block.split(b'\r\n')[0].decode()
    status_code = int(status_line.split(' ')[1])

    location = ''
    for line in header_block.split(b'\r\n')[1:]:
        if line.lower().startswith(b'location:'):
            location = line.split(b':', 1)[1].decode().strip()
            break

    if status_code in (301, 302) and 'wp-admin' in location:
        print(f"User {args.user} đăng nhập thành công")
    else:
        print(f"User {args.user} đăng nhập thất bại")


if __name__ == "__main__":
    main()
