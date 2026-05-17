# retrouver les whitespaces et caractères invalides
with open("RAPPORT.md", "r", encoding="utf-8") as f:
    text = f.read()

for i, c in enumerate(text):
    if ord(c) == 8203:  # U+200B
        print("U+200B trouvé à la position", i)
