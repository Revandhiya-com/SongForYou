import re

with open('Website-SFY/index.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

print("--- Endpoints & JS calls in index.php ---")
for line in text.splitlines():
    if 'fetch(' in line or 'submit' in line or 'get_messages' in line or 'submit_message' in line or 'spotify' in line:
        print(line[:120].strip())

