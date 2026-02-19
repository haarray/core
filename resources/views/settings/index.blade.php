@extends('layouts.app')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('topbar_extra')
  <span class="h-live-badge">
    <i class="fa-solid fa-sliders"></i>
    Settings Center
  </span>
@endsection

@section('content')
@php
  $defaultSettingsTab = (string) request()->query('tab', 'settings-app');
  $themeColor = (string) ($uiBranding['theme_color'] ?? '#f5a623');
  $logoUrl = (string) ($uiBranding['logo_url'] ?? '');
  $faviconUrl = (string) ($uiBranding['favicon_url'] ?? '');
  $appIconUrl = (string) ($uiBranding['app_icon_url'] ?? '');
  $dbLabel = ($dbConnectionInfo['database'] ?? '') !== '' ? (string) $dbConnectionInfo['database'] : 'n/a';
  $mlDefaults = (array) ($mlDiagnostics['probe_defaults'] ?? []);
  $selectedUserId = (int) request()->query('user', 0);
  $selectedRoleId = (int) request()->query('role', 0);

  $userPayloadMap = [];
  foreach ($users as $managedUser) {
      $currentRole = $hasSpatiePermissions
          ? ($managedUser->roles->first()->name ?? $managedUser->role ?? 'user')
          : ($managedUser->role ?? 'user');
      $userPayloadMap[$managedUser->id] = [
          'id' => $managedUser->id,
          'name' => (string) $managedUser->name,
          'email' => (string) $managedUser->email,
          'role' => (string) $currentRole,
          'telegram_chat_id' => (string) ($managedUser->telegram_chat_id ?? ''),
          'receive_in_app_notifications' => (bool) $managedUser->receive_in_app_notifications,
          'receive_telegram_notifications' => (bool) $managedUser->receive_telegram_notifications,
          'browser_notifications_enabled' => (bool) $managedUser->browser_notifications_enabled,
          'permissions' => $hasSpatiePermissions
              ? $managedUser->permissions->pluck('name')->values()->all()
              : [],
      ];
  }

  $rolePayloadMap = [];
  foreach ($roleCatalog as $roleRow) {
      $rolePayloadMap[(int) $roleRow['id']] = [
          'id' => (int) $roleRow['id'],
          'name' => (string) $roleRow['name'],
          'permissions' => (array) ($roleRow['permissions'] ?? []),
      ];
  }

  $extraPermissionOptions = collect($permissionOptions)
      ->reject(fn ($permission) => in_array($permission, $modulePermissionNames, true))
      ->values();
@endphp

<div class="hl-docs hl-settings">
  <div class="doc-head">
    <div>
      <div class="doc-title">Settings Control Panel</div>
      <div class="doc-sub">
        One page for app branding, users, roles, activity, notifications, system config and diagnostics.
      </div>
    </div>
    <span class="h-pill teal">DB: {{ $dbLabel }}</span>
  </div>

  <div class="h-tab-shell h-settings-shell" id="settings-main-tabs" data-ui-tabs data-default-tab="{{ $defaultSettingsTab }}">
    <div class="h-tab-nav" role="tablist" aria-label="Settings sections">
      <button type="button" class="h-tab-btn" data-tab-btn="settings-app"><i class="fa-solid fa-palette"></i> App & Branding</button>
      @if($canViewUsers)
        <button type="button" class="h-tab-btn" data-tab-btn="settings-users"><i class="fa-solid fa-users"></i> Users</button>
      @endif
      @if($canManageSettings)
        <button type="button" class="h-tab-btn" data-tab-btn="settings-roles"><i class="fa-solid fa-user-lock"></i> Roles & Access</button>
      @endif
      <button type="button" class="h-tab-btn" data-tab-btn="settings-activity"><i class="fa-solid fa-chart-line"></i> Activity</button>
      <button type="button" class="h-tab-btn" data-tab-btn="settings-security"><i class="fa-solid fa-user-shield"></i> Security</button>
      <button type="button" class="h-tab-btn" data-tab-btn="settings-notifications"><i class="fa-solid fa-bell"></i> Notifications</button>
      @if($canManageSettings)
        <button type="button" class="h-tab-btn" data-tab-btn="settings-system"><i class="fa-solid fa-gear"></i> System</button>
        <button type="button" class="h-tab-btn" data-tab-btn="settings-diagnostics"><i class="fa-solid fa-stethoscope"></i> Diagnostics</button>
      @endif
    </div>

    <div class="h-tab-panel" data-tab-panel="settings-app">
      @if($canManageSettings)
        <div class="h-card-soft mb-3">
          <div class="head">
            <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Branding, Icons, Theme</div>
            <div class="h-muted" style="font-size:13px;">Set app title, theme color, logo/favicon/app icon and manage assets from one place.</div>
          </div>
          <div class="body">
            <form method="POST" action="{{ route('settings.branding') }}" enctype="multipart/form-data" data-spa>
              @csrf
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">App Name</label>
                  <input type="text" name="ui_display_name" class="form-control" value="{{ old('ui_display_name', $uiBranding['display_name'] ?? config('app.name')) }}" required>
                </div>
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">Brand Subtitle</label>
                  <input type="text" name="ui_brand_subtitle" class="form-control" value="{{ old('ui_brand_subtitle', $uiBranding['brand_subtitle'] ?? ('by ' . config('haarray.brand_name'))) }}">
                </div>
                <div class="col-md-2">
                  <label class="h-label" style="display:block;">Mark</label>
                  <input type="text" name="ui_brand_mark" class="form-control" maxlength="8" value="{{ old('ui_brand_mark', $uiBranding['brand_mark'] ?? 'H') }}" required>
                </div>
                <div class="col-md-2">
                  <label class="h-label" style="display:block;">Theme Color</label>
                  <input type="color" name="ui_theme_color" class="form-control form-control-color" value="{{ old('ui_theme_color', $themeColor) }}">
                </div>

                <div class="col-md-4">
                  <label class="h-label" style="display:block;">Logo URL</label>
                  <input type="text" name="ui_logo_url" id="ui-logo-url" class="form-control" value="{{ old('ui_logo_url', $logoUrl) }}" placeholder="https://.../logo.png">
                </div>
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">Favicon URL</label>
                  <input type="text" name="ui_favicon_url" id="ui-favicon-url" class="form-control" value="{{ old('ui_favicon_url', $faviconUrl) }}" placeholder="https://.../favicon.ico">
                </div>
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">App Icon URL</label>
                  <input type="text" name="ui_app_icon_url" id="ui-app-icon-url" class="form-control" value="{{ old('ui_app_icon_url', $appIconUrl) }}" placeholder="https://.../app-icon.png">
                </div>

                <div class="col-md-4">
                  <label class="h-label" style="display:block;">Upload Logo</label>
                  <input type="file" name="ui_logo_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg,image/*">
                </div>
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">Upload Favicon</label>
                  <input type="file" name="ui_favicon_file" class="form-control" accept=".ico,.png,.webp,.svg,image/*">
                </div>
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">Upload App Icon</label>
                  <input type="file" name="ui_app_icon_file" class="form-control" accept=".ico,.jpg,.jpeg,.png,.webp,.svg,image/*">
                </div>
              </div>

              <div class="h-note mt-3">
                Active DB from `.env`: <code>{{ $dbConnectionInfo['connection'] }}</code> / <code>{{ $dbConnectionInfo['database'] ?: 'n/a' }}</code>
              </div>

              <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary" data-busy-text="Saving...">
                  <i class="fa-solid fa-floppy-disk me-2"></i>
                  Save App Settings
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="h-card-soft mb-3">
          <div class="head">
            <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Media Library</div>
            <div class="h-muted" style="font-size:13px;">Click any asset below to apply as logo, favicon, or app icon.</div>
          </div>
          <div class="body">
            @if(empty($mediaLibrary))
              <div class="h-note">No branding assets uploaded yet.</div>
            @else
              <div class="h-media-grid">
                @foreach($mediaLibrary as $asset)
                  <div class="h-media-card">
                    <img src="{{ $asset['url'] }}" alt="{{ $asset['name'] }}">
                    <div class="h-media-meta">
                      <div class="h-media-name" title="{{ $asset['name'] }}">{{ $asset['name'] }}</div>
                      <div class="h-muted" style="font-size:11px;">{{ $asset['size_kb'] }} KB • {{ $asset['modified_at'] }}</div>
                    </div>
                    <div class="h-media-actions">
                      <button type="button" class="btn btn-outline-secondary btn-sm" data-media-pick data-media-target="ui-logo-url" data-media-url="{{ $asset['url'] }}">Logo</button>
                      <button type="button" class="btn btn-outline-secondary btn-sm" data-media-pick data-media-target="ui-favicon-url" data-media-url="{{ $asset['url'] }}">Favicon</button>
                      <button type="button" class="btn btn-outline-secondary btn-sm" data-media-pick data-media-target="ui-app-icon-url" data-media-url="{{ $asset['url'] }}">App Icon</button>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      @else
        <div class="h-note">Only users with <code>manage settings</code> can update branding.</div>
      @endif
    </div>

    @if($canViewUsers)
      <div class="h-tab-panel" data-tab-panel="settings-users">
        <div class="h-card-soft mb-3">
          <div class="head h-split">
            <div>
              <div style="font-family:var(--fd);font-size:16px;font-weight:700;">User Directory</div>
              <div class="h-muted" style="font-size:13px;">Yajra DataTable listing with edit action and channel status.</div>
            </div>
            @if($canManageUsers)
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm" id="h-user-create-open">
                  <i class="fa-solid fa-user-plus me-2"></i>
                  Create User
                </button>
                <a href="{{ route('settings.users.export') }}" class="btn btn-outline-secondary btn-sm">
                  <i class="fa-solid fa-file-export me-2"></i>
                  Export Users
                </a>
              </div>
            @endif
          </div>

          <div class="body">
            <div class="table-responsive">
              <table
                class="table table-sm align-middle"
                data-h-datatable
                data-endpoint="{{ route('ui.datatables.users') }}"
                data-page-length="10"
                data-length-menu="10,20,50,100"
                data-order-col="0"
                data-order-dir="desc"
              >
                <thead>
                  <tr>
                    <th data-col="id">ID</th>
                    <th data-col="name">Name</th>
                    <th data-col="email">Email</th>
                    <th data-col="role">Role</th>
                    <th data-col="channels">Channels</th>
                    <th data-col="created_at">Joined</th>
                    <th data-col="actions">Action</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        @if($canManageUsers)
          <div class="h-card-soft mb-3">
            <div class="head">
              <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Import Users</div>
              <div class="h-muted" style="font-size:13px;">Import users from xlsx/xls/csv. Existing email updates current user.</div>
            </div>
            <div class="body">
              <form method="POST" action="{{ route('settings.users.import') }}" enctype="multipart/form-data" data-spa>
                @csrf
                <div class="row g-2 align-items-end">
                  <div class="col-md-9">
                    <label class="h-label" style="display:block;">Import File</label>
                    <input type="file" name="import_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                  </div>
                  <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100" data-busy-text="Importing...">Import Users</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        @endif
      </div>
    @endif

    @if($canManageSettings)
      <div class="h-tab-panel" data-tab-panel="settings-roles">
        @if(!$hasSpatiePermissions)
          <div class="h-note">Spatie role/permission tables are not ready. Run migrations first.</div>
        @else
          <div class="h-card-soft mb-3">
            <div class="head h-split">
              <div>
                <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Roles Directory</div>
                <div class="h-muted" style="font-size:13px;">Manage roles and route access matrix from one tab.</div>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="h-role-create-reset">
                <i class="fa-solid fa-plus me-2"></i>
                New Role
              </button>
            </div>
            <div class="body">
              <div class="table-responsive">
                <table
                  class="table table-sm align-middle"
                  data-h-datatable
                  data-endpoint="{{ route('ui.datatables.roles') }}"
                  data-page-length="10"
                  data-length-menu="10,20,50,100"
                  data-order-col="0"
                  data-order-dir="desc"
                >
                  <thead>
                    <tr>
                      <th data-col="id">ID</th>
                      <th data-col="name">Role</th>
                      <th data-col="permissions_count">Permissions</th>
                      <th data-col="users_count">Users</th>
                      <th data-col="is_protected">Protected</th>
                      <th data-col="actions">Action</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="h-grid-main h-rbac-grid mb-3" id="role-editor">
            <div class="h-card-soft">
              <div class="head h-split">
                <div>
                  <div style="font-family:var(--fd);font-size:16px;font-weight:700;" id="h-role-form-title">Create Role</div>
                  <div class="h-muted" style="font-size:13px;">Use one form for both create and edit.</div>
                </div>
              </div>
              <div class="body">
                <form method="POST" action="{{ route('settings.roles.store') }}" id="h-role-form" data-spa data-store-action="{{ route('settings.roles.store') }}" data-update-template="{{ route('settings.roles.update', ['role' => '__ID__']) }}">
                  @csrf
                  <span id="h-role-method-holder"></span>
                  <div class="mb-2">
                    <label class="h-label" style="display:block;">Role Name</label>
                    <input type="text" name="name" id="h-role-name" class="form-control" required>
                  </div>
                  <div class="mb-2">
                    <label class="h-label" style="display:block;">Permissions</label>
                    <select name="permissions[]" id="h-role-permissions" class="form-select" data-h-select multiple>
                      @foreach($permissionOptions as $permissionName)
                        <option value="{{ $permissionName }}">{{ $permissionName }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary" id="h-role-submit-btn" data-busy-text="Saving...">
                      <i class="fa-solid fa-floppy-disk me-2"></i>
                      Save Role
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <div class="h-card-soft">
              <div class="head">
                <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Delete Roles</div>
                <div class="h-muted" style="font-size:13px;">Protected roles and roles with users are locked.</div>
              </div>
              <div class="body">
                <div class="table-responsive">
                  <table class="table table-sm align-middle">
                    <thead>
                      <tr>
                        <th>Role</th>
                        <th>Users</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($roleCatalog as $roleRow)
                        @php
                          $roleName = (string) $roleRow['name'];
                          $isProtected = in_array($roleName, $protectedRoleNames, true);
                        @endphp
                        <tr>
                          <td>{{ strtoupper($roleName) }}</td>
                          <td>{{ (int) $roleRow['users_count'] }}</td>
                          <td>
                            <form method="POST" action="{{ route('settings.roles.delete', $roleRow['id']) }}" data-spa data-confirm="true" data-confirm-title="Delete role {{ $roleName }}?" data-confirm-text="This cannot be undone." data-confirm-ok="Delete" data-confirm-cancel="Cancel">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-outline-danger btn-sm" @disabled($isProtected || ((int) $roleRow['users_count']) > 0)>
                                Delete
                              </button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="3" class="h-muted">No roles available.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="h-card-soft mb-3">
            <div class="head">
              <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Routes Access Matrix</div>
              <div class="h-muted" style="font-size:13px;">Toggle each module per role using Active / Inactive radio options.</div>
            </div>
            <div class="body">
              <form method="POST" action="{{ route('settings.roles.matrix') }}" data-spa>
                @csrf
                <div class="table-responsive h-access-matrix-wrap">
                  <table class="table table-sm align-middle h-access-matrix">
                    <thead>
                      <tr>
                        <th style="min-width:200px;">Module</th>
                        <th style="min-width:260px;">Route / Link Scope</th>
                        @foreach($roleNames as $roleName)
                          <th style="min-width:170px;">{{ strtoupper($roleName) }}</th>
                        @endforeach
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($accessModules as $moduleKey => $module)
                        <tr>
                          <td>
                            <div style="font-weight:700;">{{ $module['label'] }}</div>
                            <div class="h-muted" style="font-size:11px;"><code>{{ $module['view_permission'] }}</code> / <code>{{ $module['manage_permission'] }}</code></div>
                          </td>
                          <td class="h-muted" style="font-size:12px;">{{ $module['description'] }}</td>
                          @foreach($roleNames as $roleName)
                            @php
                              $currentLevel = $roleAccessMap[$roleName][$moduleKey] ?? ($roleName === 'admin' ? 'manage' : 'none');
                              $isActive = in_array($currentLevel, ['view', 'manage'], true);
                              $groupName = "role_modules[{$roleName}][{$moduleKey}]";
                            @endphp
                            <td>
                              <div class="h-radio-inline">
                                <label class="h-radio-pill">
                                  <input type="radio" name="{{ $groupName }}" value="inactive" @checked(!$isActive)>
                                  <span>Inactive</span>
                                </label>
                                <label class="h-radio-pill">
                                  <input type="radio" name="{{ $groupName }}" value="active" @checked($isActive)>
                                  <span>Active</span>
                                </label>
                              </div>
                            </td>
                          @endforeach
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <div class="row g-3 mt-1">
                  <div class="col-12">
                    <label class="h-label" style="display:block;">Extra Action Permissions (Optional)</label>
                    @if($extraPermissionOptions->isEmpty())
                      <div class="h-note" style="margin-top:6px;">No extra permissions available.</div>
                    @else
                      <div class="h-access-extra-grid">
                        @foreach($roleNames as $roleName)
                          @php $selectedExtra = $roleExtraPermissionMap[$roleName] ?? []; @endphp
                          <div>
                            <label class="h-label" style="display:block;margin-bottom:6px;">{{ strtoupper($roleName) }}</label>
                            <select name="extra_permissions[{{ $roleName }}][]" class="form-select form-select-sm" data-h-select multiple>
                              @foreach($extraPermissionOptions as $permissionName)
                                <option value="{{ $permissionName }}" @selected(in_array($permissionName, $selectedExtra, true))>{{ $permissionName }}</option>
                              @endforeach
                            </select>
                          </div>
                        @endforeach
                      </div>
                    @endif
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  <button type="submit" class="btn btn-primary" data-busy-text="Updating...">
                    <i class="fa-solid fa-user-lock me-2"></i>
                    Save Access Matrix
                  </button>
                </div>
              </form>
            </div>
          </div>
        @endif
      </div>
    @endif

    <div class="h-tab-panel" data-tab-panel="settings-activity">
      <div class="h-card-soft mb-3">
        <div class="head h-split">
          <div>
            <div style="font-family:var(--fd);font-size:16px;font-weight:700;">User Activity Log</div>
            <div class="h-muted" style="font-size:13px;">Tracks routes, method, status and user identity by default.</div>
          </div>
          <a href="{{ route('settings.activity.export') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-file-export me-2"></i>
            Export Activity
          </a>
        </div>

        <div class="body">
          <div class="table-responsive">
            <table
              class="table table-sm align-middle"
              data-h-datatable
              data-endpoint="{{ route('ui.datatables.activities') }}"
              data-page-length="20"
              data-length-menu="10,20,50,100"
              data-order-col="0"
              data-order-dir="desc"
            >
              <thead>
                <tr>
                  <th data-col="id">ID</th>
                  <th data-col="created_at">Datetime</th>
                  <th data-col="user">User</th>
                  <th data-col="method">Method</th>
                  <th data-col="path">Path</th>
                  <th data-col="route_name">Route</th>
                  <th data-col="status">Status</th>
                  <th data-col="ip_address">IP</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="h-tab-panel" data-tab-panel="settings-security">
      <div class="h-card-soft mb-3">
        <div class="head">
          <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Personal Security</div>
          <div class="h-muted" style="font-size:13px;">2FA and your notification channel preferences.</div>
        </div>
        <div class="body">
          <form method="POST" action="{{ route('settings.security') }}" data-spa>
            @csrf
            <div class="row g-3">
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Telegram Chat ID</label>
                <input type="text" name="telegram_chat_id" class="form-control" value="{{ old('telegram_chat_id', auth()->user()->telegram_chat_id) }}" placeholder="e.g. 123456789">
              </div>
              <div class="col-md-6" style="padding-top:24px;">
                <div class="h-radio-stack">
                  <label class="form-check"><input class="form-check-input" type="checkbox" name="two_factor_enabled" value="1" @checked(auth()->user()->two_factor_enabled)><span class="form-check-label">Enable 2FA login</span></label>
                  <label class="form-check"><input class="form-check-input" type="checkbox" name="receive_in_app_notifications" value="1" @checked(auth()->user()->receive_in_app_notifications)><span class="form-check-label">In-app notifications</span></label>
                  <label class="form-check"><input class="form-check-input" type="checkbox" name="receive_telegram_notifications" value="1" @checked(auth()->user()->receive_telegram_notifications)><span class="form-check-label">Telegram notifications</span></label>
                  <label class="form-check"><input class="form-check-input" type="checkbox" name="browser_notifications_enabled" value="1" @checked(auth()->user()->browser_notifications_enabled)><span class="form-check-label">Browser notifications</span></label>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-primary" data-busy-text="Saving...">
                <i class="fa-solid fa-shield-halved me-2"></i>
                Save Security Preferences
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="h-tab-panel" data-tab-panel="settings-notifications">
      <div class="h-card-soft mb-3">
        <div class="head">
          <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Broadcast Notifications</div>
          <div class="h-muted" style="font-size:13px;">Send in-app + Telegram alerts and keep browser alerts noticeable with sound + vibration.</div>
        </div>
        <div class="body">
          @can('manage notifications')
            <form method="POST" action="{{ route('notifications.broadcast') }}" data-spa>
              @csrf
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="h-label" style="display:block;">Title</label>
                  <input type="text" name="title" class="form-control" placeholder="System update" required>
                </div>
                <div class="col-md-6">
                  <label class="h-label" style="display:block;">Level</label>
                  <select name="level" class="form-select" data-h-select>
                    <option value="info">Info</option>
                    <option value="success">Success</option>
                    <option value="warning">Warning</option>
                    <option value="error">Error</option>
                  </select>
                </div>
                <div class="col-md-12">
                  <label class="h-label" style="display:block;">Message</label>
                  <textarea name="message" class="form-control" rows="3" placeholder="Write notification message..." required></textarea>
                </div>
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">Audience</label>
                  <select name="audience" class="form-select" data-h-select>
                    <option value="all">All users</option>
                    <option value="role">By role</option>
                    <option value="users">Specific users</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">Role (if audience=role)</label>
                  <select name="role" class="form-select" data-h-select>
                    <option value="">Choose role</option>
                    @foreach(['super-admin','admin','manager','user','test-role'] as $roleName)
                      <option value="{{ $roleName }}">{{ strtoupper($roleName) }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">Users (if audience=users)</label>
                  <select name="user_ids[]" class="form-select" multiple data-select2-remote data-endpoint="{{ route('ui.options.leads') }}" data-placeholder="Search users..." data-min-input="1" data-dropdown-parent="#settings-main-tabs"></select>
                </div>
              </div>

              <div class="h-note mt-3">Browser alerts trigger sound + vibration where supported when new unread notifications arrive.</div>

              <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary" data-busy-text="Sending...">
                  <i class="fa-solid fa-paper-plane me-2"></i>
                  Send Broadcast
                </button>
              </div>
            </form>
          @else
            <div class="h-note">You need <code>manage notifications</code> permission to send broadcast messages.</div>
          @endcan
        </div>
      </div>
    </div>

    @if($canManageSettings)
      <div class="h-tab-panel" data-tab-panel="settings-system">
        @if(!$envWritable)
          <div class="alert alert-danger mb-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <strong>.env is not writable.</strong> Update file permissions first.
          </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}" data-spa>
          @csrf
          @foreach($sections as $sectionKey => $section)
            <div class="h-card-soft mb-3">
              <div class="head">
                <div style="font-family:var(--fd);font-size:16px;font-weight:700;">{{ $section['title'] }}</div>
                <div class="h-muted" style="font-size:13px;">{{ $section['description'] }}</div>
              </div>
              <div class="body">
                <div class="row g-3">
                  @foreach($fields as $key => $field)
                    @continue($field['section'] !== $sectionKey)
                    @php
                      $current = old($key, $values[$key] ?? ($field['default'] ?? ''));
                      $type = $field['type'] ?? 'text';
                    @endphp
                    <div class="col-md-6">
                      <label for="{{ $key }}" class="form-label h-label" style="display:block;">{{ $field['label'] }}</label>
                      @if($type === 'select')
                        <select id="{{ $key }}" name="{{ $key }}" class="form-select" data-h-select {{ ($field['required'] ?? false) ? 'required' : '' }}>
                          @foreach($field['options'] ?? [] as $option)
                            <option value="{{ $option }}" @selected((string) $current === (string) $option)>{{ strtoupper($option) }}</option>
                          @endforeach
                        </select>
                      @elseif($type === 'bool')
                        <select id="{{ $key }}" name="{{ $key }}" class="form-select" data-h-select {{ ($field['required'] ?? false) ? 'required' : '' }}>
                          <option value="true" @selected(in_array(strtolower((string) $current), ['1','true','on','yes'], true))>Enabled</option>
                          <option value="false" @selected(in_array(strtolower((string) $current), ['0','false','off','no'], true))>Disabled</option>
                        </select>
                      @else
                        <input id="{{ $key }}" name="{{ $key }}" type="{{ in_array($type, ['email','password','url','number','decimal'], true) ? ($type === 'decimal' ? 'number' : $type) : 'text' }}" value="{{ $current }}" class="form-control" {{ ($field['required'] ?? false) ? 'required' : '' }} autocomplete="off" @if($type === 'decimal') step="0.01" min="0" max="100" @endif>
                      @endif
                      @error($key)
                        <div class="h-error-msg mt-1">{{ $message }}</div>
                      @enderror
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          @endforeach

          <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
            <button type="submit" class="btn btn-primary" data-busy-text="Saving..." @disabled(!$envWritable)>
              <i class="fa-solid fa-floppy-disk me-2"></i>
              Save Environment Config
            </button>
          </div>
        </form>
      </div>

      <div class="h-tab-panel" data-tab-panel="settings-diagnostics">
        @if(!$opsUiEnabled)
          <div class="h-note mb-3">Diagnostics shell controls are disabled. Enable <code>HAARRAY_ALLOW_SHELL_UI=true</code> if you need maintenance actions.</div>
        @endif

        <div class="h-card-soft mb-3">
          <div class="head"><div style="font-family:var(--fd);font-size:16px;font-weight:700;">Overview</div><div class="h-muted" style="font-size:13px;">Environment health and runtime details.</div></div>
          <div class="body">
            <div class="row g-3">
              <div class="col-md-3"><div class="h-note"><div class="h-ops-metric-label">App Env</div><div class="h-ops-metric-value">{{ $opsSnapshot['app_env'] ?? app()->environment() }}</div></div></div>
              <div class="col-md-3"><div class="h-note"><div class="h-ops-metric-label">Debug</div><div class="h-ops-metric-value">{{ strtoupper((string) ($opsSnapshot['app_debug'] ?? (config('app.debug') ? 'true' : 'false'))) }}</div></div></div>
              <div class="col-md-3"><div class="h-note"><div class="h-ops-metric-label">PHP</div><div class="h-ops-metric-value">{{ $opsSnapshot['php_version'] ?? PHP_VERSION }}</div></div></div>
              <div class="col-md-3"><div class="h-note"><div class="h-ops-metric-label">DB</div><div class="h-ops-metric-value">{{ $dbConnectionInfo['database'] ?: 'N/A' }}</div></div></div>
            </div>
          </div>
        </div>

        <div class="h-card-soft mb-3">
          <div class="head"><div style="font-family:var(--fd);font-size:16px;font-weight:700;">Database Browser</div><div class="h-muted" style="font-size:13px;">Read-only table preview from active connection.</div></div>
          <div class="body">
            @if(!empty($dbBrowser['error']))
              <div class="alert alert-danger">{{ $dbBrowser['error'] }}</div>
            @endif

            <form method="GET" action="{{ route('settings.index') }}" data-spa class="mb-3">
              <input type="hidden" name="tab" value="settings-diagnostics">
              <div class="row g-2 align-items-end">
                <div class="col-md-8">
                  <label class="h-label" style="display:block;">Table</label>
                  <select name="db_table" class="form-select" data-h-select>
                    @foreach($dbBrowser['tables'] ?? [] as $table)
                      <option value="{{ $table }}" @selected(($dbBrowser['selected'] ?? '') === $table)>{{ $table }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4"><button type="submit" class="btn btn-outline-secondary w-100">Load Table</button></div>
              </div>
            </form>

            @if(!empty($dbBrowser['rows']))
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead><tr>@foreach($dbBrowser['columns'] ?? [] as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
                  <tbody>
                    @foreach($dbBrowser['rows'] as $row)
                      <tr>
                        @foreach($dbBrowser['columns'] ?? [] as $column)
                          @php $cell = is_scalar($row[$column] ?? null) ? (string) $row[$column] : json_encode($row[$column] ?? null); @endphp
                          <td class="h-muted" style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $cell }}">{{ $cell }}</td>
                        @endforeach
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <div class="h-note">No preview rows available for selected table.</div>
            @endif
          </div>
        </div>

        <div class="h-card-soft mb-3">
          <div class="head"><div style="font-family:var(--fd);font-size:16px;font-weight:700;">ML Diagnostic Lab</div><div class="h-muted" style="font-size:13px;">Run wrapper probe to see current classification output.</div></div>
          <div class="body">
            <div class="row g-3 mb-3">
              @foreach(($mlDiagnostics['checks'] ?? []) as $check)
                <div class="col-md-4">
                  <div class="h-note h-100">
                    <div class="h-ops-metric-label">{{ $check['title'] }}</div>
                    <div class="h-ops-metric-value {{ !empty($check['status']) ? 'text-success' : 'text-danger' }}">{{ !empty($check['status']) ? 'OK' : 'Issue' }}</div>
                    <div class="h-muted" style="font-size:12px;">{{ $check['note'] }}</div>
                  </div>
                </div>
              @endforeach
            </div>

            <form method="POST" action="{{ route('settings.ml.probe') }}" data-spa>
              @csrf
              <div class="row g-3">
                <div class="col-md-4"><label class="h-label" style="display:block;">Food Ratio</label><input type="number" step="0.01" min="0" max="1" name="food_ratio" class="form-control" value="{{ old('food_ratio', $mlDefaults['food_ratio'] ?? 0.35) }}" required></div>
                <div class="col-md-4"><label class="h-label" style="display:block;">Entertainment Ratio</label><input type="number" step="0.01" min="0" max="1" name="entertainment_ratio" class="form-control" value="{{ old('entertainment_ratio', $mlDefaults['entertainment_ratio'] ?? 0.20) }}" required></div>
                <div class="col-md-4"><label class="h-label" style="display:block;">Savings Rate</label><input type="number" step="0.01" min="0" max="1" name="savings_rate" class="form-control" value="{{ old('savings_rate', $mlDefaults['savings_rate'] ?? 0.30) }}" required></div>
              </div>
              <div class="d-flex justify-content-end mt-3"><button type="submit" class="btn btn-outline-secondary" data-busy-text="Running...">Run ML Probe</button></div>
            </form>

            @if(!empty($mlProbeResult))
              <div class="h-note mt-3"><div class="h-ops-metric-label">Probe Output</div><pre style="margin:8px 0 0;white-space:pre-wrap;color:var(--t1);font-family:var(--fm);font-size:11px;">{{ json_encode($mlProbeResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></div>
            @endif
          </div>
        </div>

        <div class="h-card-soft mb-3">
          <div class="head"><div style="font-family:var(--fd);font-size:16px;font-weight:700;">Recent Logs</div></div>
          <div class="body"><pre style="margin:0;max-height:300px;overflow:auto;white-space:pre-wrap;color:var(--t1);font-family:var(--fm);font-size:11px;">{{ $opsSnapshot['log_tail'] ?? 'No log data available.' }}</pre></div>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection

@section('modals')
  @if($canManageUsers)
    <div class="h-modal-overlay" id="settings-user-modal">
      <div class="h-modal" style="max-width:760px;">
        <div class="h-modal-head">
          <div class="h-modal-title" id="h-user-form-title">Create User</div>
          <button class="h-modal-close">×</button>
        </div>
        <div class="h-modal-body">
          <form method="POST" action="{{ route('settings.users.store') }}" id="h-user-form" data-spa data-store-action="{{ route('settings.users.store') }}" data-update-template="{{ route('settings.users.update', ['user' => '__ID__']) }}">
            @csrf
            <span id="h-user-method-holder"></span>
            <div class="row g-2">
              <div class="col-md-6"><label class="h-label" style="display:block;">Name</label><input type="text" name="name" id="h-user-name" class="form-control" required></div>
              <div class="col-md-6"><label class="h-label" style="display:block;">Email</label><input type="email" name="email" id="h-user-email" class="form-control" required></div>
              <div class="col-md-6"><label class="h-label" style="display:block;">Password</label><input type="password" name="password" id="h-user-password" class="form-control" minlength="8" required></div>
              <div class="col-md-6"><label class="h-label" style="display:block;">Role</label><select name="role" id="h-user-role" class="form-select" data-h-select required>@foreach($roleNames as $roleName)<option value="{{ $roleName }}">{{ strtoupper($roleName) }}</option>@endforeach</select></div>
              <div class="col-md-6"><label class="h-label" style="display:block;">Telegram Chat ID</label><input type="text" name="telegram_chat_id" id="h-user-tg" class="form-control" placeholder="optional"></div>
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Direct Permissions</label>
                <select name="permissions[]" id="h-user-permissions" class="form-select" data-h-select multiple>
                  @foreach($permissionOptions as $permissionName)
                    <option value="{{ $permissionName }}">{{ $permissionName }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="h-radio-stack mt-2">
              <label class="form-check"><input class="form-check-input" type="checkbox" name="receive_in_app_notifications" id="h-user-inapp" value="1" checked><span class="form-check-label">In-app notifications</span></label>
              <label class="form-check"><input class="form-check-input" type="checkbox" name="receive_telegram_notifications" id="h-user-telegram" value="1"><span class="form-check-label">Telegram notifications</span></label>
              <label class="form-check"><input class="form-check-input" type="checkbox" name="browser_notifications_enabled" id="h-user-browser" value="1"><span class="form-check-label">Browser notifications</span></label>
            </div>

            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-primary" id="h-user-submit-btn" data-busy-text="Saving...">
                <i class="fa-solid fa-floppy-disk me-2"></i>
                Save User
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
@endsection

@section('scripts')
<script>
(function () {
  const tabs = document.getElementById('settings-main-tabs');
  if (!tabs) return;

  const users = @json($userPayloadMap);
  const roles = @json($rolePayloadMap);
  const canManageUsers = @json($canManageUsers);
  const canManageSettings = @json($canManageSettings);
  const selectedUserId = Number(@json($selectedUserId));
  const selectedRoleId = Number(@json($selectedRoleId));

  const activateTab = (tabId) => {
    const button = tabs.querySelector('[data-tab-btn="' + tabId + '"]');
    if (button) button.click();
  };

  const updateQuery = (tabId, extra = {}) => {
    const url = new URL(window.location.href);
    if (tabId) url.searchParams.set('tab', tabId);
    ['user', 'role'].forEach((key) => url.searchParams.delete(key));
    Object.keys(extra).forEach((key) => {
      const value = extra[key];
      if (value !== null && value !== undefined && String(value) !== '') {
        url.searchParams.set(key, String(value));
      }
    });
    window.history.replaceState({}, '', url.toString());
  };

  document.addEventListener('h:tabs:changed', function (event) {
    if (!event.detail || event.detail.container !== tabs) return;
    updateQuery(event.detail.tabId);
  });

  document.addEventListener('click', function (event) {
    const trigger = event.target.closest('[data-media-pick]');
    if (!trigger) return;

    const targetId = trigger.getAttribute('data-media-target');
    const mediaUrl = trigger.getAttribute('data-media-url') || '';
    const target = document.getElementById(targetId);
    if (!target) return;

    target.value = mediaUrl;
    target.dispatchEvent(new Event('input', { bubbles: true }));
    if (window.HToast) HToast.success('Selected asset applied. Save settings to persist.');
  });

  if (canManageUsers) {
    const userForm = document.getElementById('h-user-form');
    const userFormTitle = document.getElementById('h-user-form-title');
    const userMethodHolder = document.getElementById('h-user-method-holder');
    const userSubmitBtn = document.getElementById('h-user-submit-btn');

    const userName = document.getElementById('h-user-name');
    const userEmail = document.getElementById('h-user-email');
    const userPassword = document.getElementById('h-user-password');
    const userRole = document.getElementById('h-user-role');
    const userTg = document.getElementById('h-user-tg');
    const userPermissions = document.getElementById('h-user-permissions');
    const userInapp = document.getElementById('h-user-inapp');
    const userTelegram = document.getElementById('h-user-telegram');
    const userBrowser = document.getElementById('h-user-browser');

    const resetUserPermissions = (selected = []) => {
      const selectedSet = new Set((selected || []).map(String));
      Array.from(userPermissions.options).forEach((option) => {
        option.selected = selectedSet.has(String(option.value));
      });
      userPermissions.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const openUserCreate = () => {
      userForm.setAttribute('action', userForm.dataset.storeAction);
      userMethodHolder.innerHTML = '';
      userFormTitle.textContent = 'Create User';
      userSubmitBtn.innerHTML = '<i class="fa-solid fa-user-plus me-2"></i>Create User';

      userName.value = '';
      userEmail.value = '';
      userPassword.value = '';
      userPassword.required = true;
      userPassword.placeholder = '';
      userRole.selectedIndex = 0;
      userRole.dispatchEvent(new Event('change', { bubbles: true }));
      userTg.value = '';
      userInapp.checked = true;
      userTelegram.checked = false;
      userBrowser.checked = false;
      resetUserPermissions([]);

      updateQuery('settings-users');
      if (window.HModal) HModal.open('settings-user-modal');
    };

    const openUserEdit = (userId) => {
      const user = users[String(userId)] || users[userId];
      if (!user) return;

      const action = String(userForm.dataset.updateTemplate || '').replace('__ID__', String(user.id));
      userForm.setAttribute('action', action);
      userMethodHolder.innerHTML = '<input type="hidden" name="_method" value="PUT">';
      userFormTitle.textContent = 'Edit User: ' + user.name;
      userSubmitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Update User';

      userName.value = String(user.name || '');
      userEmail.value = String(user.email || '');
      userPassword.value = '';
      userPassword.required = false;
      userPassword.placeholder = 'Leave blank to keep current password';
      if (user.role) userRole.value = String(user.role);
      userRole.dispatchEvent(new Event('change', { bubbles: true }));
      userTg.value = String(user.telegram_chat_id || '');
      userInapp.checked = Boolean(user.receive_in_app_notifications);
      userTelegram.checked = Boolean(user.receive_telegram_notifications);
      userBrowser.checked = Boolean(user.browser_notifications_enabled);
      resetUserPermissions(Array.isArray(user.permissions) ? user.permissions : []);

      activateTab('settings-users');
      updateQuery('settings-users', { user: user.id });
      if (window.HModal) HModal.open('settings-user-modal');
    };

    const createButton = document.getElementById('h-user-create-open');
    if (createButton) {
      createButton.addEventListener('click', openUserCreate);
    }

    document.addEventListener('click', function (event) {
      const editButton = event.target.closest('[data-user-edit-id]');
      if (!editButton) return;
      const userId = Number(editButton.getAttribute('data-user-edit-id') || 0);
      if (!userId) return;
      event.preventDefault();
      openUserEdit(userId);
    });

    if (selectedUserId > 0) {
      setTimeout(() => openUserEdit(selectedUserId), 140);
    }
  }

  if (canManageSettings) {
    const roleForm = document.getElementById('h-role-form');
    const roleFormTitle = document.getElementById('h-role-form-title');
    const roleMethodHolder = document.getElementById('h-role-method-holder');
    const roleSubmitBtn = document.getElementById('h-role-submit-btn');
    const roleName = document.getElementById('h-role-name');
    const rolePermissions = document.getElementById('h-role-permissions');

    if (roleForm && roleName && rolePermissions) {
      const setRolePermissions = (selected = []) => {
        const selectedSet = new Set((selected || []).map(String));
        Array.from(rolePermissions.options).forEach((option) => {
          option.selected = selectedSet.has(String(option.value));
        });
        rolePermissions.dispatchEvent(new Event('change', { bubbles: true }));
      };

      const setRoleCreate = () => {
        roleForm.setAttribute('action', roleForm.dataset.storeAction);
        roleMethodHolder.innerHTML = '';
        roleFormTitle.textContent = 'Create Role';
        roleSubmitBtn.innerHTML = '<i class="fa-solid fa-plus me-2"></i>Create Role';
        roleName.value = '';
        setRolePermissions([]);
        updateQuery('settings-roles');
      };

      const setRoleEdit = (roleId) => {
        const role = roles[String(roleId)] || roles[roleId];
        if (!role) return;

        const action = String(roleForm.dataset.updateTemplate || '').replace('__ID__', String(role.id));
        roleForm.setAttribute('action', action);
        roleMethodHolder.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        roleFormTitle.textContent = 'Edit Role: ' + String(role.name || '').toUpperCase();
        roleSubmitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Update Role';
        roleName.value = String(role.name || '');
        setRolePermissions(Array.isArray(role.permissions) ? role.permissions : []);

        activateTab('settings-roles');
        updateQuery('settings-roles', { role: role.id });

        const editor = document.getElementById('role-editor');
        if (editor) editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
      };

      const resetBtn = document.getElementById('h-role-create-reset');
      if (resetBtn) resetBtn.addEventListener('click', setRoleCreate);

      document.addEventListener('click', function (event) {
        const editButton = event.target.closest('[data-role-edit-id]');
        if (!editButton) return;
        const roleId = Number(editButton.getAttribute('data-role-edit-id') || 0);
        if (!roleId) return;
        event.preventDefault();
        setRoleEdit(roleId);
      });

      if (selectedRoleId > 0) {
        setTimeout(() => setRoleEdit(selectedRoleId), 160);
      }
    }
  }
})();
</script>
@endsection
