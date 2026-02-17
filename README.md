# HAARRAY CORE

### Laravel Starter Kit

**HariLog — In memory of Hari Bahadur Bhujel**

---

## Overview

`haarray-core` is the official foundation layer for all Haarray applications.

It provides:

* A modern design system (dark + light mode)
* Authentication scaffolding integration
* A lightweight progressive SPA engine
* Reusable Blade layout structure
* UI utilities (toasts, modals, loaders, helpers)

Every future Haarray product — HariLog, HariCMS, SaaS tools — begins here.

---

## Philosophy

Haarray Core follows three strict principles:

### 1️⃣ Progressive First

All routes work normally without JavaScript.

### 2️⃣ Zero Controller Changes

You do **not** need to return JSON.
Standard Laravel responses just work:

* `return view(...)`
* `return redirect(...)`
* `return back()->withErrors(...)`

The SPA layer enhances them automatically.

### 3️⃣ One System, Many Apps

One design system shared across all Haarray products.

---

## Features

### 🎨 Design System

* CSS variables
* Dark & light themes
* Minimal modern layout
* Sidebar layout
* Auth-ready pages

### ⚡ Lightweight SPA Engine

* History `pushState` navigation
* Partial page swaps (`#h-spa-content`)
* AJAX form submission
* CSRF auto handling
* Validation error handling
* Toast notifications
* Works without changing controllers

### 🧩 UI Components

* Toast system (`HToast`)
* Modal system (`HModal`)
* Theme manager (`HTheme`)
* SPA navigator (`HSPA`)
* API helper (`HApi`)
* Utility helpers (`HUtils`)

---

## Installation

### 1. Create Laravel Project

```bash
laravel new your-project
```

### 2. Install Auth (Recommended: Breeze)

```bash
php artisan breeze:install
npm install && npm run build
```

### 3. Copy haarray-core files

Copy:

* `public/css/haarray.css`
* `public/js/haarray.js`
* Blade layout files
* Config files
* Docs folder

### 4. (Optional) Install PHP ML

```bash
composer require php-ai/php-ml
```

### 5. Migrate

```bash
php artisan migrate
```

---

## JavaScript API

### Theme

```js
HTheme.apply("dark");
HTheme.apply("light");
```

---

### Toast

```js
HToast.success("Saved!");
HToast.error("Something went wrong");
HToast.warning("Be careful");
HToast.info("Heads up");
```

---

### Modal

```js
HModal.open("modal-id");
HModal.close("modal-id");
```

---

### SPA Navigation

```js
HSPA.navigate("/transactions");
```

---

### API Helper (CSRF aware)

```js
HApi.post("/transactions", { amount: 200 }, {
  success(res){},
  error(err){}
});
```

---

### Form Submission

```js
HApi.submitForm($('#my-form'), {
  success(){},
  error(){}
});
```

---

### Utilities

```js
HUtils.formatNPR(5200);
```
