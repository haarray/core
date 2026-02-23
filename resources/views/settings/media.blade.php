@extends('layouts.app')

@section('title', app()->getLocale() === 'ne' ? 'मिडिया म्यानेजर' : 'Media Manager')
@section('page_title', app()->getLocale() === 'ne' ? 'मिडिया म्यानेजर' : 'Media Manager')

@section('topbar_extra')
  <span class="h-live-badge">
    <i class="fa-solid fa-photo-film"></i>
    {{ app()->getLocale() === 'ne' ? 'ओपन सोर्स मिडिया' : 'Open Source Media' }}
  </span>
@endsection

@section('content')
@php
  $uiLocale = app()->getLocale() === 'ne' ? 'ne' : 'en';
  $hlText = static function (string $en, string $ne = '') use ($uiLocale): string {
    if ($uiLocale === 'ne' && $ne !== '') {
      return $ne;
    }
    return $en;
  };
@endphp
<div class="hl-docs hl-settings h-elfinder-page">
  <div class="doc-head">
    <div>
      <div class="doc-title">{{ $hlText('Media Manager', 'मिडिया म्यानेजर') }}</div>
      <div class="doc-sub">{{ $hlText('Powered by open-source elFinder with folder operations, right-click actions, zip archive download, image resize, and mobile-friendly view.', 'ओपन-सोर्स elFinder मा आधारित: फोल्डर व्यवस्थापन, राइट-क्लिक कार्य, zip डाउनलोड, इमेज रिसाइज, मोबाइल मैत्रीपूर्ण दृश्य।') }}</div>
    </div>
    <span class="h-pill teal">{{ $storageLabel }}</span>
  </div>

  <div class="h-note mb-2">
    {{ $hlText('Root path:', 'रुट पाथ:') }} <code>/public/uploads</code>. {{ $hlText('Folders created here are visible in your project root at', 'यहाँ बनाइएका फोल्डरहरू प्रोजेक्ट रुटमा देखिन्छन्:') }} <code>public/uploads</code>.
  </div>

  <div class="h-elfinder-shell">
    <div id="settings-media-elfinder"
      data-connector-url="{{ route('settings.media.connector') }}"
      data-read-only="{{ $canManageSettings ? '0' : '1' }}"
      data-mode="page"></div>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
  if (window.HMediaManager && typeof window.HMediaManager.mountPage === 'function') {
    window.HMediaManager.mountPage();
  }
})();
</script>
@endsection
