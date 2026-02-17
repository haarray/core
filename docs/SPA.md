# Haarray SPA — quick guide

This project ships a lightweight SPA layer that upgrades Blade pages with a near-native, app-like UX.
It makes page navigation and form submissions happen without full reloads while preserving server-side compatibility.

## Files to include
- `public/css/haarray.css` — main CSS (append SPA CSS snippet below)
- `public/js/haarray.js` — the SPA-enabled JavaScript (replace existing haarray.js with the SPA version provided)

## Minimal CSS snippet (append to haarray.css)
```css
/* SPA progress bar + skeleton helpers */
.h-spa-progress {
  position: fixed; top: 0; left: 0; height: 3px; width: 0%;
  background: linear-gradient(90deg, var(--gold), var(--teal));
  z-index: 99999; transition: width .25s linear, opacity .2s;
  box-shadow: 0 2px 8px rgba(0,0,0,.18);
}
.h-spa-progress.hide { opacity: 0; width: 0 !important; }
.h-spa-progress.show { opacity: 1; }

.h-skeleton {
  background: linear-gradient(90deg, rgba(255,255,255,.03), rgba(255,255,255,.06), rgba(255,255,255,.03));
  border-radius: 6px; min-height: 12px; display:inline-block;
  animation: h-skel 1.2s infinite linear;
}
@keyframes h-skel { 0%{background-position:-200px 0}100%{background-position:calc(200px + 100%) 0} }
