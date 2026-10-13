#!/usr/bin/env python3
"""Collect every visitor-facing text string in the theme and the bundled plugin
into demo/strings.json, so Smile Centre > Site text can list them for editing.

Run automatically by build-theme-zip.sh. Each entry: text, domain, file, section.
"""
import glob, json, os, re

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'wp-content', 'themes', 'smile-community-child')
os.chdir(ROOT)

CALL = re.compile(
    r"(?:^|[^A-Za-z0-9_])(?:__|esc_html__|esc_html_e|esc_attr__|esc_attr_e|_e)\(\s*"
    r"(?:'((?:[^'\\]|\\.)*)'|\"((?:[^\"\\]|\\.)*)\")\s*,\s*'(smile-community|smile-core)'"
)
SECTION = re.compile(r"(?:add_shortcode\(\s*'(smile_[a-z_]+)'|function\s+(smile_[a-z_]+)\s*\()")

# Admin-only screens are not visitor text; skip them.
SKIP = ('inc/wizard.php', 'inc/plugins.php', 'inc/importer.php', 'inc/content-editor.php', 'inc/security.php',
        'plugins/smile-community-core/includes/settings.php', 'plugins/smile-community-core/includes/inquiries.php',
        'plugins/smile-community-core/includes/dashboard.php', 'plugins/smile-community-core/includes/redirects.php',
        'plugins/smile-community-core/includes/media-privacy.php')

files = sorted(glob.glob('inc/*.php') + glob.glob('template-parts/*.php') + glob.glob('*.php') +
               glob.glob('plugins/smile-community-core/includes/*.php'))
out, seen = [], set()
for f in files:
    if f in SKIP:
        continue
    src = open(f, encoding='utf-8').read()
    marks = [(m.start(), m.group(1) or m.group(2)) for m in SECTION.finditer(src)]
    for m in CALL.finditer(src):
        raw = m.group(1) if m.group(1) is not None else m.group(2)
        if m.group(1) is not None:
            text = raw.replace("\\'", "'").replace('\\\\', '\\')
        else:
            text = raw.replace('\\"', '"').replace('\\\\', '\\')
        domain = m.group(3)
        if not text.strip() or (domain, text) in seen:
            continue
        seen.add((domain, text))
        section = ''
        for pos, name in marks:
            if pos < m.start():
                section = name
            else:
                break
        if not section:
            section = os.path.splitext(os.path.basename(f))[0]
        out.append({'text': text, 'domain': domain, 'file': f, 'section': section})

json.dump(out, open('demo/strings.json', 'w'), indent=0, ensure_ascii=False)
print('Site text strings: %d' % len(out))
