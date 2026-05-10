import re
import os

def debug_ids():
    path = 'python/pinterest_debug.html'
    if not os.path.exists(path):
        print("Debug file missing")
        return
        
    with open(path, encoding='utf-8') as f:
        data = f.read()
        
    # Look for IDs in JSON blobs
    ids = re.findall(r'"id":"(\d+)"', data)
    print("ALL_IDS:", ids[:20])
    
    # Look for board specific mentions
    board_mentions = re.findall(r'"board", "id": "(\d+)"', data)
    print("BOARD_MENTIONS:", board_mentions)
    
    if "Hottie" in data:
        print("Found 'Hottie' in text!")
    else:
        print("'Hottie' NOT FOUND in HTML - maybe redirected to login?")

if __name__ == '__main__':
    debug_ids()
