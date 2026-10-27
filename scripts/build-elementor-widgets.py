#!/usr/bin/env python3
"""Generate the Smile Elementor widget registry from the theme's shortcodes.

Every shortcode the theme and the bundled Smile Community Core plugin register
becomes an Elementor widget. For each shortcode this script records:
  - its attributes and defaults (from shortcode_atts), shown as widget controls;
  - every visitor-facing text it prints (directly or through the smile_*
    helpers it calls), shown as editable text boxes in the widget;
  - every section photo key it uses (smile_img / smile_image_by_key), shown as
    image pickers in the widget.

Output: inc/elementor/registry.php (a plain PHP array, no code). Re-run after
changing any shortcode:  python3 scripts/build-elementor-widgets.py
"""
import glob, hashlib, json, os, re, sys

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'wp-content', 'themes', 'smile-community-child')
os.chdir(ROOT)
FILES = sorted(glob.glob('inc/*.php')) + sorted(glob.glob('plugins/smile-community-core/includes/*.php'))
FILES = [f for f in FILES if not f.startswith('inc/elementor')]


def match_brace(src, i):
    """Index just after the brace that closes the '{' at src[i]. Skips strings and comments."""
    depth, n = 0, len(src)
    while i < n:
        c = src[i]
        if c in '\'"':
            q = c; i += 1
            while i < n and src[i] != q:
                i += 2 if src[i] == '\\' else 1
        elif src.startswith('//', i) or (c == '#' and not src.startswith('#[', i)):
            j = src.find('\n', i); i = n if j < 0 else j
        elif src.startswith('/*', i):
            j = src.find('*/', i + 2); i = n if j < 0 else j + 1
        elif c == '{':
            depth += 1
        elif c == '}':
            depth -= 1
            if depth == 0:
                return i + 1
        i += 1
    return n


def php_unescape(s):
    return s.replace("\\'", "'").replace('\\\\', '\\')


functions = {}   # name -> body
shortcodes = []  # (tag, file, body)
for f in FILES:
    src = open(f, encoding='utf-8').read()
    for m in re.finditer(r'\bfunction\s+([a-zA-Z0-9_]+)\s*\(', src):
        b = src.find('{', m.end())
        if b < 0:
            continue
        # make sure the '{' belongs to this declaration (only params/return type before it)
        if ';' in src[m.end():b]:
            continue
        functions.setdefault(m.group(1), src[b:match_brace(src, b)])
    for m in re.finditer(r"add_shortcode\(\s*'([a-z0-9_]+)'\s*,\s*(?:'([a-zA-Z0-9_]+)'|function\s*\()", src):
        tag, cb = m.group(1), m.group(2)
        if cb:
            shortcodes.append((tag, f, cb, None))
        else:
            b = src.find('{', m.end())
            shortcodes.append((tag, f, None, src[b:match_brace(src, b)]))

GT = re.compile(r"\b(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'(smile-community|smile-core)'\s*\)")
CALL = re.compile(r'\b(smile_[a-z0-9_]+)\s*\(')
IMG = re.compile(r"\bsmile_(?:img|image_by_key|image_url)\(\s*'([a-z0-9-]+)'")
IMG_ARR = re.compile(r"array\(\s*'([a-z0-9-]+)'\s*,")
ATTS = re.compile(r"shortcode_atts\(\s*array\((.*?)\)\s*,\s*\$atts", re.S)
PAIR = re.compile(r"'([a-z0-9_]+)'\s*=>\s*('(?:[^'\\]|\\.)*'|-?\d+(?:\.\d+)?|true|false|__\(\s*'(?:[^'\\]|\\.)*'\s*,\s*'[a-z-]+'\s*\))")
image_keys = {i['key'] for i in json.load(open('demo/images.json'))}

# Helpers whose text is global chrome (header/footer/brand), not part of a section.
SKIP_HELPERS = {'smile_brand_logo', 'smile_social_icons', 'smile_text_key', 'smile_text_overrides',
                'smile_image_overrides', 'smile_setting', 'smile_core_get', 'smile_tel_href', 'smile_link'}


def closure(body, seen):
    texts, imgs = [], []
    for m in GT.finditer(body):
        texts.append((php_unescape(m.group(1)), m.group(2)))
    imgs += IMG.findall(body)
    imgs += [k for k in IMG_ARR.findall(body) if k in image_keys]
    for name in CALL.findall(body):
        if name in seen or name in SKIP_HELPERS or name not in functions:
            continue
        seen.add(name)
        t, i = closure(functions[name], seen)
        texts += t; imgs += i
    return texts, imgs

GROUPS = [
    ('Giving', ['mpesa', 'bank', 'online', 'tiers', 'gift', 'use', 'trust', 'urgent_need']),
    ('Careers', ['careers', 'job']),
    ('Contact and visits', ['contact', 'next_steps', 'visit', 'directions', 'map']),
    ('Get involved', ['involve', 'roles', 'lookfor', 'intl', 'partner', 'volunteer']),
    ('Safeguarding', ['commitments', 'urgent', 'concern', 'steps']),
    ('About and story', ['mv', 'objectives', 'dream', 'founder', 'journey', 'intro']),
]


def group_for(tag):
    t = tag[len('smile_'):]
    for g, keys in GROUPS:
        if any(t == k or t.startswith(k) for k in keys):
            return g
    return 'Sections'


TITLES = {'smile_mv': 'Mission and vision', 'smile_mpesa': 'M-Pesa giving box', 'smile_gift_calc': 'Gift calculator',
          'smile_hero_slider': 'Hero slider', 'smile_form': 'Enquiry form', 'smile_setting': 'Centre detail',
          'smile_faq': 'FAQ', 'smile_careers_faq': 'Careers FAQ', 'smile_intl': 'International volunteers',
          'smile_lookfor': 'What we look for', 'smile_wall': 'Wall of hope gallery', 'smile_glance': 'Programme at a glance',
          'smile_pull': 'Pull quote', 'smile_cta': 'Call to action band'}


def title_for(tag):
    return TITLES.get(tag, tag[len('smile_'):].replace('_', ' ').capitalize())

out = []
for tag, f, cb, body in shortcodes:
    if body is None:
        body = functions.get(cb, '')
    texts, imgs = closure(body, set([cb] if cb else []))
    seen, uniq = set(), []
    for t, d in texts:
        k = hashlib.md5((d + '|' + t).encode('utf-8')).hexdigest()[:16]
        if k in seen or not t.strip():
            continue
        seen.add(k); uniq.append({'k': k, 'd': d, 't': t})
    atts = []
    m = ATTS.search(body)
    if m:
        for a, v in PAIR.findall(m.group(1)):
            if v.startswith("'"):
                v = php_unescape(v[1:-1])
            elif v.startswith('__('):
                v = php_unescape(re.match(r"__\(\s*'((?:[^'\\]|\\.)*)'", v).group(1))
            atts.append({'n': a, 'v': v})
    imgs = [i for i in dict.fromkeys(imgs) if i in image_keys]
    out.append({'tag': tag, 'title': title_for(tag), 'group': group_for(tag), 'file': f,
                'atts': atts, 'texts': uniq, 'images': imgs})

# Built-in header and footer as widgets, for Elementor header/footer templates.
for part, title in (('header', 'Site header'), ('footer', 'Site footer')):
    f = 'template-parts/site-%s.php' % part
    texts, imgs = closure(open(f, encoding='utf-8').read(), set())
    seen, uniq = set(), []
    for t, d in texts:
        k = hashlib.md5((d + '|' + t).encode('utf-8')).hexdigest()[:16]
        if k not in seen and t.strip():
            seen.add(k); uniq.append({'k': k, 'd': d, 't': t})
    out.append({'tag': 'smile_site_' + part, 'title': title, 'group': 'Header and footer', 'file': f,
                'part': 'template-parts/site-' + part, 'atts': [], 'texts': uniq, 'images': []})

out.sort(key=lambda w: (w['group'], w['tag']))


def php(v, ind=1):
    p = '\t' * ind
    if isinstance(v, dict):
        return 'array(\n' + ''.join('%s\t%s => %s,\n' % (p, php(k), php(x, ind + 1)) for k, x in v.items()) + p + ')'
    if isinstance(v, list):
        if not v:
            return 'array()'
        return 'array(\n' + ''.join('%s\t%s,\n' % (p, php(x, ind + 1)) for x in v) + p + ')'
    return "'" + str(v).replace('\\', '\\\\').replace("'", "\\'") + "'"

os.makedirs('inc/elementor', exist_ok=True)
with open('inc/elementor/registry.php', 'w', encoding='utf-8') as fh:
    fh.write("<?php\n/**\n * Smile Elementor widget registry. GENERATED by scripts/build-elementor-widgets.py:\n"
             " * do not edit by hand; re-run the script after changing a shortcode.\n *\n * @package SmileCommunity\n */\n\n"
             "defined( 'ABSPATH' ) || exit;\n\nreturn " + php(out, 0) + ";\n")
n_t = sum(len(w['texts']) for w in out)
print('Elementor registry: %d widgets, %d editable texts, %d photo slots' % (len(out), n_t, sum(len(w['images']) for w in out)))
if len(out) < 59:
    sys.exit('Expected at least 50 shortcodes, found %d' % len(out))

# One small class per widget (Elementor needs a class per widget type).
with open('inc/elementor/widget-classes.php', 'w', encoding='utf-8') as fh:
    fh.write("<?php\n/**\n * Smile Elementor widget classes. GENERATED by scripts/build-elementor-widgets.py:\n"
             " * do not edit by hand. Loaded only after Elementor has loaded.\n *\n * @package SmileCommunity\n */\n\n"
             "defined( 'ABSPATH' ) || exit;\n\n// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound\n")
    for w in out:
        cls = 'Smile_EW_' + w['tag'][len('smile_'):]
        fh.write("\n/** Smile widget: %s. */\nfinal class %s extends Smile_Elementor_Section_Widget {\n\tconst TAG = '%s';\n}\n" % (w['title'].replace('*/', ''), cls, w['tag']))
print('Widget classes written: %d' % len(out))
