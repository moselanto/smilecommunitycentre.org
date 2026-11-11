#!/usr/bin/env python3
"""Front-page CSS purge (2.53.5).

Builds assets/build/main-front.min.css and home-front.min.css from main.min.css and
home.min.css, keeping only rules whose classes/ids appear on the live Home page, in the
theme/Core JavaScript, or in the safelist. Only the front page uses the -front files
(inc/setup.php); every other page keeps the full files.

  python3 scripts/purge-front-css.py --refresh   # re-read Home from the live site (needs internet)
  python3 scripts/purge-front-css.py             # rebuild from scripts/front-used-tokens.txt

Re-run with --refresh (then build-theme-zip.sh) after changing the Home page in Elementor.
"""
import os, re, sys, urllib.request

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..")
T = os.path.join(ROOT, "wp-content", "themes", "smile-community-child")
B = os.path.join(T, "assets", "build")
TOKENS = os.path.join(ROOT, "scripts", "front-used-tokens.txt")
SITE = "https://smilecommunitycentre.org/"
UAS = ["Mozilla/5.0 (Linux; Android 11; moto g power) Mobile Chrome/130",
       "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130 Safari/537.36"]
JS = [os.path.join(T, "assets", "js", "main.js"), os.path.join(T, "assets", "js", "slider.js"),
      os.path.join(T, "plugins", "smile-community-core", "assets", "donate.js")]
# State/context classes added by WordPress, Elementor or scripts at runtime.
SAFE = re.compile(r"^(?:admin-bar|logged-in|customize-|elementor-editor|elementor-edit|e-|is-|has-|js|no-js|smile-nav-open|smile-js|screen-reader|wp-|swiper|dialog|lightbox|elementor-lightbox|elementor-motion|animated|fadeIn|elementor-invisible|ready|loaded|active|open)")

def html_tokens(html):
    toks = set()
    for v in re.findall(r'\bclass\s*=\s*"([^"]*)"', html) + re.findall(r"\bclass\s*=\s*'([^']*)'", html):
        toks.update(v.split())
    toks.update(re.findall(r'\bid\s*=\s*"([^"]+)"', html))
    for s in re.findall(r"<script\b[^>]*>(.*?)</script>", html, re.S):
        toks.update(re.findall(r"[A-Za-z_][\w-]{1,60}", s))
    return toks

def refresh():
    toks = set()
    for i, ua in enumerate(UAS):
        req = urllib.request.Request(SITE + "?purge=" + str(i), headers={"User-Agent": ua})
        toks |= html_tokens(urllib.request.urlopen(req, timeout=60).read().decode("utf-8", "replace"))
    with open(TOKENS, "w") as f:
        f.write("\n".join(sorted(toks)) + "\n")
    return toks

def load():
    toks = set(open(TOKENS).read().split())
    for p in JS:
        if os.path.exists(p):
            toks.update(re.findall(r"[A-Za-z_][\w-]{1,60}", open(p).read()))
    return toks

def blocks(css):
    i, n = 0, len(css)
    while i < n:
        j = css.find("{", i)
        if j < 0:
            return
        k, depth = j + 1, 1
        while k < n and depth:
            depth += {"{": 1, "}": -1}.get(css[k], 0)
            k += 1
        yield css[i:j].strip(), css[j + 1:k - 1]
        i = k

def strip_fn(sel, names):
    # remove :not(...)/:is(...)/:where(...) arguments (they never make a selector impossible)
    out, i = "", 0
    while i < len(sel):
        m = re.match(r":(%s)\(" % names, sel[i:])
        if m:
            depth, k = 1, i + len(m.group(0))
            while k < len(sel) and depth:
                depth += {"(": 1, ")": -1}.get(sel[k], 0)
                k += 1
            i = k
            continue
        out += sel[i]
        i += 1
    return out

def selector_used(sel, toks):
    s = strip_fn(sel, "not|is|where|matches|-webkit-any")
    s = re.sub(r"\[[^\]]*\]", "", s)  # attribute selectors
    s = re.sub(r"url\([^)]*\)", "", s)
    for kind, name in re.findall(r"([.#])(-?[_a-zA-Z][\w-]*)", s):
        if name not in toks and not SAFE.match(name):
            return False
    return True

def purge(css, toks):
    out = []
    for pre, body in blocks(css):
        if pre.startswith("@media") or pre.startswith("@supports") or pre.startswith("@container") or pre.startswith("@layer"):
            inner = purge(body, toks)
            if inner:
                out.append(pre + "{" + inner + "}")
        elif pre.startswith("@"):
            out.append(pre + "{" + body + "}")
        else:
            kept = [s for s in pre.split(",") if s.strip() and selector_used(s.strip(), toks)]
            if kept:
                out.append(",".join(kept) + "{" + body + "}")
    return "".join(out)

if __name__ == "__main__":
    if "--refresh" in sys.argv or not os.path.exists(TOKENS):
        refresh()
    toks = load()
    for src, dst in (("main.min.css", "main-front.min.css"), ("home.min.css", "home-front.min.css")):
        css = open(os.path.join(B, src)).read()
        new = purge(css, toks)
        with open(os.path.join(B, dst), "w") as f:
            f.write(new)
        print("%s: %d KB -> %s %d KB" % (src, len(css) // 1024, dst, len(new) // 1024))
