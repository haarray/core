# Notification Automation (Deprecated)

The old UI-based automation rules were removed from Settings to keep notification logic explicit and predictable.

Use `docs/tutorials/NOTIFIER_HELPER.md` and dispatch notifications directly from controllers/services with:

```php
app(\App\Support\Notifier::class)->toUser(...);
app(\App\Support\Notifier::class)->toRole(...);
```

This keeps alert behavior tied to business actions instead of broad route matching.
