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


def main() -> int:
    for out, sources in BUNDLES.items():
        text = "\n".join((CSS / name).read_text(encoding="utf-8") for name in sources)
        result = minify(text)
        if result.count("{") != result.count("}"):
            print(f"ERROR: unbalanced braces in {out}", file=sys.stderr)
            return 1
        (BUILD / out).write_text(result, encoding="utf-8")
        print(f"{out}: {len(text.encode()) // 1024} KB -> {len(result.encode()) // 1024} KB")
    return 0


if __name__ == "__main__":
    sys.exit(main())
