import re

with open('Website-SFY/index.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

print("File size:", len(text), "bytes")

# Check key endpoints
endpoints = re.findall(r'backend/[a-zA-Z0-9_\.]+', text)
print("Backend endpoints referenced:", set(endpoints))

# Check APIs
print("iTunes API used:", 'itunes.apple.com' in text)
print("Spotify track regex:", 'spotify.com/track' in text)
print("compressImage function:", 'compressImage' in text)
