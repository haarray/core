## SPA Documentation

For full technical documentation:

👉 See **`docs/SPA.md`**

---

## Recommended Structure

```
haarray-core/
├── public/
│   ├── css/haarray.css
│   └── js/haarray.js
├── resources/views/layouts/haarray.blade.php
├── docs/
│   └── SPA.md
└── README.md
```

---

## Built For

* HariLog
* HariCMS
* Future Haarray SaaS apps
* Internal dashboards
* ML-enabled Laravel tools

---

Haarray Core is not just a starter kit.
It is the architectural foundation of the Haarray ecosystem.

---

---

# 📘 docs/SPA.md

# Haarray SPA Engine

The Haarray SPA engine is a lightweight progressive enhancement layer.

It transforms standard Laravel pages into an app-like experience — without requiring JSON responses or controller modifications.

---

## Core Principles

1. Progressive enhancement
2. No controller changes required
3. Works with standard Laravel responses
4. Graceful fallback if JS disabled

---

## How It Works

### 1️⃣ Navigation Interception

Links inside the app are intercepted.

Instead of full page reload:

* Fetch page via AJAX
* Extract `#h-spa-content`
* Replace current content
* Push history state

If request fails → fallback to normal navigation.

---

### 2️⃣ Partial Content Container

Your layout must contain:

```html
<div id="h-spa-content">
    @yield('content')
</div>
```

This is the replacement container.

---

### 3️⃣ History Handling

Back/forward browser buttons are supported.

```js
window.onpopstate
```

Triggers content reload automatically.

---

### 4️⃣ Form Interception

Forms are hijacked automatically.

Supports:

* POST
* PUT
* PATCH
* DELETE

If validation fails:

* Laravel returns errors
* SPA extracts errors
* Toast notifications show messages

If redirect:

* SPA follows redirect automatically

---

## Response Handling

The SPA engine understands:

### Blade View Response

```
return view('dashboard');
```

### Redirect

```
return redirect()->route('home');
```

### Validation Errors

```
return back()->withErrors([...]);
```

### JSON (Optional)

```
return response()->json([...]);
```

JSON is optional — not required.

---

## Lifecycle Hooks

Optional hooks you may use:

```js
document.addEventListener("hspa:beforeLoad", () => {});
document.addEventListener("hspa:afterLoad", () => {});
```

Use them to reinitialize plugins or charts.

---

## Loading Indicators

During navigation:

* Sidebar remains intact
* Content fades
* Skeleton loader may appear

You can style this in `haarray.css`.

---

## CSS SPA Utilities

Example styles:

```css
#h-spa-content {
  transition: opacity .2s ease;
}

.h-spa-loading {
  opacity: .5;
  pointer-events: none;
}
```

---

## When NOT to Use SPA

Do not intercept:

* External links
* File downloads
* Links with `target="_blank"`
* Admin heavy data export pages (optional)

---

## Debug Mode

Enable debug logging:

```js
HSPA.debug = true;
```

---

## Summary

Haarray SPA Engine:

* Enhances Laravel
* Does not replace Laravel
* Requires zero backend changes
* Keeps system simple
* Avoids heavy frameworks

It is intentionally minimal and stable.
