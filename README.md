# PulseTech Solutions

Static website for PulseTech Solutions — a digital agency in Kampala, Uganda offering website development, custom software, graphic design, branding, and e-commerce solutions.

## Project structure

```
pulsetech/
├── index.html          # Home page
├── about.html          # About us
├── contact.html        # Contact form
├── services.html       # Services overview
├── portfolio.html      # Portfolio & case studies
├── css/
│   └── styles.css      # Site styles
├── js/
│   ├── main.js         # Navigation, filters, contact form
│   └── image-modal.js  # Portfolio image lightbox
├── images/             # Logo, team photos, showcase images
└── services/           # Individual service pages
    ├── website-development.html
    ├── graphic-branding.html
    ├── ui-ux.html
    └── ecommerce.html
```

## Running locally

Open `index.html` in a browser, or serve the folder with any static file server:

```bash
npx serve .
```

## Deployment

Deploy the project root to any static host (Vercel, Netlify, GitHub Pages, etc.). No server-side runtime required.

## Contact form

The contact form opens the user's email client via `mailto:` with the form details pre-filled. For server-side form handling, integrate a service like Formspree or Netlify Forms.
