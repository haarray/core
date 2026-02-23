<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="dark">
<head>
  @php
    $uiLocale = app()->getLocale() === 'ne' ? 'ne' : 'en';
    $nextUiLocale = $uiLocale === 'ne' ? 'en' : 'ne';
    $hlText = static function (string $en, string $ne = '') use ($uiLocale): string {
      if ($uiLocale === 'ne' && $ne !== '') {
        return $ne;
      }
      return $en;
    };
    $uiBranding = \App\Support\AppSettings::uiBranding();
    $brandDisplayName = trim((string) ($uiBranding['display_name'] ?? config('app.name', 'HariLog')));
    $brandMark = trim((string) ($uiBranding['brand_mark'] ?? config('haarray.app_initial', 'H')));
    $brandFavicon = \App\Support\AppSettings::resolveUiAsset((string) ($uiBranding['favicon_url'] ?? ''));
    if ($brandDisplayName === '') {
      $brandDisplayName = (string) config('app.name', 'HariLog');
    }
    if ($brandMark === '') {
      $brandMark = (string) config('haarray.app_initial', 'H');
    }
  @endphp
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $hlText('Verify Login', 'लगइन प्रमाणिकरण') }} — {{ $brandDisplayName }}</title>
  <link rel="icon" type="image/x-icon" href="{{ $brandFavicon !== '' ? $brandFavicon : asset('favicon.ico') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=JetBrains+Mono:wght@400;500&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/haarray.app.css') }}">
</head>
<body>
<div class="h-auth-wrap">
  <div class="h-auth-left">
    <div class="h-auth-art">
      <div class="h-auth-logo">{{ strtoupper(substr($brandMark, 0, 1)) }}</div>
      <div class="h-auth-headline">
        {!! $hlText('Secure login<br><span>verification</span>', 'सुरक्षित लगइन<br><span>प्रमाणिकरण</span>') !!}
      </div>
      <p class="h-auth-desc">
        {{ $hlText('Enter the 6-digit code we sent to your email.', 'इमेलमा पठाइएको ६-अंकीय कोड प्रविष्ट गर्नुहोस्।') }}
      </p>
    </div>
  </div>

  <div class="h-auth-right">
    <div class="h-auth-form">
      <div style="display:flex;justify-content:flex-end;margin-bottom:22px;">
        <form method="POST" action="{{ route('ui.locale.set') }}" class="me-2">
          @csrf
          <input type="hidden" name="locale" value="{{ $nextUiLocale }}">
          <button class="h-icon-btn h-locale-toggle" type="submit" title="{{ $uiLocale === 'ne' ? 'Switch to English' : 'नेपालीमा बदल्नुहोस्' }}">
            <span class="h-locale-pill">{{ $uiLocale === 'ne' ? 'EN' : 'ने' }}</span>
          </button>
        </form>
      </div>

      <div class="h-auth-title">{{ $hlText('Two-factor verification', 'दुई-चरण प्रमाणिकरण') }}</div>
      <div class="h-auth-sub">{{ $hlText('Code expires in 10 minutes', 'कोड १० मिनेटमा समाप्त हुन्छ') }}</div>

      @if($errors->any())
        <div class="h-alert error">
          <i class="fa-solid fa-triangle-exclamation"></i>
          {{ $errors->first() }}
        </div>
      @endif

      @if(session('success'))
        <div class="h-alert success">
          <i class="fa-solid fa-check"></i>
          {{ session('success') }}
        </div>
      @endif

      <form method="POST" action="{{ route('2fa.verify') }}" data-spa>
        @csrf
        <div class="h-form-group">
          <label class="h-label" for="code">{{ $hlText('Verification Code', 'प्रमाणिकरण कोड') }}</label>
          <input
            id="code"
            type="text"
            name="code"
            maxlength="6"
            inputmode="numeric"
            class="h-input"
            placeholder="{{ $hlText('123456', '१२३४५६') }}"
            required
            autocomplete="one-time-code"
          >
        </div>

        <button type="submit" class="h-btn primary full lg" data-busy-text="{{ $hlText('Verifying...', 'प्रमाणित हुँदैछ...') }}">
          <i class="fa-solid fa-shield-halved"></i>
          {{ $hlText('Verify and Continue', 'प्रमाणित गरी जारी राख्नुहोस्') }}
        </button>
      </form>

      <form method="POST" action="{{ route('2fa.resend') }}" style="margin-top:10px;" data-spa>
        @csrf
        <button type="submit" class="h-btn ghost full" data-busy-text="{{ $hlText('Sending...', 'पठाइँदैछ...') }}">
          <i class="fa-solid fa-rotate"></i>
          {{ $hlText('Resend Code', 'कोड फेरि पठाउनुहोस्') }}
        </button>
      </form>

      <p style="text-align:center;margin-top:18px;font-size:13px;color:var(--t2);">
        <a href="{{ route('login') }}" style="color:var(--gold);" data-spa>{{ $hlText('Back to login', 'लगइनमा फर्कनुहोस्') }}</a>
      </p>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/haarray.nepali-date.js') }}"></script>
<script src="{{ asset('js/haarray.app.js') }}"></script>
</body>
</html>
