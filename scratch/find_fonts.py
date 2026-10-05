import re

with open('scratch/old_index.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

print("--- Google fonts links ---")
for m in re.finditer(r'https://fonts\.googleapis\.com[^\'">\s]+', text):
    print(m.group(0))

print("\n--- Font CSS variables & usages ---")
for line in text.splitlines():
    if 'font' in line.lower() and ('family' in line.lower() or 'var(' in line.lower() or 'import' in line.lower() or 'link' in line.lower()):
        print(line.strip())
