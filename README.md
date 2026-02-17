# HAARRAY CORE

### Laravel Starter Kit

**HariLog — In memory of Hari Bahadur Bhujel**

---

## Overview

`haarray-core` is the official Laravel foundation for all Haarray applications.

It includes:

* Modern design system (Dark + Light)
* Authentication-ready layout
* Lightweight progressive SPA engine
* Reusable Blade structure
* Toasts, modals, helpers
* Zero-controller-change enhancement layer

Every Haarray product — HariLog, HariCMS, SaaS tools — starts here.

---

## Philosophy

### Progressive First

All routes work normally without JavaScript.

### Zero Controller Changes

You do **not** need to return JSON.

Standard Laravel responses work automatically:

```php
return view(...);
return redirect(...);
return back()->withErrors(...);
```

The SPA layer enhances them without modifying controllers.

### One System, Many Apps

One design system shared across all Haarray products.

---

# 🚀 Quick Installation

## 1️⃣ Clone Repository

```bash
git clone https://github.com/your-org/haarray-core.git
cd haarray-core
```

---

## 2️⃣ Install Dependencies

```bash
composer install
npm install
```

---

## 3️⃣ Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update your database credentials in `.env`.

---

## 4️⃣ Run Migrations

```bash
php artisan migrate
```

---

## 5️⃣ Link Storage

```bash
php artisan storage:link
```

---

## 6️⃣ Build Assets

```bash
npm run build
```

For development:

```bash
npm run dev
```

---

## 7️⃣ Start Server

```bash
php artisan serve
```

Visit:

```
http://127.0.0.1:8000
```

You are ready.

---

# Features

## 🎨 Design System

* CSS variables
* Dark & light themes
* Sidebar layout
* Responsive
* Auth-ready
* Minimal and modern

---

## ⚡ Lightweight SPA Engine

* History `pushState`
* Partial page replacement (`#h-spa-content`)
* AJAX form interception
* CSRF auto-handling
* Validation error handling
* Toast notifications
* Redirect detection
* Progressive fallback

Works with standard Laravel responses.

---

## 🧩 UI Components

* `HTheme`
* `HToast`
* `HModal`
* `HSPA`
* `HApi`
* `HUtils`

---

# JavaScript API

## Theme

```js
HTheme.apply("dark");
HTheme.apply("light");
```

---

## Toast

```js
HToast.success("Saved!");
HToast.error("Something went wrong");
HToast.warning("Be careful");
HToast.info("Heads up");
```

---

## Modal

```js
HModal.open("modal-id");
HModal.close("modal-id");
```

---

## SPA Navigation

```js
HSPA.navigate("/transactions");
```

---

## API Helper

```js
HApi.post("/transactions", { amount: 200 }, {
  success(res){},
  error(err){}
});
```

---

## Form Submission

```js
HApi.submitForm($('#my-form'), {
  success(){},
  error(){}
});
```

---

## Utilities

```js
HUtils.formatNPR(5200);
```

---

# 📖 Documentation

Detailed SPA engine documentation:

👉 **See `docs/SPA.md`**

This explains:

* How navigation interception works
* How redirects are handled
* How validation errors are parsed
* Lifecycle hooks
* When not to use SPA
* Progressive fallback strategy

---

# Project Structure

```
haarray-core/
├── app/
├── public/
├── resources/
│   └── views/
├── routes/
├── docs/
│   └── SPA.md
├── composer.json
└── README.md
```

---

# Built For

* HariLog
* HariCMS

---
