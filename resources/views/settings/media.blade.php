@extends('layouts.app')

@section('title', 'Media Library')
@section('page_title', 'Media Library')

@section('topbar_extra')
  <span class="h-live-badge">
    <i class="fa-solid fa-photo-film"></i>
    Media Manager
  </span>
@endsection

@section('content')
<div class="hl-docs hl-settings h-drive">
  <div class="doc-head">
    <div>
      <div class="doc-title">Media Manager</div>
      <div class="doc-sub">Google Drive style media workspace with Haarray touch: folders, breadcrumb, preview pane, upload, resize, and multi-select actions.</div>
    </div>
    <span class="h-pill teal">{{ $storageLabel }}</span>
  </div>

  <div class="h-drive-shell" id="settings-media-shell">
    <aside class="h-drive-sidebar">
      <div class="h-drive-box">
        <div class="h-drive-box-title">Create & Upload</div>
        <div class="h-drive-stack">
          @if($canManageSettings)
            <button type="button" class="btn btn-primary btn-sm" id="settings-media-upload-trigger">
              <i class="fa-solid fa-upload me-2"></i>
              Upload Files
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="settings-media-create-open">
              <i class="fa-solid fa-folder-plus me-2"></i>
              New Folder
            </button>
            <input type="file" class="d-none" id="settings-media-upload-input" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,.ico,.mp3,.wav,.ogg,.m4a,.aac,.flac,image/*,audio/*">
          @else
            <div class="h-note">Read-only mode. You can browse and copy media URLs.</div>
          @endif
        </div>

        @if($canManageSettings)
          <div class="h-drive-create-inline" id="settings-media-create-inline" hidden>
            <input type="text" id="settings-media-create-name" class="form-control form-control-sm" placeholder="folder-name">
            <div class="h-row" style="gap:8px;">
              <button type="button" class="btn btn-sm btn-primary" id="settings-media-create-confirm">Create</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="settings-media-create-cancel">Cancel</button>
            </div>
          </div>
        @endif
      </div>

      <div class="h-drive-box">
        <div class="h-drive-box-title">Quick Access</div>
        <div class="h-drive-stack">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="settings-media-root">
            <i class="fa-solid fa-house me-2"></i>
            My Drive Root
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="settings-media-up">
            <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>
            Up One Level
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="settings-media-refresh">
            <i class="fa-solid fa-rotate me-2"></i>
            Refresh
          </button>
          <a href="{{ route('ui.filemanager.export') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm" id="settings-media-export">
            <i class="fa-solid fa-file-csv me-2"></i>
            Export CSV
          </a>
        </div>
      </div>

      <div class="h-drive-box">
        <div class="h-drive-box-title">Folders</div>
        <div id="settings-media-sidebar-folders" class="h-drive-folder-list">
          <div class="h-drive-muted">Loading folders...</div>
        </div>
      </div>

      <div class="h-drive-box">
        <div class="h-drive-box-title">Storage</div>
        <div class="h-drive-storage-grid">
          <div>
            <span>Disk</span>
            <strong id="settings-media-storage-disk">{{ strtoupper($storageDisk) }}</strong>
          </div>
          <div>
            <span>Current Path</span>
            <strong id="settings-media-storage-path">/uploads</strong>
          </div>
          <div>
            <span>Files</span>
            <strong id="settings-media-storage-count">0</strong>
          </div>
          <div>
            <span>Selected</span>
            <strong id="settings-media-storage-selected">0</strong>
          </div>
        </div>
      </div>
    </aside>

    <section class="h-drive-main" id="settings-media-main">
      <div class="h-drive-toolbar">
        <div class="h-drive-breadcrumb-wrap">
          <div class="h-drive-caption">Location</div>
          <div id="settings-media-breadcrumb" class="h-drive-breadcrumb"></div>
        </div>

        <div class="h-drive-toolbar-actions">
          <div class="h-drive-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="settings-media-search" class="form-control form-control-sm" placeholder="Search in current folder...">
          </div>

          <div class="btn-group btn-group-sm" role="group" aria-label="View mode">
            <button type="button" class="btn btn-outline-secondary" id="settings-media-view-grid" title="Grid view">
              <i class="fa-solid fa-grip"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="settings-media-view-list" title="List view">
              <i class="fa-solid fa-list"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="h-drive-selection" id="settings-media-selection" hidden>
        <div id="settings-media-selection-text">0 selected</div>
        <div class="h-row" style="gap:8px;flex-wrap:wrap;">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="settings-media-select-all">
            <i class="fa-solid fa-check-double me-2"></i>
            Select All
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="settings-media-clear-selection">
            <i class="fa-solid fa-xmark me-2"></i>
            Clear
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="settings-media-copy-selected">
            <i class="fa-solid fa-link me-2"></i>
            Copy URLs
          </button>
          @if($canManageSettings)
            <button type="button" class="btn btn-outline-secondary btn-sm" id="settings-media-resize-selected">
              <i class="fa-solid fa-expand me-2"></i>
              Resize
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="settings-media-delete-selected">
              <i class="fa-solid fa-trash me-2"></i>
              Delete
            </button>
          @endif
        </div>
      </div>

      <div class="h-drive-folder-row" id="settings-media-folder-row"></div>

      <div class="h-drive-content" id="settings-media-content">
        <div class="h-drive-empty" id="settings-media-empty" hidden>
          <i class="fa-regular fa-folder-open"></i>
          <span>No files found in this folder.</span>
        </div>

        <div class="h-drive-grid" id="settings-media-grid"></div>

        <div class="table-responsive" id="settings-media-list-wrap" hidden>
          <table class="table table-sm align-middle h-table-sticky-actions">
            <thead>
              <tr>
                <th style="width:42px;"></th>
                <th style="min-width:78px;">Preview</th>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Modified</th>
                <th class="h-col-actions">Actions</th>
              </tr>
            </thead>
            <tbody id="settings-media-list-body"></tbody>
          </table>
        </div>
      </div>
    </section>

    <aside class="h-drive-preview">
      <div class="h-drive-preview-head">Preview</div>
      <div class="h-drive-preview-body" id="settings-media-preview-body">
        <div class="h-drive-muted">Select a file to preview details.</div>
      </div>
    </aside>
  </div>
</div>
@endsection

@section('modals')
<div class="h-modal-overlay" id="settings-media-delete-modal">
  <div class="h-modal" style="max-width:520px;">
    <div class="h-modal-head">
      <div class="h-modal-title">Delete Media</div>
      <button class="h-modal-close">×</button>
    </div>
    <div class="h-modal-body">
      <p id="settings-media-delete-text" class="mb-3">Delete selected files permanently?</p>
      <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" data-modal-close>Cancel</button>
        <button type="button" class="btn btn-danger" id="settings-media-delete-confirm">
          <i class="fa-solid fa-trash me-2"></i>
          Delete
        </button>
      </div>
    </div>
  </div>
</div>

<div class="h-modal-overlay" id="settings-media-resize-modal">
  <div class="h-modal" style="max-width:520px;">
    <div class="h-modal-head">
      <div class="h-modal-title">Resize Image</div>
      <button class="h-modal-close">×</button>
    </div>
    <div class="h-modal-body">
      <form id="settings-media-resize-form">
        <input type="hidden" name="path" id="settings-media-resize-path" value="">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="h-label" style="display:block;">Max Width</label>
            <input type="number" class="form-control" name="width" min="32" max="4096" value="1280" required>
          </div>
          <div class="col-md-6">
            <label class="h-label" style="display:block;">Max Height</label>
            <input type="number" class="form-control" name="height" min="32" max="4096" value="1280" required>
          </div>
          <div class="col-12">
            <label class="h-switch">
              <input type="hidden" name="replace" value="0">
              <input type="checkbox" name="replace" value="1">
              <span class="track"><span class="thumb"></span></span>
              <span class="h-switch-text">Replace original file</span>
            </label>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
          <button type="button" class="btn btn-outline-secondary" data-modal-close>Cancel</button>
          <button type="submit" class="btn btn-primary" data-busy-text="Resizing...">
            <i class="fa-solid fa-expand me-2"></i>
            Resize
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
  const endpointList = String(document.body.dataset.fileManagerListUrl || '').trim();
  const endpointDelete = String(document.body.dataset.fileManagerDeleteUrl || '').trim();
  const endpointFolder = String(document.body.dataset.fileManagerFolderUrl || '').trim();
  const endpointExport = String(document.body.dataset.fileManagerExportUrl || '').trim();
  const endpointResize = String(document.body.dataset.fileManagerResizeUrl || '').trim();
  const endpointUpload = String(document.body.dataset.fileManagerUploadUrl || '').trim();
  const canManage = @json((bool) ($canManageSettings ?? false));

  if (!endpointList || !window.HApi) return;

  const folderStorageKey = 'h_media_drive_view_mode';
  const initialFolder = @json((string) ($initialFolder ?? ''));

  const el = {
    sidebarFolders: document.getElementById('settings-media-sidebar-folders'),
    storageDisk: document.getElementById('settings-media-storage-disk'),
    storagePath: document.getElementById('settings-media-storage-path'),
    storageCount: document.getElementById('settings-media-storage-count'),
    storageSelected: document.getElementById('settings-media-storage-selected'),

    uploadTrigger: document.getElementById('settings-media-upload-trigger'),
    uploadInput: document.getElementById('settings-media-upload-input'),
    createOpen: document.getElementById('settings-media-create-open'),
    createInline: document.getElementById('settings-media-create-inline'),
    createName: document.getElementById('settings-media-create-name'),
    createConfirm: document.getElementById('settings-media-create-confirm'),
    createCancel: document.getElementById('settings-media-create-cancel'),

    rootBtn: document.getElementById('settings-media-root'),
    upBtn: document.getElementById('settings-media-up'),
    refreshBtn: document.getElementById('settings-media-refresh'),
    exportLink: document.getElementById('settings-media-export'),

    breadcrumb: document.getElementById('settings-media-breadcrumb'),
    searchInput: document.getElementById('settings-media-search'),
    viewGridBtn: document.getElementById('settings-media-view-grid'),
    viewListBtn: document.getElementById('settings-media-view-list'),

    selectionWrap: document.getElementById('settings-media-selection'),
    selectionText: document.getElementById('settings-media-selection-text'),
    selectAllBtn: document.getElementById('settings-media-select-all'),
    clearSelectionBtn: document.getElementById('settings-media-clear-selection'),
    copySelectedBtn: document.getElementById('settings-media-copy-selected'),
    resizeSelectedBtn: document.getElementById('settings-media-resize-selected'),
    deleteSelectedBtn: document.getElementById('settings-media-delete-selected'),

    folderRow: document.getElementById('settings-media-folder-row'),
    content: document.getElementById('settings-media-content'),
    empty: document.getElementById('settings-media-empty'),
    grid: document.getElementById('settings-media-grid'),
    listWrap: document.getElementById('settings-media-list-wrap'),
    listBody: document.getElementById('settings-media-list-body'),

    previewBody: document.getElementById('settings-media-preview-body'),

    deleteText: document.getElementById('settings-media-delete-text'),
    deleteConfirm: document.getElementById('settings-media-delete-confirm'),
    resizeForm: document.getElementById('settings-media-resize-form'),
    resizePath: document.getElementById('settings-media-resize-path'),
  };

  const state = {
    folder: sanitizeFolder(initialFolder),
    query: '',
    view: readViewMode(),
    items: [],
    folders: [],
    storage: { mode: '', disk: '', label: '' },
    selected: new Set(),
    pendingDeletePaths: [],
    searchTimer: null,
  };

  function readViewMode() {
    try {
      const saved = String(localStorage.getItem(folderStorageKey) || '').trim().toLowerCase();
      return saved === 'list' ? 'list' : 'grid';
    } catch (error) {
      return 'grid';
    }
  }

  function openModal(id) {
    if (window.HModal) {
      window.HModal.open(id);
      return;
    }
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('show');
  }

  function closeModal(id) {
    if (window.HModal) {
      window.HModal.close(id);
      return;
    }
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('show');
  }

  function sanitizeFolder(value) {
    const cleaned = String(value || '').replace(/\\/g, '/').trim().replace(/^\/+|\/+$/g, '');
    if (!cleaned) return '';

    return cleaned
      .split('/')
      .map((part) => part.trim())
      .filter((part) => part && part !== '.' && part !== '..')
      .map((part) => part.replace(/[^a-zA-Z0-9._-]+/g, '-').replace(/^-+|-+$/g, ''))
      .filter(Boolean)
      .join('/');
  }

  function sanitizeSegment(value) {
    return String(value || '')
      .replace(/[\\/]+/g, '-')
      .replace(/[^a-zA-Z0-9._-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function escapeHtml(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function typeIcon(type) {
    const key = String(type || '').toLowerCase();
    if (key === 'image') return 'fa-regular fa-image';
    if (key === 'audio') return 'fa-solid fa-wave-square';
    return 'fa-regular fa-file-lines';
  }

  function formatBytesKB(sizeKb) {
    const size = Number(sizeKb || 0);
    if (!Number.isFinite(size) || size < 1) return '0 KB';
    if (size < 1024) return Math.round(size) + ' KB';
    return (size / 1024).toFixed(2) + ' MB';
  }

  function updateUrlFolderQuery() {
    if (!window.history || typeof window.history.replaceState !== 'function') return;
    const url = new URL(window.location.href);
    if (state.folder) {
      url.searchParams.set('folder', state.folder);
    } else {
      url.searchParams.delete('folder');
    }
    window.history.replaceState({}, '', url.toString());
  }

  function syncExportLink() {
    if (!el.exportLink || !endpointExport) return;
    const url = new URL(endpointExport, window.location.origin);
    if (state.folder) url.searchParams.set('folder', state.folder);
    el.exportLink.href = url.toString();
  }

  function setViewMode(nextMode, persist) {
    state.view = nextMode === 'list' ? 'list' : 'grid';

    if (el.viewGridBtn) el.viewGridBtn.classList.toggle('active', state.view === 'grid');
    if (el.viewListBtn) el.viewListBtn.classList.toggle('active', state.view === 'list');

    if (el.grid) el.grid.hidden = state.view !== 'grid';
    if (el.listWrap) el.listWrap.hidden = state.view !== 'list';

    if (persist !== false) {
      try {
        localStorage.setItem(folderStorageKey, state.view);
      } catch (error) {
        // Ignore storage errors.
      }
    }
  }

  function setFolder(nextFolder, reload) {
    state.folder = sanitizeFolder(nextFolder);
    syncExportLink();
    updateUrlFolderQuery();
    if (reload !== false) loadMedia();
  }

  function parentFolder(folder) {
    const clean = sanitizeFolder(folder);
    if (!clean.includes('/')) return '';
    return clean.slice(0, clean.lastIndexOf('/'));
  }

  function pruneSelection() {
    const lookup = new Set(state.items.map((item) => String(item.path || '')));
    const next = new Set();
    state.selected.forEach((path) => {
      if (lookup.has(path)) next.add(path);
    });
    state.selected = next;
  }

  function selectedItems() {
    if (!state.selected.size) return [];
    return state.items.filter((item) => state.selected.has(String(item.path || '')));
  }

  function renderBreadcrumb() {
    if (!el.breadcrumb) return;

    const parts = state.folder ? state.folder.split('/').filter(Boolean) : [];
    const nodes = [];

    nodes.push('<button type="button" class="h-drive-crumb is-root" data-open-folder=""><i class="fa-solid fa-hard-drive"></i><span>My Drive</span></button>');

    let build = '';
    parts.forEach((part) => {
      build = build ? (build + '/' + part) : part;
      nodes.push('<span class="h-drive-crumb-sep"><i class="fa-solid fa-chevron-right"></i></span>');
      nodes.push('<button type="button" class="h-drive-crumb" data-open-folder="' + escapeHtml(build) + '"><span>' + escapeHtml(part) + '</span></button>');
    });

    el.breadcrumb.innerHTML = nodes.join('');
  }

  function renderSidebarFolders() {
    if (!el.sidebarFolders) return;

    if (!Array.isArray(state.folders) || state.folders.length === 0) {
      el.sidebarFolders.innerHTML = '<div class="h-drive-muted">No subfolders</div>';
      return;
    }

    el.sidebarFolders.innerHTML = state.folders.map((folder) => {
      const path = escapeHtml(folder.path || '');
      const name = escapeHtml(folder.name || path || 'Folder');
      const active = String(folder.path || '') === state.folder ? ' is-active' : '';
      return '<button type="button" class="h-drive-folder-btn' + active + '" data-open-folder="' + path + '"><i class="fa-regular fa-folder"></i><span>' + name + '</span></button>';
    }).join('');
  }

  function renderFolderTiles() {
    if (!el.folderRow) return;

    if (!Array.isArray(state.folders) || state.folders.length === 0) {
      el.folderRow.innerHTML = '';
      return;
    }

    el.folderRow.innerHTML = state.folders.map((folder) => {
      const path = escapeHtml(folder.path || '');
      const name = escapeHtml(folder.name || path || 'Folder');
      return [
        '<button type="button" class="h-drive-folder-tile" data-open-folder="' + path + '">',
        '<i class="fa-regular fa-folder"></i>',
        '<span>' + name + '</span>',
        '</button>'
      ].join('');
    }).join('');
  }

  function itemPreviewHtml(item) {
    const type = String(item.type || 'file');
    const url = escapeHtml(item.url || '');
    const name = escapeHtml(item.name || 'file');

    if (type === 'image') {
      return '<img src="' + url + '" alt="' + name + '" loading="lazy">';
    }

    if (type === 'audio') {
      return '<div class="h-drive-audio-preview"><i class="fa-solid fa-wave-square"></i><audio controls preload="none" src="' + url + '"></audio></div>';
    }

    return '<div class="h-drive-file-icon"><i class="' + typeIcon(type) + '"></i></div>';
  }

  function renderGridItems(items) {
    if (!el.grid) return;

    el.grid.innerHTML = items.map((item) => {
      const pathRaw = String(item.path || '');
      const path = escapeHtml(pathRaw);
      const url = escapeHtml(item.url || '');
      const name = escapeHtml(item.name || 'file');
      const extension = escapeHtml(String(item.extension || '').toUpperCase());
      const size = escapeHtml(formatBytesKB(item.size_kb));
      const modified = escapeHtml(item.modified_at || '-');
      const type = String(item.type || 'file');
      const selected = state.selected.has(pathRaw);

      const resizeButton = canManage && type === 'image'
        ? '<button type="button" class="btn btn-outline-secondary btn-sm" data-resize-path="' + path + '" title="Resize"><i class="fa-solid fa-expand"></i></button>'
        : '';

      const deleteButton = canManage
        ? '<button type="button" class="btn btn-outline-danger btn-sm" data-delete-path="' + path + '" data-delete-name="' + name + '" title="Delete"><i class="fa-solid fa-trash"></i></button>'
        : '';

      return [
        '<article class="h-drive-card' + (selected ? ' is-selected' : '') + '" data-item-path="' + path + '">',
        '  <button type="button" class="h-drive-card-select" data-toggle-select="' + path + '" aria-pressed="' + (selected ? 'true' : 'false') + '">',
        '    <i class="fa-solid fa-check"></i>',
        '  </button>',
        '  <button type="button" class="h-drive-card-preview" data-open-preview="' + path + '">',
        itemPreviewHtml(item),
        '  </button>',
        '  <div class="h-drive-card-meta">',
        '    <div class="h-drive-card-name" title="' + name + '">' + name + '</div>',
        '    <div class="h-drive-card-sub">' + extension + ' • ' + size + '</div>',
        '    <div class="h-drive-card-sub">' + modified + '</div>',
        '  </div>',
        '  <div class="h-drive-card-actions">',
        '    <button type="button" class="btn btn-outline-secondary btn-sm" data-copy-url="' + url + '" title="Copy URL"><i class="fa-solid fa-link"></i></button>',
        resizeButton,
        deleteButton,
        '  </div>',
        '</article>'
      ].join('');
    }).join('');
  }

  function renderListItems(items) {
    if (!el.listBody) return;

    el.listBody.innerHTML = items.map((item) => {
      const pathRaw = String(item.path || '');
      const path = escapeHtml(pathRaw);
      const url = escapeHtml(item.url || '');
      const name = escapeHtml(item.name || 'file');
      const type = String(item.type || 'file');
      const typeLabel = escapeHtml(type.toUpperCase());
      const extLabel = escapeHtml(String(item.extension || '').toUpperCase());
      const size = escapeHtml(formatBytesKB(item.size_kb));
      const modified = escapeHtml(item.modified_at || '-');
      const selected = state.selected.has(pathRaw);

      const resizeButton = canManage && type === 'image'
        ? '<button type="button" class="btn btn-outline-secondary btn-sm h-action-icon" data-resize-path="' + path + '" title="Resize"><i class="fa-solid fa-expand"></i></button>'
        : '';

      const deleteButton = canManage
        ? '<button type="button" class="btn btn-outline-danger btn-sm h-action-icon" data-delete-path="' + path + '" data-delete-name="' + name + '" title="Delete"><i class="fa-solid fa-trash"></i></button>'
        : '';

      return [
        '<tr class="' + (selected ? 'is-selected' : '') + '" data-item-path="' + path + '">',
        '  <td><input type="checkbox" class="form-check-input" data-toggle-select="' + path + '"' + (selected ? ' checked' : '') + '></td>',
        '  <td><button type="button" class="h-drive-list-preview" data-open-preview="' + path + '">' + itemPreviewHtml(item) + '</button></td>',
        '  <td><div class="h-drive-list-name">' + name + '</div><div class="h-drive-list-path">' + path + '</div></td>',
        '  <td>' + typeLabel + ' <span class="h-muted">(' + extLabel + ')</span></td>',
        '  <td>' + size + '</td>',
        '  <td>' + modified + '</td>',
        '  <td class="h-col-actions"><span class="h-action-group">',
        '    <button type="button" class="btn btn-outline-secondary btn-sm h-action-icon" data-copy-url="' + url + '" title="Copy URL"><i class="fa-solid fa-link"></i></button>',
        resizeButton,
        deleteButton,
        '  </span></td>',
        '</tr>'
      ].join('');
    }).join('');
  }

  function renderSelectionState() {
    const selectedCount = state.selected.size;

    if (el.selectionWrap) el.selectionWrap.hidden = selectedCount === 0;
    if (el.selectionText) {
      el.selectionText.textContent = selectedCount + ' selected';
    }

    if (el.storageSelected) {
      el.storageSelected.textContent = String(selectedCount);
    }

    if (el.selectAllBtn) {
      const allSelected = selectedCount > 0 && selectedCount === state.items.length;
      el.selectAllBtn.innerHTML = allSelected
        ? '<i class="fa-solid fa-check-double me-2"></i>Selected All'
        : '<i class="fa-solid fa-check-double me-2"></i>Select All';
    }
  }

  function renderPreviewPanel() {
    if (!el.previewBody) return;

    const items = selectedItems();

    if (items.length !== 1) {
      const imageCount = state.items.filter((item) => String(item.type || '') === 'image').length;
      const audioCount = state.items.filter((item) => String(item.type || '') === 'audio').length;

      el.previewBody.innerHTML = [
        '<div class="h-drive-preview-summary">',
        '<div><span>Total files</span><strong>' + state.items.length + '</strong></div>',
        '<div><span>Images</span><strong>' + imageCount + '</strong></div>',
        '<div><span>Audio</span><strong>' + audioCount + '</strong></div>',
        '<div><span>Folder</span><strong>' + escapeHtml(state.folder || 'root') + '</strong></div>',
        '</div>',
        '<div class="h-drive-muted" style="margin-top:10px;">Select one file to view detailed preview and actions.</div>'
      ].join('');
      return;
    }

    const item = items[0];
    const name = escapeHtml(item.name || 'file');
    const path = escapeHtml(item.path || '');
    const url = escapeHtml(item.url || '');
    const type = String(item.type || 'file');
    const extension = escapeHtml(String(item.extension || '').toUpperCase());
    const size = escapeHtml(formatBytesKB(item.size_kb));
    const modified = escapeHtml(item.modified_at || '-');

    const preview = type === 'image'
      ? '<img src="' + url + '" alt="' + name + '" class="h-drive-preview-image">'
      : (type === 'audio'
          ? '<audio controls preload="none" src="' + url + '" class="w-100"></audio>'
          : '<div class="h-drive-file-icon lg"><i class="' + typeIcon(type) + '"></i></div>');

    const resizeButton = canManage && type === 'image'
      ? '<button type="button" class="btn btn-outline-secondary btn-sm" data-resize-path="' + path + '"><i class="fa-solid fa-expand me-2"></i>Resize</button>'
      : '';

    const deleteButton = canManage
      ? '<button type="button" class="btn btn-outline-danger btn-sm" data-delete-path="' + path + '" data-delete-name="' + name + '"><i class="fa-solid fa-trash me-2"></i>Delete</button>'
      : '';

    el.previewBody.innerHTML = [
      '<div class="h-drive-preview-media">' + preview + '</div>',
      '<div class="h-drive-preview-name" title="' + name + '">' + name + '</div>',
      '<div class="h-drive-preview-meta"><span>Path</span><code>' + path + '</code></div>',
      '<div class="h-drive-preview-meta"><span>Type</span><strong>' + escapeHtml(type.toUpperCase()) + ' (' + extension + ')</strong></div>',
      '<div class="h-drive-preview-meta"><span>Size</span><strong>' + size + '</strong></div>',
      '<div class="h-drive-preview-meta"><span>Modified</span><strong>' + modified + '</strong></div>',
      '<div class="h-drive-preview-actions">',
      '<button type="button" class="btn btn-outline-secondary btn-sm" data-copy-url="' + url + '"><i class="fa-solid fa-link me-2"></i>Copy URL</button>',
      '<a href="' + url + '" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Open</a>',
      resizeButton,
      deleteButton,
      '</div>'
    ].join('');
  }

  function renderMain() {
    const hasItems = Array.isArray(state.items) && state.items.length > 0;

    if (el.empty) el.empty.hidden = hasItems;
    if (el.storageCount) el.storageCount.textContent = String(state.items.length);
    if (el.storagePath) el.storagePath.textContent = '/uploads' + (state.folder ? '/' + state.folder : '');
    if (el.storageDisk && state.storage.disk) el.storageDisk.textContent = String(state.storage.disk || '').toUpperCase();

    renderGridItems(state.items);
    renderListItems(state.items);
    renderSelectionState();
    renderPreviewPanel();
    setViewMode(state.view, false);
  }

  function renderAll() {
    renderBreadcrumb();
    renderSidebarFolders();
    renderFolderTiles();
    renderMain();
  }

  function showLoading() {
    if (el.grid) {
      el.grid.innerHTML = '<div class="h-drive-loading"><i class="fa-solid fa-spinner fa-spin"></i><span>Loading files...</span></div>';
    }
    if (el.listBody) {
      el.listBody.innerHTML = '<tr><td colspan="7" class="text-center h-muted py-4"><i class="fa-solid fa-spinner fa-spin me-2"></i>Loading...</td></tr>';
    }
    if (el.sidebarFolders) {
      el.sidebarFolders.innerHTML = '<div class="h-drive-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i>Loading folders...</div>';
    }
    if (el.folderRow) {
      el.folderRow.innerHTML = '';
    }
  }

  function loadMedia() {
    showLoading();

    window.HApi.get(endpointList, {
      folder: state.folder,
      q: state.query,
      limit: 300,
    }).done((payload) => {
      state.folder = sanitizeFolder(String(payload.current_folder || state.folder || ''));
      state.items = Array.isArray(payload.items) ? payload.items : [];
      state.folders = Array.isArray(payload.folders) ? payload.folders : [];
      state.storage = payload && payload.storage ? payload.storage : {};

      pruneSelection();
      syncExportLink();
      updateUrlFolderQuery();
      renderAll();
    }).fail((xhr) => {
      const message = xhr && xhr.responseJSON && xhr.responseJSON.message
        ? xhr.responseJSON.message
        : 'Unable to load media files.';

      if (el.grid) {
        el.grid.innerHTML = '<div class="h-drive-loading is-error"><i class="fa-regular fa-circle-xmark"></i><span>' + escapeHtml(message) + '</span></div>';
      }
      if (el.listBody) {
        el.listBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">' + escapeHtml(message) + '</td></tr>';
      }
      if (window.HToast) HToast.error(message);
    });
  }

  function toggleSelection(path, additive) {
    const cleanPath = String(path || '').trim();
    if (!cleanPath) return;

    if (!additive) {
      state.selected.clear();
      state.selected.add(cleanPath);
      renderMain();
      return;
    }

    if (state.selected.has(cleanPath)) {
      state.selected.delete(cleanPath);
    } else {
      state.selected.add(cleanPath);
    }

    renderMain();
  }

  function clearSelection() {
    state.selected.clear();
    renderMain();
  }

  function copyText(text) {
    const clean = String(text || '').trim();
    if (!clean) return;

    if (navigator && navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
      navigator.clipboard.writeText(clean)
        .then(() => window.HToast && HToast.success('Copied to clipboard.'))
        .catch(() => window.HToast && HToast.info(clean));
      return;
    }

    if (window.HToast) HToast.info(clean);
  }

  function copySelectedUrls() {
    const items = selectedItems();
    if (!items.length) {
      if (window.HToast) HToast.warning('Select at least one file first.');
      return;
    }

    const urls = items.map((item) => String(item.url || '').trim()).filter(Boolean);
    if (!urls.length) {
      if (window.HToast) HToast.warning('No URLs found for selected files.');
      return;
    }

    copyText(urls.join('\n'));
  }

  function openDeleteModal(paths, label) {
    if (!canManage) {
      if (window.HToast) HToast.warning('Read-only mode: delete is disabled.');
      return;
    }

    const cleanPaths = Array.from(new Set((paths || []).map((path) => String(path || '').trim()).filter(Boolean)));
    if (!cleanPaths.length) return;

    state.pendingDeletePaths = cleanPaths;

    if (el.deleteText) {
      if (cleanPaths.length === 1) {
        el.deleteText.textContent = 'Delete "' + (label || cleanPaths[0]) + '" permanently?';
      } else {
        el.deleteText.textContent = 'Delete ' + cleanPaths.length + ' selected files permanently?';
      }
    }

    openModal('settings-media-delete-modal');
  }

  function deleteFileRequest(path) {
    return new Promise((resolve, reject) => {
      window.HApi.post(endpointDelete, { path }, {
        dataType: 'json',
        headers: { Accept: 'application/json' },
      }).done(resolve).fail(reject);
    });
  }

  async function confirmDeleteQueue() {
    if (!endpointDelete) {
      if (window.HToast) HToast.error('Delete endpoint is not configured.');
      return;
    }

    const queue = Array.from(new Set(state.pendingDeletePaths || []));
    if (!queue.length) return;

    if (el.deleteConfirm) {
      el.deleteConfirm.disabled = true;
      el.deleteConfirm.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Deleting...';
    }

    let okCount = 0;
    let failCount = 0;

    for (const path of queue) {
      try {
        await deleteFileRequest(path);
        okCount++;
      } catch (error) {
        failCount++;
      }
    }

    closeModal('settings-media-delete-modal');
    state.pendingDeletePaths = [];
    clearSelection();

    if (el.deleteConfirm) {
      el.deleteConfirm.disabled = false;
      el.deleteConfirm.innerHTML = '<i class="fa-solid fa-trash me-2"></i>Delete';
    }

    if (window.HToast) {
      if (okCount > 0 && failCount === 0) {
        HToast.success(okCount + ' file(s) deleted.');
      } else if (okCount > 0 && failCount > 0) {
        HToast.warning(okCount + ' deleted, ' + failCount + ' failed.');
      } else {
        HToast.error('Unable to delete selected files.');
      }
    }

    loadMedia();
  }

  function uploadFile(file) {
    if (!canManage) {
      if (window.HToast) HToast.warning('Read-only mode: upload is disabled.');
      return;
    }

    if (!endpointUpload) {
      if (window.HToast) HToast.error('Upload endpoint is not configured.');
      return;
    }

    if (!file) {
      if (window.HToast) HToast.warning('Choose a file first.');
      return;
    }

    const token = String((document.querySelector('meta[name="csrf-token"]') || {}).content || '');
    const data = new FormData();
    data.append('file', file);
    data.append('folder', state.folder);

    $.ajax({
      url: endpointUpload,
      method: 'POST',
      data,
      processData: false,
      contentType: false,
      headers: token ? { 'X-CSRF-TOKEN': token } : {},
    }).done((payload) => {
      if (el.uploadInput) el.uploadInput.value = '';

      const item = payload && payload.item ? payload.item : null;
      if (item && item.path) {
        state.selected.clear();
        state.selected.add(String(item.path));
      }

      if (window.HToast) HToast.success('File uploaded successfully.');
      loadMedia();
    }).fail((xhr) => {
      const message = xhr && xhr.responseJSON && xhr.responseJSON.message
        ? xhr.responseJSON.message
        : 'Upload failed.';
      if (window.HToast) HToast.error(message);
    });
  }

  function createFolder() {
    if (!canManage) {
      if (window.HToast) HToast.warning('Read-only mode: folder create is disabled.');
      return;
    }

    if (!endpointFolder) {
      if (window.HToast) HToast.error('Folder endpoint is not configured.');
      return;
    }

    const raw = String((el.createName && el.createName.value) ? el.createName.value : '').trim();
    const name = sanitizeSegment(raw);
    if (!name) {
      if (window.HToast) HToast.warning('Enter a valid folder name.');
      return;
    }

    window.HApi.post(endpointFolder, {
      parent: state.folder,
      name,
    }, {
      dataType: 'json',
      headers: { Accept: 'application/json' },
    }).done((payload) => {
      if (el.createName) el.createName.value = '';
      if (el.createInline) el.createInline.hidden = true;

      const next = [state.folder, name].filter(Boolean).join('/');
      setFolder(next, true);

      if (window.HToast) {
        HToast.success(payload && payload.message ? payload.message : 'Folder created.');
      }
    }).fail((xhr) => {
      const message = xhr && xhr.responseJSON && xhr.responseJSON.message
        ? xhr.responseJSON.message
        : 'Unable to create folder.';
      if (window.HToast) HToast.error(message);
    });
  }

  function openResize(path) {
    if (!canManage) {
      if (window.HToast) HToast.warning('Read-only mode: resize is disabled.');
      return;
    }

    if (!el.resizePath) return;
    el.resizePath.value = String(path || '');
    openModal('settings-media-resize-modal');
  }

  function resizeImage(form) {
    if (!endpointResize) {
      if (window.HToast) HToast.error('Resize endpoint is not configured.');
      return;
    }

    const data = new FormData(form);
    window.HApi.post(endpointResize, data, {
      dataType: 'json',
      processData: false,
      contentType: false,
      headers: { Accept: 'application/json' },
    }).done((payload) => {
      closeModal('settings-media-resize-modal');
      if (window.HToast) HToast.success(payload && payload.message ? payload.message : 'Image resized.');
      loadMedia();
    }).fail((xhr) => {
      const message = xhr && xhr.responseJSON && xhr.responseJSON.message
        ? xhr.responseJSON.message
        : 'Unable to resize image.';
      if (window.HToast) HToast.error(message);
    });
  }

  if (el.rootBtn) {
    el.rootBtn.addEventListener('click', () => setFolder('', true));
  }

  if (el.upBtn) {
    el.upBtn.addEventListener('click', () => setFolder(parentFolder(state.folder), true));
  }

  if (el.refreshBtn) {
    el.refreshBtn.addEventListener('click', () => loadMedia());
  }

  if (el.viewGridBtn) {
    el.viewGridBtn.addEventListener('click', () => setViewMode('grid', true));
  }

  if (el.viewListBtn) {
    el.viewListBtn.addEventListener('click', () => setViewMode('list', true));
  }

  if (el.searchInput) {
    el.searchInput.addEventListener('input', () => {
      state.query = String(el.searchInput.value || '').trim();
      window.clearTimeout(state.searchTimer);
      state.searchTimer = window.setTimeout(() => loadMedia(), 180);
    });
  }

  if (el.uploadTrigger && el.uploadInput) {
    el.uploadTrigger.addEventListener('click', () => el.uploadInput.click());
    el.uploadInput.addEventListener('change', () => {
      const file = el.uploadInput.files && el.uploadInput.files[0] ? el.uploadInput.files[0] : null;
      uploadFile(file);
    });
  }

  if (el.createOpen && el.createInline) {
    el.createOpen.addEventListener('click', () => {
      el.createInline.hidden = !el.createInline.hidden;
      if (!el.createInline.hidden && el.createName) el.createName.focus();
    });
  }

  if (el.createCancel && el.createInline && el.createName) {
    el.createCancel.addEventListener('click', () => {
      el.createInline.hidden = true;
      el.createName.value = '';
    });
  }

  if (el.createConfirm) {
    el.createConfirm.addEventListener('click', createFolder);
  }

  if (el.createName) {
    el.createName.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter') return;
      event.preventDefault();
      createFolder();
    });
  }

  if (el.selectAllBtn) {
    el.selectAllBtn.addEventListener('click', () => {
      const allSelected = state.items.length > 0 && state.selected.size === state.items.length;
      if (allSelected) {
        state.selected.clear();
      } else {
        state.selected = new Set(state.items.map((item) => String(item.path || '')));
      }
      renderMain();
    });
  }

  if (el.clearSelectionBtn) {
    el.clearSelectionBtn.addEventListener('click', clearSelection);
  }

  if (el.copySelectedBtn) {
    el.copySelectedBtn.addEventListener('click', copySelectedUrls);
  }

  if (el.deleteSelectedBtn) {
    el.deleteSelectedBtn.addEventListener('click', () => {
      const items = selectedItems();
      if (!items.length) {
        if (window.HToast) HToast.warning('Select files to delete.');
        return;
      }
      openDeleteModal(items.map((item) => String(item.path || '')));
    });
  }

  if (el.resizeSelectedBtn) {
    el.resizeSelectedBtn.addEventListener('click', () => {
      const items = selectedItems();
      if (items.length !== 1 || String(items[0].type || '') !== 'image') {
        if (window.HToast) HToast.warning('Select a single image to resize.');
        return;
      }
      openResize(items[0].path || '');
    });
  }

  if (el.deleteConfirm) {
    el.deleteConfirm.addEventListener('click', () => {
      confirmDeleteQueue();
    });
  }

  if (el.resizeForm) {
    el.resizeForm.addEventListener('submit', (event) => {
      event.preventDefault();
      resizeImage(el.resizeForm);
    });
  }

  if (el.content) {
    const shell = document.getElementById('settings-media-shell');
    if (shell) {
      shell.addEventListener('click', (event) => {
        const folderBtn = event.target.closest('[data-open-folder]');
        if (!folderBtn) return;
        const folder = String(folderBtn.getAttribute('data-open-folder') || '');
        setFolder(folder, true);
      });
    }

    el.content.addEventListener('click', (event) => {
      const selectBtn = event.target.closest('[data-toggle-select]');
      if (selectBtn) {
        const path = String(selectBtn.getAttribute('data-toggle-select') || '');
        const isCheckbox = selectBtn instanceof HTMLInputElement && String(selectBtn.type || '').toLowerCase() === 'checkbox';
        const additive = isCheckbox || event.metaKey || event.ctrlKey || event.shiftKey;
        toggleSelection(path, additive);
        return;
      }

      const previewBtn = event.target.closest('[data-open-preview]');
      if (previewBtn) {
        const path = String(previewBtn.getAttribute('data-open-preview') || '');
        toggleSelection(path, false);
        return;
      }

      const copyBtn = event.target.closest('[data-copy-url]');
      if (copyBtn) {
        const url = String(copyBtn.getAttribute('data-copy-url') || '').trim();
        copyText(url);
        return;
      }

      const resizeBtn = event.target.closest('[data-resize-path]');
      if (resizeBtn) {
        const path = String(resizeBtn.getAttribute('data-resize-path') || '').trim();
        if (!path) return;
        openResize(path);
        return;
      }

      const deleteBtn = event.target.closest('[data-delete-path]');
      if (deleteBtn) {
        const path = String(deleteBtn.getAttribute('data-delete-path') || '').trim();
        const name = String(deleteBtn.getAttribute('data-delete-name') || path).trim();
        if (!path) return;
        openDeleteModal([path], name);
        return;
      }
    });

    el.content.addEventListener('dragenter', (event) => {
      if (!canManage) return;
      event.preventDefault();
      el.content.classList.add('is-dragover');
    });

    el.content.addEventListener('dragover', (event) => {
      if (!canManage) return;
      event.preventDefault();
      event.dataTransfer.dropEffect = 'copy';
      el.content.classList.add('is-dragover');
    });

    el.content.addEventListener('dragleave', (event) => {
      if (!canManage) return;
      if (!el.content.contains(event.relatedTarget)) {
        el.content.classList.remove('is-dragover');
      }
    });

    el.content.addEventListener('drop', (event) => {
      if (!canManage) return;
      event.preventDefault();
      el.content.classList.remove('is-dragover');

      const files = event.dataTransfer && event.dataTransfer.files
        ? Array.from(event.dataTransfer.files)
        : [];
      if (!files.length) return;
      uploadFile(files[0]);
    });
  }

  if (el.previewBody) {
    el.previewBody.addEventListener('click', (event) => {
      const copyBtn = event.target.closest('[data-copy-url]');
      if (copyBtn) {
        const url = String(copyBtn.getAttribute('data-copy-url') || '').trim();
        copyText(url);
        return;
      }

      const resizeBtn = event.target.closest('[data-resize-path]');
      if (resizeBtn) {
        const path = String(resizeBtn.getAttribute('data-resize-path') || '').trim();
        if (!path) return;
        openResize(path);
        return;
      }

      const deleteBtn = event.target.closest('[data-delete-path]');
      if (deleteBtn) {
        const path = String(deleteBtn.getAttribute('data-delete-path') || '').trim();
        const name = String(deleteBtn.getAttribute('data-delete-name') || path).trim();
        if (!path) return;
        openDeleteModal([path], name);
      }
    });
  }

  syncExportLink();
  setViewMode(state.view, false);
  loadMedia();
})();
</script>
@endsection
