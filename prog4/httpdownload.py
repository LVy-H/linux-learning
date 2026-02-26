import socket
import os
from urllib.parse import urlparse, urlencode
from argparse import ArgumentParser

def main():
    parser = ArgumentParser()
    parser.add_argument('--url',        required=True)
    parser.add_argument('--remote-file', required=True, dest='remote_file')
    args = parser.parse_args()
    url = urlparse(args.url)
    host = url.hostname
    port = url.port if url.port else 80

    request = f"GET {args.remote_file} HTTP/1.1\r\nHost: {host}:{port}\r\nConnection: close\r\n\r\n".encode()
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.connect((host, port))
        s.sendall(request)
        response = b''
        while True:
            data = s.recv(4096)
            if not data:
                break
            response += data
    header_block, _, body = response.partition(b'\r\n\r\n')
    status_line = header_block.split(b'\r\n')[0].decode()
    status_code = int(status_line.split(' ')[1])
    if status_code == 200:
        filename = os.path.basename(args.remote_file)
        with open(filename, 'wb') as f:
            f.write(body)
        print(f"File downloaded successfully: {filename}")
    else:
        print(f"Failed to download file. HTTP status code: {status_code}")

if __name__ == "__main__":
    main()