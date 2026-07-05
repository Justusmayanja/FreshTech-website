# PulseTech Solutions

Static website for PulseTech Solutions — a Kampala-based digital agency offering website development, custom software, and social media growth services.

## Services

1. **Website Development** — Static and dynamic websites for schools, businesses, NGOs, hospitals, e-commerce, and more
2. **Software Development** — POS systems, management systems, and custom business software
3. **Social Media Growth** — Content strategy, video production, and posting on TikTok, Instagram, Facebook, and X

## Project structure

```
pulsetech/
├── index.html
├── about.html
├── contact.html
├── services.html
├── projects.html
├── products.html
├── blog.html
├── portfolio.html          # redirects to projects.html
├── css/styles.css
├── js/
│   ├── main.js
│   └── image-modal.js
├── images/
└── services/
    ├── website-development.html
    ├── software-development.html
    └── social-media-growth.html
```

## Running locally

```bash
npx serve .
```

## Deployment

Deploy the project root to any static host (Vercel, Netlify, GitHub Pages). No server-side runtime required.
