import socket
import os
import re
import random
import string
from urllib.parse import urlparse, urlencode
from argparse import ArgumentParser


def random_boundary():
    return '----Boundary' + ''.join(random.choices(string.ascii_letters + string.digits, k=16))


def send(host, port, request):
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.connect((host, port))
        s.sendall(request)
        data = b''
        while True:
            chunk = s.recv(4096)
            if not chunk:
                break
            data += chunk
    return data


def parse_response(response):
    head, _, body = response.partition(b'\r\n\r\n')
    status = int(head.split(b'\r\n')[0].decode().split()[1])
    return status, body


def collect_cookies(response):
    head, _, _ = response.partition(b'\r\n\r\n')
    cookies = {}
    for line in head.split(b'\r\n'):
        if line.lower().startswith(b'set-cookie:'):
            pair = line.split(b':', 1)[1].split(b';')[0].strip()
            k, _, v = pair.partition(b'=')
            cookies[k.decode()] = v.decode()
    return cookies


def cookie_str(cookies):
    return '; '.join(f'{k}={v}' for k, v in cookies.items())


def main():
    parser = ArgumentParser()
    parser.add_argument('--url',        required=True)
    parser.add_argument('--user',       required=True)
    parser.add_argument('--password',   required=True)
    parser.add_argument('--local-file', required=True, dest='local_file')
    args = parser.parse_args()

    if not os.path.exists(args.local_file):
        print(f"Upload failed. Local file not found: {args.local_file}")
        return

    url      = urlparse(args.url)
    host     = url.hostname
    port     = url.port or 80
    filename = os.path.basename(args.local_file)

    login_body = urlencode({
        'log': args.user, 'pwd': args.password,
        'wp-submit': 'Log In', 'redirect_to': '/wp-admin/', 'testcookie': '1',
    }).encode()
    login_req = (
        f"POST /wp-login.php HTTP/1.1\r\nHost: {host}:{port}\r\nConnection: close\r\n"
        f"Content-Type: application/x-www-form-urlencoded\r\n"
        f"Cookie: wordpress_test_cookie=WP+Cookie+check\r\n"
        f"Content-Length: {len(login_body)}\r\n\r\n"
    ).encode() + login_body

    login_resp = send(host, port, login_req)
    status, _ = parse_response(login_resp)
    cookies = collect_cookies(login_resp)

    if status not in (301, 302) or not any('logged_in' in k for k in cookies):
        print("Upload failed. Login unsuccessful.")
        return

    nonce_req = (
        f"GET /wp-admin/media-new.php HTTP/1.1\r\nHost: {host}:{port}\r\nConnection: close\r\n"
        f"Cookie: {cookie_str(cookies)}\r\n\r\n"
    ).encode()
    _, nonce_body = parse_response(send(host, port, nonce_req))
    m = re.search(r'"_wpnonce"\s*value="([^"]+)"', nonce_body.decode(errors='replace'))
    if not m:
        print("Upload failed. Could not retrieve nonce.")
        return
    nonce = m.group(1)

    file_bytes = open(args.local_file, 'rb').read()
    boundary   = random_boundary()
    upload_body = (
        f'--{boundary}\r\nContent-Disposition: form-data; name="name"\r\n\r\n{filename}\r\n'
        f'--{boundary}\r\nContent-Disposition: form-data; name="action"\r\n\r\nupload-attachment\r\n'
        f'--{boundary}\r\nContent-Disposition: form-data; name="_wpnonce"\r\n\r\n{nonce}\r\n'
        f'--{boundary}\r\nContent-Disposition: form-data; name="async-upload"; filename="{filename}"\r\n'
        f'Content-Type: application/octet-stream\r\n\r\n'
    ).encode() + file_bytes + f'\r\n--{boundary}--\r\n'.encode()

    upload_req = (
        f"POST /wp-admin/async-upload.php HTTP/1.1\r\nHost: {host}:{port}\r\nConnection: close\r\n"
        f"Cookie: {cookie_str(cookies)}\r\n"
        f"Content-Type: multipart/form-data; boundary={boundary}\r\n"
        f"Content-Length: {len(upload_body)}\r\n\r\n"
    ).encode() + upload_body

    status, resp_body = parse_response(send(host, port, upload_req))
    resp_str = resp_body.decode(errors='replace')

    if status == 200:
        m = re.search(r'"url"\s*:\s*"([^"]+)"', resp_str)
        print("Upload success. File upload url:")
        print(m.group(1).replace('\\/', '/') if m else '(url not found in response)')
    else:
        print(f"Upload failed. (HTTP {status})")
        m = re.search(r'"message"\s*:\s*"([^"]+)"', resp_str)
        if m:
            print(f"Reason: {m.group(1)}")


if __name__ == '__main__':
    main()


