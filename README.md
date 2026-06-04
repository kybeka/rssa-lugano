# RSSA Lugano

Website for the **Russian Speaking Student Association** at the Università della Svizzera italiana (USI), Lugano, Switzerland.

**Live site:** [kybeka.github.io/rssa-lugano](https://kybeka.github.io/rssa-lugano/)

---

## About

RSSA is a non-profit, non-political student association at USI Lugano that brings together Russian-speaking students and anyone who shares an interest in Russian language and culture. Open to all USI students.

- **Email:** RSSA@usi.ch
- **Telegram:** [@RSSA\_Lugano](https://t.me/RSSA_Lugano)
- **Instagram:** [@rssa\_lugano](https://instagram.com/rssa_lugano)

---

## Project

A complete redesign of the original Wix site, built as a single-file static website with no dependencies or build step. The design targets a modern Swiss institutional aesthetic — clean typography, generous whitespace, and a strong editorial identity.

### Stack

| | |
|---|---|
| HTML | Semantic, accessible markup |
| CSS | Modern CSS — OKLCH colors, `clamp()` fluid type, CSS custom properties |
| JavaScript | Vanilla JS — scroll reveal, nav state, form handling |
| Fonts | [Bricolage Grotesque](https://fonts.google.com/specimen/Bricolage+Grotesque) + [Spectral](https://fonts.google.com/specimen/Spectral) via Google Fonts |
| Social feed | [Juicer.io](https://juicer.io) embed (free tier, Telegram via rss.app) |
| Contact form | Pure `mailto:` — no third-party service required |

### Design principles

- **Swiss graphic design** — grid structure, typographic hierarchy, one accent color used sparingly
- **No framework overhead** — single HTML file, hosts anywhere for free
- **WCAG AA accessible** — skip link, semantic HTML, proper ARIA labels, focus indicators
- **Mobile first** — fluid type scale, responsive grid, 44px touch targets

---

## Development

No build step. Open `index.html` in a browser.

```bash
open index.html
```

Or serve locally with any static server:

```bash
npx serve .
# or
python3 -m http.server
```

---

## Deployment options

### GitHub Pages (free)

1. Go to **Settings → Pages**
2. Set source to **Deploy from a branch**
3. Select `main` branch, `/ (root)` folder
4. Save — the site is live at `https://kybeka.github.io/rssa-lugano/`

### Netlify (free)

Drag and drop the project folder at [netlify.com/drop](https://app.netlify.com/drop).

### Vercel (free)

```bash
npx vercel
```

---

## Contact form

The contact form uses a pure `mailto:` approach — no backend or third-party service required. When submitted, it opens the visitor's email client pre-filled with their name, reply-to address, and message. They send it from their own client directly to RSSA@usi.ch.

If no email client is configured on the visitor's device, the form displays an error message with the direct email address.

---

## License

MIT — free to use and adapt.
