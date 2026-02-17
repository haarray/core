(function ($, window, document) {
  'use strict';

  /* ── THEME ────────────────────────────────────────────── */
  const HTheme = {
    init() {
      const saved = localStorage.getItem('h_theme') || 'dark';
      this.apply(saved, false);
      $(document).on('click', '.h-theme-toggle', () => {
        this.apply($('html').attr('data-theme') === 'dark' ? 'light' : 'dark');
      });
    },
    apply(t, toast = true) {
      $('html').attr('data-theme', t);
      localStorage.setItem('h_theme', t);
      $('.h-theme-toggle .moon').toggle(t === 'dark');
      $('.h-theme-toggle .sun').toggle(t === 'light');
      if (toast) HToast.info('Switched to ' + t + ' mode');
    },
    current() { return $('html').attr('data-theme') === 'dark' ? 'dark' : 'light'; }
  };

  /* ── SIDEBAR ──────────────────────────────────────────── */
  const HSidebar = {
    init() {
      $(document).on('click', '.h-menu-toggle', () => this.toggle());
      $(document).on('click', '.h-sidebar-overlay', () => this.close());
      $(document).on('click', '.h-nav-item', () => {
        if ($(window).width() <= 768) this.close();
      });
    },
    toggle() { $('#h-sidebar').hasClass('open') ? this.close() : this.open(); },
    open()  { $('#h-sidebar').addClass('open'); $('.h-sidebar-overlay').addClass('show'); $('body').css('overflow','hidden'); },
    close() { $('#h-sidebar').removeClass('open'); $('.h-sidebar-overlay').removeClass('show'); $('body').css('overflow',''); }
  };

  /* ── TOAST ────────────────────────────────────────────── */
  const HToast = {
    init() { if (!$('#h-toasts').length) $('body').append('<div id="h-toasts"></div>'); },
    show(msg, type = 'info', dur = 3800) {
      const ic = { success:'✓', error:'✕', warning:'⚠', info:'ℹ' };
      const cl = { success:'var(--green)', error:'var(--red)', warning:'var(--gold)', info:'var(--teal)' };
      const $t = $(`<div class="h-toast"><span style="color:${cl[type]};font-size:15px">${ic[type]}</span><span>${msg}</span></div>`);
      $('#h-toasts').append($t);
      requestAnimationFrame(() => $t.addClass('show'));
      setTimeout(() => { $t.removeClass('show'); setTimeout(() => $t.remove(), 350); }, dur);
    },
    success: (m,d) => HToast.show(m,'success',d),
    error:   (m,d) => HToast.show(m,'error',d),
    warning: (m,d) => HToast.show(m,'warning',d),
    info:    (m,d) => HToast.show(m,'info',d),
  };

  /* ── MODAL ────────────────────────────────────────────── */
  const HModal = {
    init() {
      $(document).on('click', '[data-modal-open]', function () { HModal.open($(this).data('modal-open')); });
      $(document).on('click', '.h-modal-close, [data-modal-close]', function () { HModal.close($(this).closest('.h-modal-overlay').attr('id')); });
      $(document).on('click', '.h-modal-overlay', function (e) { if ($(e.target).hasClass('h-modal-overlay')) HModal.close($(this).attr('id')); });
      $(document).on('keydown', e => { if (e.key === 'Escape') HModal.closeAll(); });
    },
    open(id)  { $(`#${id}`).addClass('show'); $('body').css('overflow','hidden'); },
    close(id) { $(`#${id}`).removeClass('show'); if (!$('.h-modal-overlay.show').length) $('body').css('overflow',''); },
    closeAll(){ $('.h-modal-overlay').removeClass('show'); $('body').css('overflow',''); }
  };

  /* ── PASSWORD TOGGLE ──────────────────────────────────── */
  $(document).on('click', '.h-pw-toggle', function () {
    const $inp = $(this).closest('.h-input-wrap').find('.h-input');
    const t = $inp.attr('type') === 'password' ? 'text' : 'password';
    $inp.attr('type', t);
    $(this).find('.eye-on').toggle(t === 'text');
    $(this).find('.eye-off').toggle(t === 'password');
  });

  /* ── CLOCK ────────────────────────────────────────────── */
  function updateClock() {
    const $el = $('#h-live-clock,#h-clock');
    if (!$el.length) return;
    $el.text(new Date().toLocaleString('en-US', { weekday:'short', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }));
  }

  /* ── CSRF AJAX ────────────────────────────────────────── */
  function initAjax() {
    const tok = $('meta[name="csrf-token"]').attr('content');
    if (tok) $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': tok } });
  }

  /* ── UTILITIES ────────────────────────────────────────── */
  const HUtils = {
    formatNPR(n) {
      if (typeof n !== 'number') n = Number(n) || 0;
      return 'रू ' + n.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    },
    htmlToDoc(html) { return new DOMParser().parseFromString(html, 'text/html'); },
    extractContainer(html, selector) {
      const doc = HUtils.htmlToDoc(html);
      return doc.querySelector(selector);
    }
  };

  /* ── API WRAPPER ──────────────────────────────────────── */
  const HApi = {
    get(url, data = {}, opts = {}) {
      return $.ajax($.extend({ url, method: 'GET', data }, opts));
    },
    post(url, data = {}, opts = {}) {
      return $.ajax($.extend({ url, method: 'POST', data }, opts));
    },
    submitForm($form, opts = {}) {
      const url = $form.attr('action') || location.pathname;
      const method = ($form.attr('method') || 'POST').toUpperCase();
      const hasFile = $form.find('input[type=file]').length > 0;
      let data;
      let settings = { url, method };
      if (hasFile) {
        data = new FormData($form[0]);
        settings.processData = false;
        settings.contentType = false;
        settings.data = data;
      } else {
        data = $form.serialize();
        settings.data = data;
      }
      return $.ajax(settings).done((res, status, xhr) => {
        if (opts.success) opts.success(res, status, xhr);
      }).fail((xhr) => {
        if (opts.error) opts.error(xhr);
      });
    }
  };

  /* ── SPA LAYER ────────────────────────────────────────── */
  const HSPA = {
    container: '#h-spa-content',
    progress: null,
    init() {
      this.createProgress();
      this.bindLinks();
      this.bindForms();
      window.addEventListener('popstate', () => {
        // load current location without pushing state
        this.load(location.pathname + location.search, false);
      });
    },
    createProgress() {
      if (!document.querySelector('.h-spa-progress')) {
        const p = document.createElement('div');
        p.className = 'h-spa-progress hide';
        document.body.appendChild(p);
        this.progress = p;
      } else {
        this.progress = document.querySelector('.h-spa-progress');
      }
    },
    showProgress() {
      if (!this.progress) this.createProgress();
      this.progress.classList.remove('hide');
      this.progress.style.width = '6%';
      this.progress.offsetWidth; // force repaint
      setTimeout(() => { if (this.progress) this.progress.style.width = '45%'; }, 80);
      document.documentElement.classList.add('h-spa-loading');
    },
    finishProgress() {
      if (!this.progress) return;
      this.progress.style.width = '100%';
      setTimeout(() => {
        this.progress.classList.add('hide');
        this.progress.style.width = '0%';
      }, 180);
      document.documentElement.classList.remove('h-spa-loading');
    },
    bindLinks() {
      // delegate: only intercept <a data-spa>
      $(document).on('click', 'a[data-spa]', function (e) {
        const href = $(this).attr('href');
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
        e.preventDefault();
        HSPA.navigate(href);
      });
    },
    bindForms() {
      // intercept forms with data-spa
      $(document).on('submit', 'form[data-spa]', function (e) {
        e.preventDefault();
        const $form = $(this);
        // disable submit, show local busy text if present
        const $btn = $form.find('button[type="submit"]').first();
        if ($btn.length) {
          $btn.prop('disabled', true).data('orig', $btn.text()).text($btn.data('busy-text') || 'Working…');
        }
        HApi.submitForm($form, {
          success(res, status, xhr) {
            HSPA._handleResponse(res, xhr, true);
            if ($btn && $btn.length) { $btn.prop('disabled', false).text($btn.data('orig')); }
          },
          error(xhr) {
            HSPA._handleError(xhr);
            if ($btn && $btn.length) { $btn.prop('disabled', false).text($btn.data('orig')); }
          }
        });
      });
    },
    navigate(url, push = true) {
      this.showProgress();
      const opts = { url, method: 'GET', dataType: 'html' };
      return $.ajax(opts).done((html, status, xhr) => {
        this._handleResponse(html, xhr, push);
      }).fail((xhr) => {
        this.finishProgress();
        HToast.error('Failed to load page — falling back.');
        // fallback to full navigation
        window.location.href = url;
      });
    },
    load(url) { return this.navigate(url, false); },
    _handleResponse(res, xhr, push) {
      const ct = (xhr && xhr.getResponseHeader) ? (xhr.getResponseHeader('Content-Type') || '') : '';
      // JSON response? parse and honour redirect/message
      if (ct.indexOf('application/json') !== -1 || (typeof res !== 'string' && typeof res === 'object')) {
        try {
          const json = (typeof res === 'string') ? JSON.parse(res) : res;
          if (json.redirect) {
            // if redirect is absolute or relative, navigate normally (will trigger SPA)
            this.navigate(json.redirect);
            return;
          }
          if (json.message) HToast.success(json.message);
        } catch (e) {
          HToast.info('Done');
        } finally {
          this.finishProgress();
          return;
        }
      }

      // If HTML: try to extract SPA container
      if (typeof res === 'string') {
        const doc = HUtils.htmlToDoc(res);
        const newContainer = doc.querySelector(this.container);
        if (newContainer) {
          const curContainer = document.querySelector(this.container);
          if (curContainer) {
            curContainer.innerHTML = newContainer.innerHTML;
            // update title if present
            const newTitle = doc.querySelector('title');
            if (newTitle) document.title = newTitle.textContent;
            // run inline scripts inside container
            this._runInlineScripts(newContainer);
            // optional: re-initialize small components if needed
            this._rehydrate();
            if (push !== false) history.pushState({}, '', xhr ? xhr.responseURL || location.href : location.href);
            // smooth scroll to top
            window.scrollTo(0,0);
            this.finishProgress();
            return;
          }
        } else {
          // No partial; server returned full page (or redirect resolved to page) — replace document
          document.open();
          document.write(res);
          document.close();
          this.finishProgress();
          return;
        }
      }

      // fallback
      this.finishProgress();
    },
    _handleError(xhr) {
      // Try parse JSON errors
      let msg = 'Something went wrong.';
      const ct = xhr.getResponseHeader ? (xhr.getResponseHeader('Content-Type') || '') : '';
      if (ct.indexOf('application/json') !== -1) {
        try {
          const json = JSON.parse(xhr.responseText);
          msg = json.message || (json.errors && Object.values(json.errors)[0][0]) || msg;
        } catch (e) {}
      } else if (xhr.responseText && typeof xhr.responseText === 'string') {
        // attempt to extract generic error message from HTML (rare)
        const doc = HUtils.htmlToDoc(xhr.responseText);
        const errEl = doc.querySelector('.h-alert.error, .alert-danger');
        if (errEl) msg = errEl.textContent.trim().slice(0,200);
      }
      HToast.error(msg);
      this.finishProgress();
    },
    _runInlineScripts(containerEl) {
      // run <script> tags inside the container element safely
      try {
        const scripts = containerEl.querySelectorAll('script');
        scripts.forEach(s => {
          if (s.src) {
            // dynamic load external script if not already present
            if (!document.querySelector(`script[src="${s.src}"]`)) {
              const sc = document.createElement('script');
              sc.src = s.src;
              sc.defer = true;
              document.head.appendChild(sc);
            }
          } else {
            // inline code
            try { window.eval(s.textContent); } catch (e) { console.error('Inline script error', e); }
          }
        });
      } catch (e) { /* ignore */ }
    },
    _rehydrate() {
      // Re-run any trivial initialization that depends on DOM presence
      // (Theme/Modal/Toast/Password toggles already bound via delegated handlers)
      updateClock();
    }
  };

  /* ── INIT ─────────────────────────────────────────────── */
  $(document).ready(function () {
    HTheme.init();
    HSidebar.init();
    HToast.init();
    HModal.init();
    initAjax();
    updateClock();
    setInterval(updateClock, 30000);

    // Expose utilities and APIs globally
    window.HTheme = HTheme;
    window.HSidebar = HSidebar;
    window.HToast = HToast;
    window.HModal = HModal;
    window.HApi = HApi;
    window.HSPA = HSPA;
    window.HUtils = HUtils;

    // start SPA engine
    HSPA.init();

    // small UX: attach busy-text behaviour to submit buttons inside data-spa forms
    $(document).on('submit', 'form[data-spa]', function () {
      const $btn = $(this).find('button[type="submit"]').first();
      if ($btn.length) {
        $btn.prop('disabled', true).data('orig', $btn.text()).text($btn.data('busy-text') || 'Working…');
        setTimeout(() => $btn.prop('disabled', false).text($btn.data('orig')), 8000);
      }
    });
  });

})(jQuery, window, document);
