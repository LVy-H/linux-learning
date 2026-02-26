from pydoc import html
import socket
from urllib.parse import urlparse
from html.parser import HTMLParser
from argparse import ArgumentParser

class TitleParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.in_title = False
        self.title = ""

    def handle_starttag(self, tag, attrs):
        if tag.lower() == 'title':
            self.in_title = True

    def handle_endtag(self, tag):
        if tag.lower() == 'title':
            self.in_title = False

    def handle_data(self, data):
        if self.in_title:
            self.title += data

def main():
    parser = ArgumentParser(description='A simple HTTP client')
    parser.add_argument('--url', help='The URL to fetch')
    args = parser.parse_args()

    url = urlparse(args.url)
    host = url.hostname
    port = url.port if url.port else 80
    path = url.path if url.path else '/'
    print(f"Connecting to {host}:{port} and requesting {path}")

    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.connect((host, port))
        request = f"GET {path} HTTP/1.1\r\nHost: {host}:{port}\r\nConnection: close\r\n\r\n"
        s.sendall(request.encode())

        response = b""
        while True:
            data = s.recv(1024)
            if not data:
                break
            response += data


    response_str = response.decode()
    headers, _, body = response_str.partition('\r\n\r\n')

    title_parser = TitleParser()
    title_parser.feed(body)
    print("\nTitle:")
    print(title_parser.title)

if __name__ == "__main__":
    main()