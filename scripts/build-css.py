#!/usr/bin/env python3
"""Rebuild the theme's minified CSS.

Run from the repository root after editing any file in assets/css/:

    python3 scripts/build-css.py

What it writes (wp-content/themes/smile-community-child/assets/build/):
    main.min.css   from assets/css/main.css            (every page)
    home.min.css   from assets/css/slider.css + home.css (pages built from Smile sections)
    login.min.css  from assets/css/login.css            (sign-in screen)

The minifier is deliberately simple and safe: it removes comments and
extra whitespace, and the space after ":" inside declarations only.
It never reorders or merges rules, so the output behaves exactly like the source.
"""
import re
import sys
from pathlib import Path

THEME = Path(__file__).resolve().parent.parent / "wp-content/themes/smile-community-child"
CSS = THEME / "assets/css"
BUILD = THEME / "assets/build"

BUNDLES = {
    "main.min.css": ["main.css"],
    "home.min.css": ["slider.css", "home.css"],
    "login.min.css": ["login.css"],
}


def minify(css: str) -> str:
    css = re.sub(r"/\*.*?\*/", "", css, flags=re.S)          # comments
    css = re.sub(r"\s+", " ", css)                            # whitespace runs
    css = re.sub(r"\s*([{};,>])\s*", r"\1", css)             # around punctuation
    # Inside declaration blocks only (no nested braces): "color: red" -> "color:red"
    css = re.sub(r"\{([^{}]*)\}", lambda m: "{" + re.sub(r":\s+", ":", m.group(1)) + "}", css)
    css = css.replace(";}", "}")
    return css.strip()


# Classes that only exist on the home page (sections in demo/pages.json "home" and the hero slider).
# Rules whose every selector needs one of them can never match on other pages, so the inner-page
# bundles (*-inner.min.css) leave them out. The home page, Elementor preview and any page that uses
# one of these sections still get the full bundles (see smile_needs_full_css() in inc/setup.php).
HOME_ONLY = re.compile(
    r"\.(?:smile-slide|smile-slider|smile-pillars|smile-intro2|smile-community2|smile-head-row|"
    r"smile-pcards?|smile-appeal|smile-founder2|smile-help-head|smile-voices2|smile-closing)"
    r"(?:__[\w-]+|--[\w-]+)?(?![\w-])|\.home(?![\w-])"
)


def split_selectors(prelude: str):
    out, depth, cur = [], 0, ""
    for ch in prelude:
        if ch in "([":
            depth += 1
        elif ch in ")]":
            depth -= 1
        if ch == "," and depth == 0:
            out.append(cur)
            cur = ""
        else:
            cur += ch
    out.append(cur)
    return [x.strip() for x in out if x.strip()]


def strip_home_only(css: str) -> str:
    """Drop rules that can only match on the home page. Needs tinycss2; returns css unchanged without it."""
    try:
        import tinycss2
    except ImportError:
        return css

    def walk(rules):
        parts = []
        for r in rules:
            if r.type == "qualified-rule":
                prelude = tinycss2.serialize(r.prelude)
                sels = split_selectors(prelude)
                if sels and all(HOME_ONLY.search(x) for x in sels):
                    continue
                parts.append(prelude + "{" + tinycss2.serialize(r.content) + "}")
            elif r.type == "at-rule" and r.content is not None and r.lower_at_keyword in ("media", "supports"):
                inner = walk(tinycss2.parse_rule_list(r.content))
                if inner.strip():
                    parts.append("@" + r.at_keyword + tinycss2.serialize(r.prelude) + "{" + inner + "}")
            elif r.type in ("qualified-rule", "at-rule"):
                parts.append(tinycss2.serialize([r]))
        return "".join(parts)

    return walk(tinycss2.parse_stylesheet(css))


INNER = {"main-inner.min.css": "main.min.css", "home-inner.min.css": "home.min.css"}


def main() -> int:
    for out, sources in BUNDLES.items():
        text = "\n".join((CSS / name).read_text(encoding="utf-8") for name in sources)
        result = minify(text)
        if result.count("{") != result.count("}"):
            print(f"ERROR: unbalanced braces in {out}", file=sys.stderr)
            return 1
        (BUILD / out).write_text(result, encoding="utf-8")
        print(f"{out}: {len(text.encode()) // 1024} KB -> {len(result.encode()) // 1024} KB")
    for out, full in INNER.items():
        sources = BUNDLES[full]
        text = "\n".join((CSS / name).read_text(encoding="utf-8") for name in sources)
        result = minify(strip_home_only(text))
        if result.count("{") != result.count("}"):
            print(f"ERROR: unbalanced braces in {out}", file=sys.stderr)
            return 1
        (BUILD / out).write_text(result, encoding="utf-8")
        print(f"{out}: {len(result.encode()) // 1024} KB (inner pages, home-only rules removed)")
    return 0


if __name__ == "__main__":
    sys.exit(main())
