import sys
import os
import json
import urllib.request
import yt_dlp

def fetch_with_ytdlp(url, target_dir, proxy=None, cookiefile=None):
    try:
        # Resolve absolute paths
        target_dir = os.path.abspath(target_dir)
        if cookiefile:
            cookiefile = os.path.abspath(cookiefile)
            
        if not os.path.exists(target_dir):
            os.makedirs(target_dir, exist_ok=True)

        ydl_opts = {
            'format': 'best',
            'outtmpl': os.path.join(target_dir, 'ytdl_%(id)s.%(ext)s'),
            'noplaylist': True,
            'quiet': True,
            'no_warnings': True,
            'user_agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'merge_output_format': 'mp4',
            'proxy': proxy if proxy else None,
            'cookiefile': cookiefile if cookiefile and os.path.exists(cookiefile) else None,
            'extractor_args': {'instagram': {'get_test': True}},
            'postprocessors': [{
                'key': 'FFmpegVideoRemuxer',
                'preferedformat': 'mp4',
            }],
        }

        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            # Extract info FIRST (without downloading) to get metadata
            info = ydl.extract_info(url, download=False)
            
            title = info.get('title', '')
            thumbnail = info.get('thumbnail', '')
            
            # Output metadata as JSON line for PHP to parse
            meta = {'title': title, 'thumbnail': thumbnail}
            print("META_JSON:" + json.dumps(meta))
            
            # Now actually download
            ydl.download([url])
            
            # Try to determine the actual output filename
            filename = ydl.prepare_filename(info)
            # Normalize extension in case remux changed it
            base = os.path.splitext(filename)[0]
            for ext in ['mp4', 'webm', 'mkv']:
                candidate = base + '.' + ext
                if os.path.exists(candidate):
                    print("DOWNLOADED_FILE:" + candidate)
                    break
            
        print("yt-dlp: Success!")
        return True
            
    except Exception as e:
        print(f"yt-dlp Library Error: {str(e)}")
        try:
            print("yt-dlp: Retrying without remux...")
            ydl_opts_simple = {
                'format': 'best[ext=mp4]/best',
                'outtmpl': os.path.join(target_dir, 'ytdl_%(id)s.%(ext)s'),
                'noplaylist': True,
                'nocheckcertificate': True,
                'quiet': True,
                'user_agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
            }
            with yt_dlp.YoutubeDL(ydl_opts_simple) as ydl:
                info = ydl.extract_info(url, download=False)
                meta = {'title': info.get('title', ''), 'thumbnail': info.get('thumbnail', '')}
                print("META_JSON:" + json.dumps(meta))
                ydl.download([url])
            print("yt-dlp: Simple download success!")
            return True
        except Exception as e2:
            print(f"yt-dlp Retry Error: {str(e2)}")
            return False

if __name__ == "__main__":
    if len(sys.argv) > 2:
        url = sys.argv[1]
        target_dir = sys.argv[2]
        proxy = sys.argv[3] if len(sys.argv) > 3 and sys.argv[3].strip() != "" else None
        cookiefile = sys.argv[4] if len(sys.argv) > 4 and sys.argv[4].strip() != "" else None
        fetch_with_ytdlp(url, target_dir, proxy, cookiefile)
    else:
        print("yt-dlp Bridge: Missing arguments.")
