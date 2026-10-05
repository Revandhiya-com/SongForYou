import subprocess

# Run git show 9b0e4d8:Website-SFY/index.php
res = subprocess.run(['git', 'show', '9b0e4d8:Website-SFY/index.php'], capture_output=True, text=True, encoding='utf-8', errors='ignore')
old_index = res.stdout

with open('scratch/old_index.php', 'w', encoding='utf-8') as f:
    f.write(old_index)

print("Saved old index.php, length:", len(old_index))
