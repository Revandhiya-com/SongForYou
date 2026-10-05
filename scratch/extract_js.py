with open('Website-SFY/index.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

# Extract script tag content
import re
scripts = re.findall(r'<script>(.*?)</script>', text, re.DOTALL)
print("Found scripts:", len(scripts))

with open('scratch/extracted_js.js', 'w', encoding='utf-8') as f_out:
    for idx, s in enumerate(scripts):
        f_out.write(f"// --- SCRIPT BLOCK {idx+1} ---\n")
        f_out.write(s)
        f_out.write("\n\n")

print("Saved JS to scratch/extracted_js.js, length:", len('\n'.join(scripts)))
