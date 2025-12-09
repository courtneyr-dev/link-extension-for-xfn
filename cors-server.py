#!/usr/bin/env python3
"""Simple HTTP server with CORS enabled for testing blueprints"""
from http.server import HTTPServer, SimpleHTTPRequestHandler
import sys

class CORSRequestHandler(SimpleHTTPRequestHandler):
    def end_headers(self):
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', '*')
        self.send_header('Cache-Control', 'no-store, no-cache, must-revalidate')
        super().end_headers()

    def do_OPTIONS(self):
        self.send_response(200)
        self.end_headers()

if __name__ == '__main__':
    port = 8000
    print(f'Starting CORS-enabled server on http://localhost:{port}')
    print(f'Test blueprint at: https://playground.wordpress.net/?blueprint-url=http://localhost:{port}/assets/blueprints/blueprint.json')
    print('Press Ctrl+C to stop')

    httpd = HTTPServer(('localhost', port), CORSRequestHandler)
    httpd.serve_forever()
