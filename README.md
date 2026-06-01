# RSSA Lugano

Website for the **Russian Speaking Student Association** at the Università della Svizzera italiana (USI), Lugano, Switzerland.

**Live site:** [rssa-lugano.github.io](https://rssa-lugano.github.io) *(update once deployed)*

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
| CSS | Modern CSS — OKLCH colors, `clamp()` fluid type, container queries, CSS custom properties |
| JavaScript | Vanilla JS — scroll reveal, nav state, form handling |
| Fonts | [Bricolage Grotesque](https://fonts.google.com/specimen/Bricolage+Grotesque) + [Spectral](https://fonts.google.com/specimen/Spectral) via Google Fonts |
| Forms | [Formspree](https://formspree.io) (free tier) |

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
4. Save — the site will be live at `https://<username>.github.io/rssa-lugano`

### Netlify (free)

Drag and drop the project folder at [netlify.com/drop](https://app.netlify.com/drop).

### Vercel (free)

```bash
npx vercel
```

---

## Wiring the contact form

The contact form uses [Formspree](https://formspree.io). To activate it:

1. Create a free account at formspree.io
2. Create a new form — copy the form ID from the endpoint URL
3. In `index.html`, find the `<form>` tag and replace `YOUR_FORM_ID`:

```html
action="https://formspree.io/f/YOUR_FORM_ID"
```

Until this is configured, the form gracefully falls back to opening a pre-filled `mailto:RSSA@usi.ch` link.

---

## Photo direction

When team photos are available, replace the typographic roster in the `#team` section with portrait cards. Recommended photography style:

> Natural window light, USI campus setting, warm film aesthetic, shallow depth of field, 35mm equivalent, candid rather than posed.

AI generation prompt: *"Graduate student portrait, natural window light, Lugano university campus, warm film photography, shallow depth of field, 35mm, thoughtful expression, no studio background"*

---

## License

MIT — free to use and adapt.
