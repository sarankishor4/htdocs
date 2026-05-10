import json
import os

def convert_json_to_netscape(json_file, output_file):
    try:
        with open(json_file, 'r') as f:
            cookies = json.load(f)
        
        with open(output_file, 'w') as f:
            f.write("# Netscape HTTP Cookie File\n")
            f.write("# http://curl.haxx.se/rfc/cookie_spec.html\n")
            f.write("# This is a generated file!  Do not edit.\n\n")
            
            for cookie in cookies:
                # domain - TRUE/FALSE - path - TRUE/FALSE - expiration - name - value
                domain = cookie.get('domain', '')
                flag = "TRUE" if domain.startswith('.') else "FALSE"
                path = cookie.get('path', '/')
                secure = "TRUE" if cookie.get('secure', False) else "FALSE"
                expiration = int(cookie.get('expirationDate', 0))
                name = cookie.get('name', '')
                value = cookie.get('value', '')
                
                f.write(f"{domain}\t{flag}\t{path}\t{secure}\t{expiration}\t{name}\t{value}\n")
        
        print("Success: Converted JSON cookies to Netscape format.")
    except Exception as e:
        print(f"Error: {str(e)}")

if __name__ == "__main__":
    convert_json_to_netscape('python/instagram_cookies.txt', 'python/instagram_cookies.txt')
