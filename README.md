# HAARRAY CORE — Laravel Starter Kit

## HariLog | In memory of Hari Bahadur Bhujel

---

## What This Is

`haarray-core` is a Laravel starter kit that contains a complete
design system, auth pages, a lightweight SPA engine, and scaffolding
to kick off any Haarray application (HariLog, HariCMS, etc.).

**Every future Haarray app starts here.**

---

## Quick Setup

1. Create Laravel project
2. Install Breeze (or your preferred auth)
3. Copy `haarray-core` files into your project (css, js, views, config, controllers)
4. Install `php-ai/php-ml` if you want the PHP ML examples
5. Create migrations from `database/migrations/all_migrations_reference.php`
6. `php artisan migrate`

(You already have the full step-by-step in your kit; keep that.)

---

## Design & UX Goals

- Minimal, modern design tokens (dark + light, CSS variables)
- Progressive enhancement — pages work if JS is disabled
- Native, app-like UX using a tiny SPA layer:
  - History pushState navigation
  - Partial page loads (replace `#h-spa-content`)
  - AJAX form submission for auth & common forms
  - Toasts, modals, skeleton loaders, optimistic UI
- Reusable Blade layout so every app can reuse the same visual system

---

## JavaScript API (highlight)

```js
// THEME
HTheme.apply("dark"); // or 'light'

// TOAST
HToast.success("Saved!");

// MODAL
HModal.open("modal-id");

// SPA NAV
HSPA.navigate("/transactions");

// API (CSRF-aware)
HApi.post("/transactions", { amount: 200 }, { success(){}, error(){} });
HApi.submitForm($('#my-form'), { success(){}, error(){} });

// UTILITIES
HUtils.formatNPR(5200);
