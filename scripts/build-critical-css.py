#!/usr/bin/env python3
"""Build critical (above-the-fold) CSS for the opt-in lean asset mode (SMILE_LEAN_ASSETS).

Keeps only the rules that style what is visible before scrolling on phones and desktops:
base elements, fonts, the top bar, header and menu drawer, buttons, the home slider and
the inner-page banner (pagehero/breadcrumbs). Whole rules are kept when any selector in
the rule matches, and @media/@supports blocks are kept around matching rules, so the
inlined CSS renders the first screen exactly like the full stylesheet (no layout jump).

2.51.0: only the matching selectors of a rule are kept (not the whole comma list),
interaction states (:hover, :focus, open menu) are left to the full stylesheet, and
each template skips components that are never in its first screen (home: page
banner, breadcrumbs, FAQ, stories lead; inner pages: slider, FAQ).

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
    r"pagehero|breadcrumbs|eyebrow|hero|article|slider-sec|slider|slide|pillars|el-header|el-hidden|lead|faq)(?![a-z0-9]|-[a-z0-9])|\.menu-item|\.sub-menu|\.current-menu)"
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


STATE = re.compile(r":(?:hover|focus|focus-visible|focus-within|active|visited)\b|\.is-open\b|\[aria-expanded=.?true|\[open\]")

# Components that are never in the first screen of a given template (2.51.0).
SKIP = {
    "home": re.compile(r"^\.smile-(?:pagehero|breadcrumbs|faq|lead|article)(?![a-z0-9]|-[a-z0-9])"),
    "inner": re.compile(r"^\.smile-(?:slider|slide|pillars|faq)(?![a-z0-9]|-[a-z0-9])"),
}


def keep_selectors(prelude, kind):
    """Return only the selectors in this rule that style the first screen.

    Interaction states (hover, focus, open menu) are left to the full stylesheet:
    nothing is hovered or open when the page first paints.
    """
    kept = []
    for raw in prelude.split(","):
        sel = raw.strip()
        if not sel or STATE.search(sel):
            continue
        bare = re.sub(r"^(?:body(?:\.[\w-]+)*|html(?:\.[\w-]+)*)\s+", "", sel)  # body.home .x -> .x
        if KEEP.match(bare) and not SKIP[kind].match(bare):
            kept.append(sel)
    return ",".join(kept)


def selector_matches(prelude, kind="home"):
    return bool(keep_selectors(prelude, kind))


def critical(css, kind="home"):
    out = []
    for prelude, body in blocks(css):
        if prelude.startswith("@font-face") or prelude.startswith("@charset"):
            out.append(prelude + "{" + body + "}")
        elif prelude.startswith(("@media", "@supports", "@layer")):
            if "print" in prelude and "screen" not in prelude:
                continue
            inner = critical(body, kind)
            if inner:
                out.append(prelude + "{" + inner + "}")
        elif prelude.startswith("@"):
            continue  # @keyframes etc. are not needed for the first paint
        else:
            sel = keep_selectors(prelude, kind)
            if sel:
                out.append(sel + "{" + body + "}")
    return "".join(out)


def read(name):
    with open(os.path.join(T, name), encoding="utf-8") as fh:
        return fh.read()


for target, sources, kind in (
    ("critical-home.min.css", ("main.min.css", "home.min.css"), "home"),
    ("critical-inner.min.css", ("main-inner.min.css", "home-inner.min.css"), "inner"),
):
    css = critical("".join(read(s) for s in sources), kind)
    with open(os.path.join(T, target), "w", encoding="utf-8") as fh:
        fh.write(css)
    print("%s: %d bytes" % (target, len(css)))
