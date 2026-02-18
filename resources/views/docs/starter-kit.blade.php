@extends('layouts.haarray')

@section('title','Starter Kit — Docs')
@section('page_title','Starter Kit')

@section('content')
<div class="hl-docs">
  <div class="doc-head">
    <div>
      <div class="doc-title">Haarray Starter Kit</div>
      <div class="doc-sub">A compact, progressive-enhancement toolkit — confirm modal, editor, icons, and SVG charts.</div>
    </div>
    <div class="kv">v1.0 • Minimal JS • jQuery friendly</div>
  </div>

  <div class="doc-grid">
    <aside class="doc-toc">
      <strong>Contents</strong>
      <ul style="margin-top:8px;">
        <li><a href="#confirm">Confirm (HConfirm)</a></li>
        <li><a href="#editor">Editor (HEditor)</a></li>
        <li><a href="#icons">Icons</a></li>
        <li><a href="#svgpies">SVG Pie charts</a></li>
        <li><a href="#integration">Integration</a></li>
      </ul>
    </aside>

    <main class="doc-content">
      <section id="confirm">
        <h3>Confirm (HConfirm)</h3>
        <p>Make any destructive link or form show a confirmation modal while still allowing the action to work without JS.</p>

        <h4>Simple link example</h4>
        <pre><code>&lt;a href="{{ route('dashboard', 1) }}" data-confirm="true" data-confirm-title="Delete post?" data-confirm-text="This cannot be undone." data-confirm-method="DELETE"&gt;Delete&lt;/a&gt;</code></pre>

        <h4>Form example</h4>
        <pre><code>&lt;form action="/delete" method="POST" data-confirm="true"&gt;
  &lt;input type="hidden" name="_token" value="..."&gt;
  &lt;button type="submit"&gt;Delete&lt;/button&gt;
&lt;/form&gt;</code></pre>

        <h4>Data attributes</h4>
        <ul>
          <li><code>data-confirm-title</code> — modal title</li>
          <li><code>data-confirm-text</code> — body text</li>
          <li><code>data-confirm-ok</code> — ok button text</li>
          <li><code>data-confirm-cancel</code> — cancel text</li>
          <li><code>data-confirm-method</code> — for links: GET/POST/DELETE (default GET)</li>
        </ul>
      </section>

      <section id="editor" style="margin-top:18px;">
        <h3>Editor (HEditor)</h3>
        <p>Use <code>data-editor</code> or <code>class="h-editor"</code>. If inside a form set a name via <code>data-editor-name</code>, which syncs to a hidden textarea on submit.</p>
        <pre><code>&lt;div data-editor data-editor-name="notes"&gt;Initial content&lt;/div&gt;</code></pre>
        <p>Toolbar: bold, italic, link. If you want bare editing (no toolbar): <code>data-editor="bare"</code>.</p>
      </section>

      <section id="icons" style="margin-top:18px;">
        <h3>Icons</h3>
        <p>Use sprite icons with <code>&lt;use&gt;</code>:</p>
        <pre><code>&lt;svg class="h-icon"&gt;&lt;use xlink:href="/icons/icons.svg#trash"&gt;&lt;/use&gt;&lt;/svg&gt;</code></pre>
      </section>

      <section id="svgpies" style="margin-top:18px;">
        <h3>SVG Pie charts</h3>
        <p>Data is JSON inside <code>data-pie</code>. Example:</p>
        <pre><code>&lt;div class="h-svg-pie" data-pie='[{ "label":"Food","value":30 },{ "label":"Rent","value":50 }] '&gt;&lt;/div&gt;</code></pre>
        <p>Colors can be provided per slice: <code>{"label":"Food","value":30,"color":"#f5a623"}</code>.</p>
      </section>

      <section id="integration" style="margin-top:18px;">
        <h3>Integration</h3>
        <ol>
          <li>Include <code>public/css/haarray.starter.css</code> after your main CSS.</li>
          <li>Include <code>public/js/haarray.plugins.js</code> after your main JS and after jQuery.</li>
          <li>Include the sprite <code>public/icons/icons.svg</code> (inline or as external asset).</li>
          <li>Include the confirm modal partial once: <code>@include('components.confirm-modal')</code>.</li>
        </ol>
      </section>
    </main>
  </div>
</div>
@endsection
