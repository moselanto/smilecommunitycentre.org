#!/usr/bin/env python3
"""Smile theme quality gate. Run before every build; the build stops if anything fails.

Checks: stray backslash-bang (breaks PHP/CSS), PHP syntax (php -l), JS syntax (node --check),
CSS parse errors (tinycss2), and pages.json integrity (shortcodes, photos, links, element IDs, one H1 per banner).
"""
import collections, glob, json, os, re, shutil, subprocess, sys

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'wp-content', 'themes', 'smile-community-child')
os.chdir(ROOT)
errors, notes = [], []
BB = chr(92) + chr(33)

# 1. Stray backslash-bang
for f in glob.glob('**/*', recursive=True):
    if f.endswith(('.php', '.css', '.js', '.json')) and os.path.isfile(f):
        if BB in open(f, encoding='utf-8', errors='ignore').read():
            errors.append('stray backslash-bang in ' + f)

# 2. PHP syntax
php = shutil.which('php') or ('/tmp/php' if os.path.exists('/tmp/php') else None)
if php:
    for f in glob.glob('**/*.php', recursive=True):
        r = subprocess.run([php, '-l', f], capture_output=True, text=True)
        if r.returncode:
            errors.append('PHP: ' + (r.stdout + r.stderr).strip().splitlines()[0])
else:
    notes.append('php not found: PHP syntax not checked')

# 3. JS syntax
if shutil.which('node'):
    for f in glob.glob('assets/js/*.js') + glob.glob('assets/build/*.js'):
        r = subprocess.run(['node', '--check', f], capture_output=True, text=True)
        if r.returncode:
            errors.append('JS: ' + f + ': ' + r.stderr.strip().splitlines()[0])
else:
    notes.append('node not found: JS syntax not checked')

# 4. CSS parse
try:
    import tinycss2
    def walk(rules, f):
        for r in rules:
            if r.type == 'error':
                errors.append('CSS %s line %s: %s' % (f, r.source_line, r.message))
            elif r.type == 'qualified-rule':
                for d in tinycss2.parse_declaration_list(r.content, skip_whitespace=True, skip_comments=True):
                    if d.type == 'error':
                        errors.append('CSS %s line %s: %s' % (f, d.source_line, d.message))
            elif r.type == 'at-rule' and r.content is not None and r.at_keyword in ('media', 'supports'):
                walk(tinycss2.parse_rule_list(r.content, skip_whitespace=True, skip_comments=True), f)
    for f in glob.glob('assets/css/*.css'):
        walk(tinycss2.parse_stylesheet(open(f).read(), skip_whitespace=True, skip_comments=True), f)
except ImportError:
    notes.append('tinycss2 not installed: CSS not parsed (pip install tinycss2)')

# 5. pages.json integrity
pages = json.load(open('demo/pages.json'))
images = {i['key'] for i in json.load(open('demo/images.json'))}
slugs = {p['slug'] for p in pages}
src = ''.join(open(f).read() for f in glob.glob('inc/*.php') + glob.glob('plugins/**/*.php', recursive=True))
shortcodes = set(re.findall(r"add_shortcode\(\s*'([a-z_]+)'", src))
ids = collections.Counter()
def collect(els):
    for e in els:
        ids[e['id']] += 1
        collect(e.get('elements', []))
for p in pages:
    s = json.dumps(p['elementor'])
    collect(p['elementor'])
    for sc in set(re.findall(r'\[(smile_[a-z_]+)', s)):
        if sc not in shortcodes:
            errors.append('%s: shortcode [%s] is not defined' % (p['slug'], sc))
    for k in set(re.findall(r'smile-img:([a-z0-9-]+)', s)):
        if k not in images:
            errors.append('%s: photo key %s is missing from images.json' % (p['slug'], k))
    for u in set(re.findall(r'"url": "(/[^"#?]*)', s) + re.findall(r'href=\\"(/[^"\\#?]*)', s)):
        first = u.strip('/').split('/')[0]
        if first and first not in slugs:
            errors.append('%s: link %s points to a page that does not exist' % (p['slug'], u))
    if p['elementor'] and 'smile-pagehero' in json.dumps(p['elementor'][0]):
        sizes = [w['settings'].get('header_size', 'h2') for c in p['elementor'][0]['elements'] for w in c['elements'] if w.get('widgetType') == 'heading']
        if sizes.count('h1') != 1:
            errors.append('%s: banner should have exactly one H1 (found %d)' % (p['slug'], sizes.count('h1')))
for k, v in ids.items():
    if v > 1:
        errors.append('pages.json: element id %s is used %d times' % (k, v))

# 6. Security patterns (fail the build on risky code)
SEC_RULES = [
    (r'\beval\s*\(', 'eval() is not allowed'),
    (r'\bcreate_function\s*\(', 'create_function() is not allowed'),
    (r'\bbase64_decode\s*\(', 'base64_decode() is not allowed'),
    (r'unserialize\s*\(\s*\$_', 'unserialize() of request data'),
    (r'echo\s+\$_(GET|POST|REQUEST|COOKIE|SERVER)', 'unescaped request data in output'),
    (r'\bwp_remote_get\s*\(', 'use wp_safe_remote_get() for remote URLs'),
    (r'\$wpdb->(query|get_results|get_row|get_var|get_col)\(\s*"[^"]*\$(?!wpdb)', 'SQL with a variable must use $wpdb->prepare()'),
]
for f in glob.glob('**/*.php', recursive=True):
    src_txt = open(f, encoding='utf-8', errors='ignore').read()
    for pattern, msg in SEC_RULES:
        for m in re.finditer(pattern, src_txt):
            line = src_txt.count('\n', 0, m.start()) + 1
            errors.append('SECURITY %s:%d: %s' % (f, line, msg))
for f in glob.glob('assets/js/*.js') + glob.glob('assets/admin/*.js'):
    js = open(f, encoding='utf-8', errors='ignore').read()
    for m in re.finditer(r'\.innerHTML\s*=\s*(?![\'"][^\'"+]*[\'"]\s*;)', js):
        line = js.count('\n', 0, m.start()) + 1
        errors.append('SECURITY %s:%d: innerHTML built from data (use textContent / createElement)' % (f, line))

for n in notes:
    print('note:', n)
if errors:
    print('THEME CHECK FAILED (%d):' % len(errors))
    for e in errors:
        print('  -', e)
    sys.exit(1)
print('Theme check passed: %d pages, %d shortcodes, PHP/JS/CSS clean.' % (len(pages), len(shortcodes)))
