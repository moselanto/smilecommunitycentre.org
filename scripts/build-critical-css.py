#!/usr/bin/env python3
"""Build critical (above-the-fold) CSS for the opt-in lean asset mode (SMILE_LEAN_ASSETS).

Keeps only the rules that style what is visible before scrolling on phones and desktops:
base elements, fonts, the top bar, header and menu drawer, buttons, the home slider and
the inner-page banner (pagehero/breadcrumbs). Whole rules are kept when any selector in
the rule matches, and @media/@supports blocks are kept around matching rules, so the
inlined CSS renders the first screen exactly like the full stylesheet (no layout jump).

Output:
  assets/build/critical-home.min.css   (main + home stylesheets, home page)
  assets/build/critical-inner.min.css  (main + inner home bundle, every other page)
Run: python3 scripts/build-critical-css.py   (build-theme-zip.sh runs it automatically)
"""
import os
import re

T = os.path.join(os.path.dirname(__file__), "..", "wp-content", "themes", "smile-community-child", "assets", "build")

KEEP = re.compile(
    r"^(?:\*|html|body|:root|::?(?:before|after|selection)|h[1-3]|p|a|img|svg|picture|button|"
    r"\.screen-reader-text|\.skip-link|\.smile-(?:skip-link|container|topbar|header|brand|nav|ico|icon|social|btn|"
    r"pagehero|breadcrumbs|eyebrow|hero|slider|slide|el-header|el-hidden|lead|faq)(?![a-z0-9]|-[a-z0-9])|\.menu-item|\.sub-menu|\.current-menu)"
)


def blocks(css):
    """Yield (prelude, body) for each top-level block in minified CSS."""
    i, n = 0, len(css)
    while i < n:
        j = css.find("{", i)
        if j < 0:
            return
        prelude = css[i:j].strip()
        depth, k = 1, j + 1
        while k < n and depth:
            if css[k] == "{":
                depth += 1
            elif css[k] == "}":
                depth -= 1
            k += 1
        yield prelude, css[j + 1:k - 1]
        i = k


def selector_matches(prelude):
    for sel in prelude.split(","):
        sel = sel.strip()
        sel = re.sub(r"^(?:body(?:\.[\w-]+)*|html(?:\.[\w-]+)*)\s+", "", sel)  # body.home .x -> .x
        if KEEP.match(sel):
            return True
    return False


def critical(css):
    out = []
    for prelude, body in blocks(css):
        if prelude.startswith("@font-face") or prelude.startswith("@charset"):
            out.append(prelude + "{" + body + "}")
        elif prelude.startswith(("@media", "@supports", "@layer")):
            inner = critical(body)
            if inner:
                out.append(prelude + "{" + inner + "}")
        elif prelude.startswith("@"):
            continue  # @keyframes etc. are not needed for the first paint
        elif selector_matches(prelude):
            out.append(prelude + "{" + body + "}")
    return "".join(out)


def read(name):
    with open(os.path.join(T, name), encoding="utf-8") as fh:
        return fh.read()


for target, sources in (
    ("critical-home.min.css", ("main.min.css", "home.min.css")),
    ("critical-inner.min.css", ("main-inner.min.css", "home-inner.min.css")),
):
    css = critical("".join(read(s) for s in sources))
    with open(os.path.join(T, target), "w", encoding="utf-8") as fh:
        fh.write(css)
    print("%s: %d bytes" % (target, len(css)))
