with open('scratch/old_index.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

print("=== Check Spotify search logic in old index.php ===")
for m in re.finditer(r'spotify[a-zA-Z0-9_\.]*', text, re.IGNORECASE):
    print(m.group(0))

print("\n=== Check renderMessages / Card structure ===")
idx = text.find('function renderMessages')
if idx != -1:
    print(text[idx:idx+2500])

print("\n=== Check Song Search JS ===")
idx2 = text.find('async function searchSong')
if idx2 != -1:
    print(text[idx2:idx2+2000])
else:
    idx2 = text.find('spotify_search')
    if idx2 != -1:
        print(text[idx2-100:idx2+1000])
