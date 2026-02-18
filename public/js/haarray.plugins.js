// public/js/haarray.plugins.js
// Haarray Starter Kit plugins (HConfirm, HEditor, HIcons, HSvgPie)
// No external deps beyond jQuery (your project already uses it).
(function (window, document, $) {
    'use strict';

    /* --------------------------
       HConfirm: confirm modal
       Usage:
         <a href="/post/1" data-confirm="true"
            data-confirm-title="Delete post?"
            data-confirm-text="This action is permanent."
            data-confirm-ok="Delete"
            data-confirm-cancel="Keep"
            data-confirm-method="DELETE">Delete</a>
       Or:
         <form action="/delete" method="POST" data-confirm="true" ...>...</form>
    ---------------------------*/
    const HConfirm = {
      init() {
        this.$modal = $('[data-h-confirm]').first();
        if (!this.$modal.length) {
          // not included — nothing to do
          return;
        }
        this.$title  = this.$modal.find('#h-confirm-title');
        this.$text   = this.$modal.find('#h-confirm-text');
        this.$ok     = this.$modal.find('[data-h-confirm-ok]');
        this.$cancel = this.$modal.find('[data-h-confirm-cancel]');
        this.$close  = this.$modal.find('[data-h-confirm-close]');

        // events
        const self = this;
        $(document).on('click', 'a[data-confirm="true"]', function (e) {
          const $a = $(this);
          if (e.ctrlKey || e.metaKey || e.which === 2) return; // allow open in new tab
          e.preventDefault();
          self._openFromAnchor($a);
        });

        $(document).on('submit', 'form[data-confirm="true"]', function (e) {
          e.preventDefault();
          self._openFromForm($(this));
        });

        this.$cancel.on('click', () => this.close());
        this.$close.on('click', () => this.close());

        // when ok clicked: decide what to do
        this.$ok.on('click', () => this._doConfirm());
        // ESC key closes
        $(document).on('keydown', function (e) { if (e.key === 'Escape') self.close(); });
      },

      _openFromAnchor($a) {
        this.current = {
          type: 'link',
          el: $a,
          href: $a.attr('href'),
          method: ($a.data('confirm-method') || 'GET').toUpperCase()
        };
        this._show(
          $a.data('confirm-title') || 'Confirm',
          $a.data('confirm-text')  || 'Are you sure you want to continue?',
          $a.data('confirm-ok')    || 'OK',
          $a.data('confirm-cancel')|| 'Cancel'
        );
      },

      _openFromForm($f) {
        this.current = {
          type: 'form',
          el: $f
        };
        this._show(
          $f.data('confirm-title') || 'Confirm',
          $f.data('confirm-text')  || 'Are you sure you want to continue?',
          $f.data('confirm-ok')    || 'OK',
          $f.data('confirm-cancel')|| 'Cancel'
        );
      },

      _show(title, text, ok, cancel) {
        this.$title.text(title);
        this.$text.text(text);
        this.$ok.text(ok);
        this.$cancel.text(cancel);
        this.$modal.addClass('show');
        $('body').css('overflow', 'hidden');
      },

      close() {
        if (!this.$modal) return;
        this.$modal.removeClass('show');
        $('body').css('overflow', '');
        this.current = null;
      },

      _doConfirm() {
        if (!this.current) { this.close(); return; }
        if (this.current.type === 'link') {
          const m = this.current.method;
          const href = this.current.href;
          if (!href) { this.close(); return; }
          if (m === 'GET') {
            window.location.href = href;
          } else {
            // create a form to submit (progressive: works without AJAX)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = href;
            form.style.display = 'none';
            // CSRF token
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (tokenMeta) {
              const token = tokenMeta.getAttribute('content');
              const i = document.createElement('input'); i.type = 'hidden'; i.name = '_token'; i.value = token;
              form.appendChild(i);
            }
            // method override
            if (m !== 'POST') {
              const im = document.createElement('input'); im.type = 'hidden'; im.name = '_method'; im.value = m;
              form.appendChild(im);
            }
            document.body.appendChild(form);
            form.submit();
          }
          this.close();
        } else if (this.current.type === 'form') {
          // unbind our submit handler for this form, then submit
          const $f = this.current.el;
          $f.off('submit'); // prevents loop
          $f.trigger('submit'); // will now submit normally
          this.close();
        } else {
          this.close();
        }
      }
    };

    /* --------------------------
       HEditor: simple rich text editor
       - Any element with data-editor or .h-editor becomes editable
       - If inside a form and has data-editor-name, we create a hidden textarea to sync html on submit
       - toolbar supports bold, italic, link insertion
    ---------------------------*/
    const HEditor = {
      init() {
        // initialize existing editors
        $('[data-editor], .h-editor').each(function () { HEditor.setup($(this)); });
      },

      setup($el) {
        if ($el.data('he-ready')) return;
        $el.attr('contenteditable', true).addClass('h-editor');
        $el.data('he-ready', true);

        // create hidden textarea if inside a form and name provided
        const editorName = $el.data('editor-name') || $el.attr('name') || $el.attr('id');
        if (editorName) {
          const $form = $el.closest('form');
          if ($form.length) {
            const $hidden = $(`<textarea name="${editorName}" style="display:none" aria-hidden="true"></textarea>`);
            $form.append($hidden);
            $form.on('submit', function () {
              $hidden.val($el.html());
            });
          }
        }

        // toolbar (unless data-editor="bare")
        if ($el.data('editor') !== 'bare') {
          const $tb = $(`<div class="h-editor-toolbar" aria-hidden="false">
            <button type="button" data-cmd="bold" title="Bold"><strong>B</strong></button>
            <button type="button" data-cmd="italic" title="Italic"><em>I</em></button>
            <button type="button" data-cmd="createLink" title="Link">🔗</button>
            <button type="button" data-cmd="unlink" title="Unlink">⛓</button>
          </div>`);
          $el.before($tb);
          $tb.on('click', '[data-cmd]', function (ev) {
            const cmd = $(this).data('cmd');
            if (cmd === 'createLink') {
              const url = prompt('Enter link URL');
              if (!url) return;
              document.execCommand('createLink', false, url);
            } else {
              document.execCommand(cmd, false, null);
            }
          });
        }
      }
    };

    /* --------------------------
       HIcons: small helper for icon sprite usage
       - Inline or <use>. Optionally fetch and inline sprite to avoid separate request.
       - Use: <svg class="h-icon"><use xlink:href="/icons/icons.svg#trash"></use></svg>
       - Or: <img class="h-icon-inline" data-icon="trash">
    ---------------------------*/
    const HIcons = {
      init() {
        // inline images with data-icon -> replace with svg <use> markup
        $('[data-icon]').each(function () {
          const name = $(this).data('icon');
          const attrs = [];
          const classes = $(this).attr('class') || '';
          const w = $(this).data('w') || 16;
          const h = $(this).data('h') || 16;
          const svg = $(`<svg class="${classes}" width="${w}" height="${h}" viewBox="0 0 24 24" aria-hidden="true"><use xlink:href="/icons/icons.svg#${name}"></use></svg>`);
          $(this).replaceWith(svg);
        });
      }
    };

    /* --------------------------
       HSvgPie: render pie charts using SVG arcs (no canvas).
       Usage:
         <div class="h-svg-pie" data-pie='[{"label":"A","value":20,"color":"#f5a623"},{"label":"B","value":80}]'></div>
    ---------------------------*/
    const HSvgPie = {
      init() {
        document.querySelectorAll('.h-svg-pie[data-pie]').forEach(el => this.render(el));
      },
      render(container) {
        let data;
        try { data = JSON.parse(container.getAttribute('data-pie')); } catch (e) { return; }
        const total = data.reduce((s, d) => s + (d.value || 0), 0) || 1;
        // build svg
        const NS = 'http://www.w3.org/2000/svg';
        const svg = document.createElementNS(NS, 'svg');
        svg.setAttribute('viewBox', '0 0 200 100'); // wide view for responsive look (pie left, legend right)
        svg.setAttribute('preserveAspectRatio', 'xMinYMid meet');
        // center at (50,50)
        const cx = 50, cy = 50, r = 40;
        let start = -0.5 * Math.PI; // start angle
        const group = document.createElementNS(NS, 'g');
        data.forEach((d, i) => {
          const value = d.value || 0;
          const slice = (value / total) * Math.PI * 2;
          const end = start + slice;
          const x1 = cx + r * Math.cos(start);
          const y1 = cy + r * Math.sin(start);
          const x2 = cx + r * Math.cos(end);
          const y2 = cy + r * Math.sin(end);
          const large = slice > Math.PI ? 1 : 0;
          const path = document.createElementNS(NS, 'path');
          const color = d.color || HSvgPie._niceColor(i);
          const dPath = `M ${cx} ${cy} L ${x1} ${y1} A ${r} ${r} 0 ${large} 1 ${x2} ${y2} Z`;
          path.setAttribute('d', dPath);
          path.setAttribute('fill', color);
          group.appendChild(path);
          start = end;
        });
        svg.appendChild(group);

        // legend (render as foreignObject so it flows) — fallback if not supported will show nothing,
        // but we also append a simple right-side legend using SVG rect/text
        const legendX = 110;
        data.forEach((d, i) => {
          const color = d.color || HSvgPie._niceColor(i);
          const y = 20 + i * 16;
          const rect = document.createElementNS(NS, 'rect');
          rect.setAttribute('x', legendX);
          rect.setAttribute('y', y);
          rect.setAttribute('width', 10);
          rect.setAttribute('height', 10);
          rect.setAttribute('fill', color);
          svg.appendChild(rect);
          const txt = document.createElementNS(NS, 'text');
          txt.setAttribute('x', legendX + 14);
          txt.setAttribute('y', y + 9);
          txt.setAttribute('font-size', '9');
          txt.setAttribute('fill', getComputedStyle(document.documentElement).getPropertyValue('--t1') || '#fff');
          txt.textContent = `${d.label} (${d.value})`;
          svg.appendChild(txt);
        });

        // clear and append
        container.innerHTML = '';
        container.appendChild(svg);
      },
      _niceColor(i) {
        const colors = ['#f5a623','#2dd4bf','#60a5fa','#f87171','#a78bfa','#34d399'];
        return colors[i % colors.length];
      }
    };

    // init when document ready
    $(function () {
      HConfirm.init();
      HEditor.init();
      HIcons.init();
      HSvgPie.init();
      // expose for debugging / manual use
      window.HConfirm = HConfirm;
      window.HEditor = HEditor;
      window.HSvgPie = HSvgPie;
    });

  })(window, document, jQuery);
