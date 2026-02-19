@extends('layouts.haarray')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('topbar_extra')
  <span class="h-live-badge">
    <i class="fa-solid fa-gear"></i>
    Control Center
  </span>
@endsection

@section('content')
@php
  $defaultSettingsTab = (string) request()->query('tab', 'settings-profile');
  $defaultOpsTab = (string) request()->query('ops_tab', 'ops-overview');
@endphp
<div class="hl-docs hl-settings">
  <div class="doc-head">
    <div>
      <div class="doc-title">Settings Control Center</div>
      <div class="doc-sub">
        Manage security, notification channels, Telegram setup, ML thresholds, environment configuration,
        and role-based access from one place.
      </div>
    </div>
    @if($isAdmin)
      <span class="h-pill gold">Admin Mode</span>
    @else
      <span class="h-pill teal">User Mode</span>
    @endif
  </div>

  <div class="h-tab-shell" id="settings-main-tabs" data-ui-tabs data-default-tab="{{ $defaultSettingsTab }}">
    <div class="h-tab-nav" role="tablist" aria-label="Settings sections">
      <button type="button" class="h-tab-btn" data-tab-btn="settings-profile">
        <i class="fa-solid fa-user-shield"></i>
        Profile & Security
      </button>

      @if($isAdmin)
        <button type="button" class="h-tab-btn" data-tab-btn="settings-system">
          <i class="fa-solid fa-sliders"></i>
          System Config
        </button>
        <button type="button" class="h-tab-btn" data-tab-btn="settings-access">
          <i class="fa-solid fa-user-lock"></i>
          Roles & Access
        </button>
        @if($canManageSettings)
          <button type="button" class="h-tab-btn" data-tab-btn="settings-ops">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            Diagnostics
          </button>
        @endif
        <button type="button" class="h-tab-btn" data-tab-btn="settings-broadcast">
          <i class="fa-solid fa-bullhorn"></i>
          Broadcasts
        </button>
      @endif
    </div>

    <div class="h-tab-panel" data-tab-panel="settings-profile">
      <div class="h-card-soft mb-3">
        <div class="head">
          <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Personal Security</div>
          <div class="h-muted" style="font-size:13px;">Your own 2FA and delivery preferences.</div>
        </div>

        <div class="body">
          <form method="POST" action="{{ route('settings.security') }}" data-spa>
            @csrf

            <div class="row g-3">
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Telegram Chat ID</label>
                <input
                  type="text"
                  name="telegram_chat_id"
                  class="form-control"
                  value="{{ old('telegram_chat_id', auth()->user()->telegram_chat_id) }}"
                  placeholder="e.g. 123456789"
                >
              </div>

              <div class="col-md-6 d-flex align-items-center gap-3" style="padding-top:24px;flex-wrap:wrap;">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="two_factor_enabled" value="1" id="two_factor_enabled" @checked(auth()->user()->two_factor_enabled)>
                  <label class="form-check-label" for="two_factor_enabled">Enable 2FA login</label>
                </div>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="receive_in_app_notifications" value="1" id="receive_in_app_notifications" @checked(auth()->user()->receive_in_app_notifications)>
                  <label class="form-check-label" for="receive_in_app_notifications">In-app notifications</label>
                </div>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="receive_telegram_notifications" value="1" id="receive_telegram_notifications" @checked(auth()->user()->receive_telegram_notifications)>
                  <label class="form-check-label" for="receive_telegram_notifications">Telegram notifications</label>
                </div>

                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="browser_notifications_enabled" value="1" id="browser_notifications_enabled" @checked(auth()->user()->browser_notifications_enabled)>
                  <label class="form-check-label" for="browser_notifications_enabled">Browser push notifications</label>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-primary" data-busy-text="Saving...">
                <i class="fa-solid fa-shield-halved me-2"></i>
                Save Personal Preferences
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    @if($isAdmin)
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
                <div class="h-split">
                  <div>
                    <div style="font-family:var(--fd);font-size:16px;font-weight:700;">{{ $section['title'] }}</div>
                    <div class="h-muted" style="font-size:13px;">{{ $section['description'] }}</div>
                  </div>
                </div>
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
                        <select id="{{ $key }}" name="{{ $key }}" class="form-select" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                          @foreach($field['options'] ?? [] as $option)
                            <option value="{{ $option }}" @selected((string) $current === (string) $option)>{{ strtoupper($option) }}</option>
                          @endforeach
                        </select>
                      @elseif($type === 'bool')
                        <select id="{{ $key }}" name="{{ $key }}" class="form-select" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                          <option value="true" @selected(in_array(strtolower((string) $current), ['1','true','on','yes'], true))>Enabled</option>
                          <option value="false" @selected(in_array(strtolower((string) $current), ['0','false','off','no'], true))>Disabled</option>
                        </select>
                      @else
                        <input
                          id="{{ $key }}"
                          name="{{ $key }}"
                          type="{{ in_array($type, ['email','password','url','number','decimal'], true) ? ($type === 'decimal' ? 'number' : $type) : 'text' }}"
                          value="{{ $current }}"
                          class="form-control"
                          {{ ($field['required'] ?? false) ? 'required' : '' }}
                          autocomplete="off"
                          @if($type === 'decimal') step="0.01" min="0" max="100" @endif
                        >
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

      <div class="h-tab-panel" data-tab-panel="settings-access">
        @if(!$hasSpatiePermissions)
          <div class="alert alert-warning mb-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            Spatie role/permission tables are not ready yet. Run migrations first to enable full access matrix controls.
          </div>
        @endif

        @if($hasSpatiePermissions)
          <div class="h-card-soft mb-3">
            <div class="head">
              <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Route, Link & Action Access Matrix</div>
              <div class="h-muted" style="font-size:13px;">Assign each role per module using dropdown levels: none, view, manage.</div>
            </div>

            <div class="body">
              <form method="POST" action="{{ route('settings.roles.matrix') }}" data-spa>
                @csrf

                <div class="table-responsive h-access-matrix-wrap">
                  <table class="table table-sm align-middle h-access-matrix">
                    <thead>
                      <tr>
                        <th style="min-width:180px;">Module</th>
                        <th style="min-width:270px;">Route / Link / Action Scope</th>
                        @foreach($roleNames as $roleName)
                          <th style="min-width:150px;">{{ strtoupper($roleName) }}</th>
                        @endforeach
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($accessModules as $moduleKey => $module)
                        <tr>
                          <td>
                            <div style="font-weight:700;">{{ $module['label'] }}</div>
                            <div class="h-muted" style="font-size:11px;">
                              <code>{{ $module['view_permission'] }}</code> / <code>{{ $module['manage_permission'] }}</code>
                            </div>
                          </td>
                          <td class="h-muted" style="font-size:12px;">{{ $module['description'] }}</td>
                          @foreach($roleNames as $roleName)
                            @php
                              $currentLevel = $roleAccessMap[$roleName][$moduleKey] ?? ($roleName === 'admin' ? 'manage' : 'none');
                            @endphp
                            <td>
                              <select name="role_modules[{{ $roleName }}][{{ $moduleKey }}]" class="form-select form-select-sm">
                                <option value="none" @selected($currentLevel === 'none')>No Access</option>
                                <option value="view" @selected($currentLevel === 'view')>View</option>
                                <option value="manage" @selected($currentLevel === 'manage')>Manage</option>
                              </select>
                            </td>
                          @endforeach
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                @php
                  $extraPermissionOptions = collect($permissionOptions)
                    ->reject(fn ($permission) => in_array($permission, $modulePermissionNames, true))
                    ->values();
                @endphp

                <div class="row g-3 mt-1">
                  <div class="col-12">
                    <label class="h-label" style="display:block;">Extra Action Permissions (Optional)</label>
                    @if($extraPermissionOptions->isEmpty())
                      <div class="h-note" style="margin-top:6px;">
                        No extra actions are defined yet. Add custom permissions in code/migrations and they will appear here automatically.
                      </div>
                    @else
                      <div class="h-access-extra-grid">
                        @foreach($roleNames as $roleName)
                          @php
                            $selectedExtra = $roleExtraPermissionMap[$roleName] ?? [];
                          @endphp
                          <div>
                            <label class="h-label" style="display:block;margin-bottom:6px;">{{ strtoupper($roleName) }}</label>
                            <select
                              name="extra_permissions[{{ $roleName }}][]"
                              class="form-select form-select-sm"
                              multiple
                              data-permission-select
                              data-placeholder="Select extra actions..."
                            >
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

        @if($hasSpatiePermissions && $canManageSettings)
          <div class="h-card-soft mb-3">
            <div class="head">
              <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Roles Directory (Modal CRUD)</div>
              <div class="h-muted" style="font-size:13px;">Create, edit permissions, and delete roles using modal forms with checkbox-based permission sets.</div>
            </div>

            <div class="body">
              <div class="d-flex justify-content-end mb-2">
                <button type="button" class="btn btn-primary btn-sm" data-modal-open="role-create-modal">
                  <i class="fa-solid fa-plus me-2"></i>
                  Create Role
                </button>
              </div>

              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead>
                    <tr>
                      <th>Role</th>
                      <th>Permissions</th>
                      <th>Users</th>
                      <th style="min-width:180px;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($roleCatalog as $roleRow)
                      @php
                        $roleName = (string) $roleRow['name'];
                        $isProtected = in_array($roleName, $protectedRoleNames, true);
                      @endphp
                      <tr>
                        <td>
                          <div style="font-weight:700;">{{ strtoupper($roleName) }}</div>
                          @if($isProtected)
                            <div class="h-muted" style="font-size:11px;">Protected system role</div>
                          @endif
                        </td>
                        <td>
                          <span class="h-pill teal">{{ $roleRow['permissions_count'] }} permissions</span>
                        </td>
                        <td>{{ $roleRow['users_count'] }}</td>
                        <td>
                          <div class="d-flex gap-2 flex-wrap">
                            <button
                              type="button"
                              class="btn btn-outline-secondary btn-sm"
                              data-role-edit-open
                              data-role='@json($roleRow)'
                            >
                              <i class="fa-solid fa-pen-to-square me-1"></i>
                              Edit
                            </button>

                            <form method="POST" action="{{ route('settings.roles.delete', $roleRow['id']) }}" data-spa data-confirm="true" data-confirm-title="Delete role {{ $roleName }}?" data-confirm-text="This cannot be undone." data-confirm-ok="Delete" data-confirm-cancel="Cancel">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-outline-danger btn-sm" @disabled($isProtected || ((int) $roleRow['users_count']) > 0)>
                                <i class="fa-solid fa-trash me-1"></i>
                                Delete
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" class="h-muted">No roles available.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif

        @if($canManageUsers)
          <div class="h-card-soft mb-3">
            <div class="head">
              <div style="font-family:var(--fd);font-size:16px;font-weight:700;">User Directory (DataTable)</div>
              <div class="h-muted" style="font-size:13px;">Server-side listing for quick filtering without breaking SPA navigation.</div>
            </div>

            <div class="body">
              <div class="table-responsive">
                <table
                  class="table table-sm align-middle"
                  data-h-datatable
                  data-endpoint="{{ route('ui.datatables.users') }}"
                  data-page-length="10"
                >
                  <thead>
                    <tr>
                      <th data-col="id">ID</th>
                      <th data-col="name">Name</th>
                      <th data-col="email">Email</th>
                      <th data-col="role">Role</th>
                      <th data-col="channels">Channels</th>
                      <th data-col="created_at">Joined</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="h-card-soft mb-3">
            <div class="head">
              <div style="font-family:var(--fd);font-size:16px;font-weight:700;">User Accounts (Modal CRUD)</div>
              <div class="h-muted" style="font-size:13px;">Create, update, and delete users with role + permission assignment from modal forms.</div>
            </div>

            <div class="body">
              <div class="d-flex justify-content-end mb-2">
                <button type="button" class="btn btn-primary btn-sm" data-modal-open="user-create-modal">
                  <i class="fa-solid fa-user-plus me-2"></i>
                  Create User
                </button>
              </div>

              <div class="table-responsive">
                <table class="table align-middle h-access-user-table">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Role</th>
                      <th>Direct Permissions</th>
                      <th>Channels</th>
                      <th>Telegram Chat ID</th>
                      <th style="min-width:200px;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($users as $managedUser)
                      @php
                        $userDirectPermissions = $hasSpatiePermissions
                          ? $managedUser->permissions->pluck('name')->values()->all()
                          : [];
                        $currentRole = $hasSpatiePermissions
                          ? ($managedUser->roles->first()->name ?? $managedUser->role ?? 'user')
                          : ($managedUser->role ?? 'user');
                        $canDeleteUser = auth()->id() !== $managedUser->id;
                      @endphp
                      <tr>
                        <td>
                          <div style="font-weight:600;">{{ $managedUser->name }}</div>
                          <div class="h-muted" style="font-size:11px;">{{ $managedUser->email }}</div>
                        </td>
                        <td>
                          <span class="h-pill teal">{{ strtoupper($currentRole) }}</span>
                        </td>
                        <td style="min-width:260px;">
                          <span class="h-muted" style="font-size:12px;">
                            {{ empty($userDirectPermissions) ? 'None' : implode(', ', $userDirectPermissions) }}
                          </span>
                        </td>
                        <td>
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="in_app_{{ $managedUser->id }}" @checked($managedUser->receive_in_app_notifications) disabled>
                            <label class="form-check-label" for="in_app_{{ $managedUser->id }}">In-app</label>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tg_{{ $managedUser->id }}" @checked($managedUser->receive_telegram_notifications) disabled>
                            <label class="form-check-label" for="tg_{{ $managedUser->id }}">Telegram</label>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="browser_{{ $managedUser->id }}" @checked($managedUser->browser_notifications_enabled) disabled>
                            <label class="form-check-label" for="browser_{{ $managedUser->id }}">Browser</label>
                          </div>
                        </td>
                        <td>
                          <span class="h-muted" style="font-size:12px;">{{ $managedUser->telegram_chat_id ?: '-' }}</span>
                        </td>
                        <td>
                          <div class="d-flex gap-2 flex-wrap">
                            <button
                              type="button"
                              class="btn btn-sm btn-outline-secondary"
                              data-user-edit-open
                              data-user='@json([
                                'id' => $managedUser->id,
                                'name' => $managedUser->name,
                                'email' => $managedUser->email,
                                'role' => $currentRole,
                                'telegram_chat_id' => $managedUser->telegram_chat_id,
                                'receive_in_app_notifications' => (bool) $managedUser->receive_in_app_notifications,
                                'receive_telegram_notifications' => (bool) $managedUser->receive_telegram_notifications,
                                'browser_notifications_enabled' => (bool) $managedUser->browser_notifications_enabled,
                                'permissions' => $userDirectPermissions,
                              ])'
                            >
                              <i class="fa-solid fa-pen-to-square me-1"></i>
                              Edit
                            </button>

                            <form method="POST" action="{{ route('settings.users.delete', $managedUser) }}" data-spa data-confirm="true" data-confirm-title="Delete {{ $managedUser->name }}?" data-confirm-text="This user account will be removed permanently." data-confirm-ok="Delete" data-confirm-cancel="Cancel">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger" @disabled(!$canDeleteUser)>
                                <i class="fa-solid fa-trash me-1"></i>
                                Delete
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @else
          <div class="h-note">
            You can manage role matrix rules, but this account does not have `manage users` permission for per-user updates.
          </div>
        @endif
      </div>

      @if($canManageSettings)
        <div class="h-tab-panel" data-tab-panel="settings-ops">
          <div class="h-card-soft mb-3">
            <div class="head">
              <div style="font-family:var(--fd);font-size:16px;font-weight:700;">App Diagnostics & Maintenance</div>
              <div class="h-muted" style="font-size:13px;">Split into focused tabs so diagnostics stay fast, readable, and actionable.</div>
            </div>

            <div class="body">
              @if(!$opsUiEnabled)
                <div class="h-note">
                  DevOps UI is currently disabled for safety. Enable `HAARRAY_ALLOW_SHELL_UI=true` in System Config and save to unlock Git/maintenance actions.
                </div>
              @else
                @php
                  $gitStatusRaw = (string) ($opsSnapshot['git_status'] ?? '');
                  $gitLines = collect(preg_split('/\r\n|\r|\n/', $gitStatusRaw))
                    ->map(fn ($line) => trim((string) $line))
                    ->filter(fn ($line) => $line !== '');
                  $dirtyCount = $gitLines->reject(fn ($line) => str_starts_with($line, '##'))->count();
                  $mlChecks = collect($mlDiagnostics['checks'] ?? []);
                @endphp

                <div class="h-tab-shell h-ops-tabs" id="settings-ops-tabs" data-ui-tabs data-default-tab="{{ $defaultOpsTab }}">
                  <div class="h-tab-nav" role="tablist" aria-label="Diagnostics panels">
                    <button type="button" class="h-tab-btn" data-tab-btn="ops-overview">
                      <i class="fa-solid fa-gauge-high"></i>
                      Overview
                    </button>
                    <button type="button" class="h-tab-btn" data-tab-btn="ops-ml">
                      <i class="fa-solid fa-flask-vial"></i>
                      ML Lab
                    </button>
                    <button type="button" class="h-tab-btn" data-tab-btn="ops-logs">
                      <i class="fa-solid fa-database"></i>
                      Data & Logs
                    </button>
                  </div>

                  <div class="h-tab-panel" data-tab-panel="ops-overview">
                    <div class="row g-3 mb-3">
                      <div class="col-md-4">
                        <div class="h-note h-ops-metric">
                          <div class="h-ops-metric-label">Git Branch</div>
                          <div class="h-ops-metric-value">{{ $opsSnapshot['git_branch'] ?? 'N/A' }}</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="h-note h-ops-metric">
                          <div class="h-ops-metric-label">Dirty Files</div>
                          <div class="h-ops-metric-value">{{ $dirtyCount }}</div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="h-note h-ops-metric">
                          <div class="h-ops-metric-label">PHP-ML</div>
                          <div class="h-ops-metric-value">{{ ($mlDiagnostics['phpml_loaded'] ?? false) ? 'Loaded' : 'Missing' }}</div>
                        </div>
                      </div>
                    </div>

                    <form method="POST" action="{{ route('settings.ops.action') }}" class="d-flex gap-2 flex-wrap mb-3" data-spa>
                      @csrf
                      <button class="btn btn-outline-secondary btn-sm" type="submit" name="action" value="git_status">Git Status</button>
                      <button class="btn btn-outline-secondary btn-sm" type="submit" name="action" value="git_pull">Git Pull</button>
                      <button class="btn btn-outline-secondary btn-sm" type="submit" name="action" value="git_push">Git Push</button>
                      <button class="btn btn-outline-secondary btn-sm" type="submit" name="action" value="composer_dump_autoload">Composer Dump</button>
                      <button class="btn btn-outline-secondary btn-sm" type="submit" name="action" value="optimize_clear">Optimize Clear</button>
                      <button class="btn btn-outline-secondary btn-sm" type="submit" name="action" value="migrate_status">Migrate Status</button>
                      <button class="btn btn-outline-secondary btn-sm" type="submit" name="action" value="fix_permissions">Fix Storage Permissions</button>
                    </form>

                    <div class="h-note">
                      <div class="h-ops-metric-label">Git Status Snapshot</div>
                      <pre style="margin:6px 0 0;white-space:pre-wrap;color:var(--t1);font-family:var(--fm);font-size:11px;">{{ $opsSnapshot['git_status'] ?? 'No status data.' }}</pre>
                    </div>

                    @if($opsOutput !== '')
                      <div class="h-card-soft mt-3">
                        <div class="head">
                          <div style="font-family:var(--fd);font-size:14px;font-weight:700;">Last Command Output</div>
                        </div>
                        <div class="body">
                          <pre style="margin:0;white-space:pre-wrap;color:var(--t1);font-family:var(--fm);font-size:11px;">{{ $opsOutput }}</pre>
                        </div>
                      </div>
                    @endif
                  </div>

                  <div class="h-tab-panel" data-tab-panel="ops-ml">
                    <div class="row g-3 mb-3">
                      @forelse($mlChecks as $check)
                        <div class="col-md-4">
                          <div class="h-note h-ops-metric">
                            <div class="h-ops-metric-label">{{ $check['title'] }}</div>
                            <div class="h-ops-metric-value {{ !empty($check['status']) ? 'text-success' : 'text-danger' }}">
                              {{ !empty($check['status']) ? 'Healthy' : 'Attention' }}
                            </div>
                            <div class="h-muted" style="font-size:11px;margin-top:4px;">{{ $check['note'] ?? '' }}</div>
                          </div>
                        </div>
                      @empty
                        <div class="col-12">
                          <div class="h-note">No ML diagnostics available.</div>
                        </div>
                      @endforelse
                    </div>

                    <div class="h-card-soft mb-3">
                      <div class="head">
                        <div style="font-family:var(--fd);font-size:14px;font-weight:700;">Current ML Thresholds</div>
                      </div>
                      <div class="body">
                        <div class="table-responsive">
                          <table class="table table-sm align-middle">
                            <tbody>
                              <tr>
                                <th style="min-width:220px;">Idle Cash Threshold</th>
                                <td>रू {{ number_format((float) ($mlDiagnostics['thresholds']['idle_cash_threshold'] ?? 0), 2) }}</td>
                              </tr>
                              <tr>
                                <th>Food Budget Warning</th>
                                <td>{{ number_format(((float) ($mlDiagnostics['thresholds']['food_budget_warning'] ?? 0)) * 100, 1) }}%</td>
                              </tr>
                              <tr>
                                <th>Savings Rate Target</th>
                                <td>{{ number_format(((float) ($mlDiagnostics['thresholds']['savings_rate_target'] ?? 0)) * 100, 1) }}%</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <div class="h-card-soft mb-3">
                      <div class="head">
                        <div style="font-family:var(--fd);font-size:14px;font-weight:700;">Scenario Predictions</div>
                      </div>
                      <div class="body">
                        <div class="table-responsive">
                          <table class="table table-sm align-middle">
                            <thead>
                              <tr>
                                <th>Scenario</th>
                                <th>Ratios (Food / Entertainment / Savings)</th>
                                <th>Predicted Label</th>
                                <th>Risk Score</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach($mlDiagnostics['scenario_outputs'] ?? [] as $scenario)
                                @php
                                  $prediction = $scenario['output'] ?? [];
                                  $label = (string) ($prediction['label'] ?? 'unknown');
                                  $riskScore = (float) ($prediction['risk_score'] ?? 0);
                                @endphp
                                <tr>
                                  <td>{{ $scenario['label'] ?? 'Scenario' }}</td>
                                  <td class="h-muted" style="font-size:12px;">
                                    {{ number_format(((float) ($scenario['input']['food_ratio'] ?? 0)) * 100, 0) }}% /
                                    {{ number_format(((float) ($scenario['input']['entertainment_ratio'] ?? 0)) * 100, 0) }}% /
                                    {{ number_format(((float) ($scenario['input']['savings_rate'] ?? 0)) * 100, 0) }}%
                                  </td>
                                  <td>
                                    <span class="h-pill {{ $label === 'high-risk' ? 'gold' : ($label === 'low-risk' ? 'teal' : '') }}">{{ $label }}</span>
                                  </td>
                                  <td>{{ number_format($riskScore * 100, 1) }}%</td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <div class="h-card-soft mb-3">
                      <div class="head">
                        <div style="font-family:var(--fd);font-size:14px;font-weight:700;">Cluster Preview (Expense Buckets)</div>
                      </div>
                      <div class="body">
                        <div class="h-muted" style="font-size:12px;margin-bottom:10px;">
                          Seed set: {{ implode(', ', $mlDiagnostics['cluster_seed'] ?? []) }}
                        </div>
                        <div class="table-responsive">
                          <table class="table table-sm align-middle">
                            <thead>
                              <tr>
                                <th>Cluster</th>
                                <th>Size</th>
                                <th>Average</th>
                                <th>Range</th>
                              </tr>
                            </thead>
                            <tbody>
                              @forelse($mlDiagnostics['cluster_summary'] ?? [] as $index => $cluster)
                                <tr>
                                  <td>#{{ $index + 1 }}</td>
                                  <td>{{ $cluster['size'] ?? 0 }}</td>
                                  <td>रू {{ number_format((float) ($cluster['avg'] ?? 0), 2) }}</td>
                                  <td>रू {{ number_format((float) ($cluster['min'] ?? 0), 2) }} - रू {{ number_format((float) ($cluster['max'] ?? 0), 2) }}</td>
                                </tr>
                              @empty
                                <tr>
                                  <td colspan="4" class="h-muted">Not enough data to generate clusters.</td>
                                </tr>
                              @endforelse
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <div class="h-card-soft">
                      <div class="head">
                        <div style="font-family:var(--fd);font-size:14px;font-weight:700;">Manual ML Probe</div>
                        <div class="h-muted" style="font-size:12px;">Run on-demand classification for your ratio assumptions.</div>
                      </div>
                      <div class="body">
                        <form method="POST" action="{{ route('settings.ml.probe') }}" data-spa>
                          @csrf
                          <div class="row g-3">
                            <div class="col-md-4">
                              <label class="h-label" style="display:block;">Food Ratio (0-1)</label>
                              <input type="number" name="food_ratio" step="0.01" min="0" max="1" class="form-control" value="{{ old('food_ratio', $mlProbeResult['input']['food_ratio'] ?? ($mlDiagnostics['probe_defaults']['food_ratio'] ?? 0.30)) }}" required>
                            </div>
                            <div class="col-md-4">
                              <label class="h-label" style="display:block;">Entertainment Ratio (0-1)</label>
                              <input type="number" name="entertainment_ratio" step="0.01" min="0" max="1" class="form-control" value="{{ old('entertainment_ratio', $mlProbeResult['input']['entertainment_ratio'] ?? ($mlDiagnostics['probe_defaults']['entertainment_ratio'] ?? 0.15)) }}" required>
                            </div>
                            <div class="col-md-4">
                              <label class="h-label" style="display:block;">Savings Rate (0-1)</label>
                              <input type="number" name="savings_rate" step="0.01" min="0" max="1" class="form-control" value="{{ old('savings_rate', $mlProbeResult['input']['savings_rate'] ?? ($mlDiagnostics['probe_defaults']['savings_rate'] ?? 0.30)) }}" required>
                            </div>
                          </div>
                          <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary btn-sm" data-busy-text="Running...">
                              <i class="fa-solid fa-play me-2"></i>
                              Run Probe
                            </button>
                          </div>
                        </form>

                        @if(!empty($mlProbeResult['output']))
                          @php
                            $probeOutput = $mlProbeResult['output'];
                            $probeLabel = (string) ($probeOutput['label'] ?? 'unknown');
                            $probeRisk = (float) ($probeOutput['risk_score'] ?? 0);
                          @endphp
                          <div class="h-note mt-3">
                            <div class="h-ops-metric-label">Probe Output</div>
                            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:6px;">
                              <span class="h-pill {{ $probeLabel === 'high-risk' ? 'gold' : ($probeLabel === 'low-risk' ? 'teal' : '') }}">{{ $probeLabel }}</span>
                              <span class="h-muted">Risk score: {{ number_format($probeRisk * 100, 1) }}%</span>
                            </div>
                          </div>
                        @endif
                      </div>
                    </div>
                  </div>

                  <div class="h-tab-panel" data-tab-panel="ops-logs">
                    <div class="h-card-soft mb-3">
                      <div class="head">
                        <div style="font-family:var(--fd);font-size:14px;font-weight:700;">DB Browser (phpMyAdmin-style preview)</div>
                        <div class="h-muted" style="font-size:12px;">Read-only browser for table rows and schema preview without leaving settings.</div>
                      </div>
                      <div class="body">
                        <form method="GET" action="{{ route('settings.index') }}" class="row g-2 align-items-end mb-3" data-spa>
                          <input type="hidden" name="tab" value="settings-ops">
                          <input type="hidden" name="ops_tab" value="ops-logs">
                          <div class="col-md-8">
                            <label class="h-label" style="display:block;">Table</label>
                            <select name="db_table" class="form-select">
                              @foreach($dbBrowser['tables'] ?? [] as $tableName)
                                <option value="{{ $tableName }}" @selected(($dbBrowser['selected'] ?? '') === $tableName)>{{ $tableName }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                              <i class="fa-solid fa-table me-2"></i>
                              Browse
                            </button>
                          </div>
                        </form>

                        @if(!empty($dbBrowser['error']))
                          <div class="alert alert-danger">{{ $dbBrowser['error'] }}</div>
                        @endif

                        @if(!empty($dbBrowser['selected']))
                          <div class="h-note mb-2">
                            <strong>{{ $dbBrowser['selected'] }}</strong> · Rows: {{ $dbBrowser['row_count'] ?? 0 }} · Showing up to 50 rows.
                          </div>
                          <div class="table-responsive">
                            <table class="table table-sm align-middle">
                              <thead>
                                <tr>
                                  @foreach($dbBrowser['columns'] ?? [] as $column)
                                    <th>{{ $column }}</th>
                                  @endforeach
                                </tr>
                              </thead>
                              <tbody>
                                @forelse($dbBrowser['rows'] ?? [] as $row)
                                  <tr>
                                    @foreach($dbBrowser['columns'] ?? [] as $column)
                                      @php
                                        $cellValue = $row[$column] ?? null;
                                        if (is_array($cellValue)) {
                                          $cellText = json_encode($cellValue);
                                        } elseif (is_object($cellValue)) {
                                          $cellText = json_encode($cellValue, JSON_UNESCAPED_UNICODE);
                                        } elseif (is_bool($cellValue)) {
                                          $cellText = $cellValue ? 'true' : 'false';
                                        } else {
                                          $cellText = (string) $cellValue;
                                        }
                                      @endphp
                                      <td class="h-muted" style="max-width:220px;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $cellText }}">{{ $cellText }}</td>
                                    @endforeach
                                  </tr>
                                @empty
                                  <tr>
                                    <td colspan="{{ max(1, count($dbBrowser['columns'] ?? [])) }}" class="h-muted">No rows found for selected table.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>
                        @endif
                      </div>
                    </div>

                    <div class="h-card-soft mb-3">
                      <div class="head">
                        <div style="font-family:var(--fd);font-size:14px;font-weight:700;">User Activity (Default Tracking)</div>
                        <div class="h-muted" style="font-size:12px;">Latest authenticated actions captured by middleware.</div>
                      </div>
                      <div class="body">
                        <div class="table-responsive">
                          <table class="table table-sm align-middle">
                            <thead>
                              <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Method</th>
                                <th>Path</th>
                                <th>Route</th>
                                <th>Status</th>
                              </tr>
                            </thead>
                            <tbody>
                              @forelse($recentActivities as $activity)
                                <tr>
                                  <td class="h-muted" style="font-size:11px;">{{ optional($activity->created_at)->format('Y-m-d H:i:s') }}</td>
                                  <td>
                                    <div style="font-weight:600;">{{ $activity->user->name ?? 'Unknown' }}</div>
                                    <div class="h-muted" style="font-size:11px;">{{ $activity->user->email ?? '-' }}</div>
                                  </td>
                                  <td><span class="h-pill teal">{{ strtoupper((string) $activity->method) }}</span></td>
                                  <td class="h-muted" style="font-size:11px;">{{ $activity->path }}</td>
                                  <td class="h-muted" style="font-size:11px;">{{ $activity->route_name ?: '-' }}</td>
                                  <td>{{ (int) data_get($activity->meta, 'status', 0) ?: '-' }}</td>
                                </tr>
                              @empty
                                <tr>
                                  <td colspan="6" class="h-muted">No user activity entries yet.</td>
                                </tr>
                              @endforelse
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <div class="h-card-soft mb-3">
                      <div class="head">
                        <div style="font-family:var(--fd);font-size:14px;font-weight:700;">Database Structure Snapshot</div>
                      </div>
                      <div class="body">
                        <div class="table-responsive">
                          <table class="table table-sm align-middle">
                            <thead>
                              <tr>
                                <th>Table</th>
                                <th>Rows</th>
                                <th>Columns (preview)</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach($opsSnapshot['db_tables'] ?? [] as $table)
                                <tr>
                                  <td><code>{{ $table['name'] }}</code></td>
                                  <td>{{ $table['row_count'] ?? 'n/a' }}</td>
                                  <td class="h-muted" style="font-size:11px;">{{ $table['columns'] }}</td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <div class="h-card-soft mb-3">
                      <div class="head">
                        <div style="font-family:var(--fd);font-size:14px;font-weight:700;">Application Error Log (Tail)</div>
                      </div>
                      <div class="body">
                        <pre style="margin:0;max-height:300px;overflow:auto;white-space:pre-wrap;color:var(--t1);font-family:var(--fm);font-size:11px;">{{ $opsSnapshot['log_tail'] ?? 'No log data available.' }}</pre>
                      </div>
                    </div>

                    <div class="h-card-soft">
                      <div class="head">
                        <div style="font-family:var(--fd);font-size:14px;font-weight:700;">Browser Runtime Errors</div>
                      </div>
                      <div class="body">
                        <div class="d-flex justify-content-end mb-2">
                          <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-client-errors">Clear Browser Errors</button>
                        </div>
                        <pre id="h-client-errors" style="margin:0;max-height:220px;overflow:auto;white-space:pre-wrap;color:var(--t1);font-family:var(--fm);font-size:11px;">No browser errors captured yet.</pre>
                      </div>
                    </div>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      @endif

      <div class="h-tab-panel" data-tab-panel="settings-broadcast">
        <div class="h-card-soft mb-3">
          <div class="head">
            <div style="font-family:var(--fd);font-size:16px;font-weight:700;">Broadcast Notifications</div>
            <div class="h-muted" style="font-size:13px;">Send in-app + Telegram notifications to selected audience.</div>
          </div>

          <div class="body">
            <form method="POST" action="{{ route('notifications.broadcast') }}" data-spa>
              @csrf
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="h-label" style="display:block;">Title</label>
                  <input type="text" name="title" class="form-control" placeholder="System maintenance" required>
                </div>
                <div class="col-md-6">
                  <label class="h-label" style="display:block;">Level</label>
                  <select name="level" class="form-select" required>
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
                  <select name="audience" class="form-select" id="audience-select" required>
                    <option value="all">All users</option>
                    <option value="admins">Admins only</option>
                    <option value="role">By role</option>
                    <option value="users">Specific users</option>
                  </select>
                </div>
                <div class="col-md-4" id="role-filter-wrap">
                  <label class="h-label" style="display:block;">Role Filter (if audience=role)</label>
                  <select name="role" class="form-select">
                    @foreach($roleNames as $roleName)
                      <option value="{{ $roleName }}">{{ strtoupper($roleName) }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="h-label" style="display:block;">URL (optional)</label>
                  <input type="url" name="url" class="form-control" placeholder="https://...">
                </div>
                <div class="col-md-12" id="user-filter-wrap">
                  <label class="h-label" style="display:block;">Specific Users (if audience=users)</label>
                  <select
                    name="user_ids[]"
                    class="form-select"
                    multiple
                    data-select2-remote
                    data-endpoint="{{ route('ui.options.leads') }}"
                    data-placeholder="Search users..."
                    data-min-input="1"
                  ></select>
                </div>
                <div class="col-md-12 d-flex gap-3 flex-wrap">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="channels[]" value="in_app" id="ch_in_app" checked>
                    <label class="form-check-label" for="ch_in_app">In-app</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="channels[]" value="telegram" id="ch_telegram">
                    <label class="form-check-label" for="ch_telegram">Telegram</label>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary" data-busy-text="Broadcasting...">
                  <i class="fa-solid fa-paper-plane me-2"></i>
                  Send Broadcast
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection

@section('modals')
  @if($canManageSettings && $hasSpatiePermissions)
    <div class="h-modal-overlay" id="role-create-modal">
      <div class="h-modal">
        <div class="h-modal-head">
          <div class="h-modal-title">Create Role</div>
          <button class="h-modal-close">×</button>
        </div>
        <div class="h-modal-body">
          <form method="POST" action="{{ route('settings.roles.store') }}" data-spa>
            @csrf
            <div class="h-form-group">
              <label class="h-label" style="display:block;">Role Name</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. super-admin" required>
            </div>
            <div class="h-form-group">
              <label class="h-label" style="display:block;">Permissions</label>
              <div class="h-perm-grid">
                @foreach($permissionOptions as $permissionName)
                  <label class="h-perm-item">
                    <input type="checkbox" name="permissions[]" value="{{ $permissionName }}">
                    <span>{{ $permissionName }}</span>
                  </label>
                @endforeach
              </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-primary btn-sm" data-busy-text="Creating...">
                <i class="fa-solid fa-check me-2"></i>
                Create Role
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="h-modal-overlay" id="role-edit-modal">
      <div class="h-modal">
        <div class="h-modal-head">
          <div class="h-modal-title">Edit Role</div>
          <button class="h-modal-close">×</button>
        </div>
        <div class="h-modal-body">
          <form method="POST" action="{{ route('settings.roles.update', ['role' => 1]) }}" id="role-edit-form" data-action-template="{{ route('settings.roles.update', ['role' => '__ID__']) }}" data-spa>
            @csrf
            @method('PUT')
            <div class="h-form-group">
              <label class="h-label" style="display:block;">Role Name</label>
              <input type="text" name="name" id="role-edit-name" class="form-control" required>
            </div>
            <div class="h-form-group">
              <label class="h-label" style="display:block;">Permissions</label>
              <div class="h-perm-grid" id="role-edit-permissions">
                @foreach($permissionOptions as $permissionName)
                  <label class="h-perm-item">
                    <input type="checkbox" name="permissions[]" value="{{ $permissionName }}">
                    <span>{{ $permissionName }}</span>
                  </label>
                @endforeach
              </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-primary btn-sm" data-busy-text="Saving...">
                <i class="fa-solid fa-floppy-disk me-2"></i>
                Save Role
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  @if($canManageUsers)
    <div class="h-modal-overlay" id="user-create-modal">
      <div class="h-modal">
        <div class="h-modal-head">
          <div class="h-modal-title">Create User</div>
          <button class="h-modal-close">×</button>
        </div>
        <div class="h-modal-body">
          <form method="POST" action="{{ route('settings.users.store') }}" data-spa>
            @csrf
            <div class="row g-3">
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Full Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Password</label>
                <input type="password" name="password" class="form-control" minlength="8" required>
              </div>
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Role</label>
                <select name="role" class="form-select" required>
                  @foreach($roleNames as $roleName)
                    <option value="{{ $roleName }}">{{ strtoupper($roleName) }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Telegram Chat ID</label>
                <input type="text" name="telegram_chat_id" class="form-control" placeholder="optional">
              </div>
              <div class="col-md-6 d-flex gap-3 align-items-end flex-wrap">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="receive_in_app_notifications" value="1" id="new_user_in_app" checked>
                  <label class="form-check-label" for="new_user_in_app">In-app</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="receive_telegram_notifications" value="1" id="new_user_tg">
                  <label class="form-check-label" for="new_user_tg">Telegram</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="browser_notifications_enabled" value="1" id="new_user_browser">
                  <label class="form-check-label" for="new_user_browser">Browser</label>
                </div>
              </div>
              <div class="col-12">
                <label class="h-label" style="display:block;">Direct Permissions</label>
                <select name="permissions[]" class="form-select" multiple data-permission-select data-placeholder="Choose permissions...">
                  @foreach($permissionOptions as $permissionName)
                    <option value="{{ $permissionName }}">{{ $permissionName }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-primary btn-sm" data-busy-text="Creating...">
                <i class="fa-solid fa-user-plus me-2"></i>
                Create User
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="h-modal-overlay" id="user-edit-modal">
      <div class="h-modal">
        <div class="h-modal-head">
          <div class="h-modal-title">Edit User</div>
          <button class="h-modal-close">×</button>
        </div>
        <div class="h-modal-body">
          <form method="POST" action="{{ route('settings.users.update', ['user' => 1]) }}" id="user-edit-form" data-action-template="{{ route('settings.users.update', ['user' => '__ID__']) }}" data-spa>
            @csrf
            @method('PUT')
            <div class="row g-3">
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Full Name</label>
                <input type="text" name="name" id="user-edit-name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Email</label>
                <input type="email" name="email" id="user-edit-email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Password (leave blank to keep)</label>
                <input type="password" name="password" id="user-edit-password" class="form-control" minlength="8">
              </div>
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Role</label>
                <select name="role" id="user-edit-role" class="form-select" required>
                  @foreach($roleNames as $roleName)
                    <option value="{{ $roleName }}">{{ strtoupper($roleName) }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="h-label" style="display:block;">Telegram Chat ID</label>
                <input type="text" name="telegram_chat_id" id="user-edit-chat" class="form-control">
              </div>
              <div class="col-md-6 d-flex gap-3 align-items-end flex-wrap">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="receive_in_app_notifications" value="1" id="user-edit-inapp">
                  <label class="form-check-label" for="user-edit-inapp">In-app</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="receive_telegram_notifications" value="1" id="user-edit-tg">
                  <label class="form-check-label" for="user-edit-tg">Telegram</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="browser_notifications_enabled" value="1" id="user-edit-browser">
                  <label class="form-check-label" for="user-edit-browser">Browser</label>
                </div>
              </div>
              <div class="col-12">
                <label class="h-label" style="display:block;">Direct Permissions</label>
                <select name="permissions[]" id="user-edit-permissions" class="form-select" multiple data-permission-select data-placeholder="Choose permissions...">
                  @foreach($permissionOptions as $permissionName)
                    <option value="{{ $permissionName }}">{{ $permissionName }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-primary btn-sm" data-busy-text="Saving...">
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
    const stateKey = '__hSettingsBindings';
    const prev = window[stateKey] || {};
    if (prev.onTabsChanged) {
      document.removeEventListener('h:tabs:changed', prev.onTabsChanged);
    }
    if (prev.onAudienceChange && prev.audienceEl) {
      prev.audienceEl.removeEventListener('change', prev.onAudienceChange);
    }

    const audience = document.getElementById('audience-select');
    const roleWrap = document.getElementById('role-filter-wrap');
    const userWrap = document.getElementById('user-filter-wrap');

    function syncAudienceFields() {
      if (!audience || !roleWrap || !userWrap) return;

      const roleSelect = roleWrap.querySelector('select[name="role"]');
      const userSelect = userWrap.querySelector('select[name="user_ids[]"]');
      const mode = audience.value;
      const isRole = mode === 'role';
      const isUsers = mode === 'users';

      roleWrap.style.display = isRole ? '' : 'none';
      userWrap.style.display = isUsers ? '' : 'none';

      if (roleSelect) roleSelect.required = isRole;
      if (userSelect) userSelect.required = isUsers;
    }

    function ensureRemoteSelect(panel) {
      if (!panel || !window.HSelectRemote || !window.jQuery) return;

      panel.querySelectorAll('select[data-select2-remote]').forEach((el) => {
        window.HSelectRemote.setup(window.jQuery(el));
      });
    }

    function ensurePermissionSelects(panel) {
      if (!panel || !window.jQuery || typeof window.jQuery.fn.select2 !== 'function') return;

      window.jQuery(panel).find('select[data-permission-select]').each(function () {
        const $select = window.jQuery(this);
        if ($select.hasClass('select2-hidden-accessible')) return;

        const placeholder = $select.data('placeholder') || 'Select permissions';
        const $parent = $select.closest('.h-modal, .h-tab-panel, .h-card-soft').first();
        $select.select2({
          width: '100%',
          closeOnSelect: false,
          placeholder,
          dropdownCssClass: 'h-s2-dropdown',
          dropdownParent: $parent.length ? $parent : window.jQuery(document.body),
        });
      });
    }

    function ensureDataTables(panel) {
      if (!panel) return;

      if (window.HDataTable) {
        window.HDataTable.init(panel);
        return;
      }

      if (!window.jQuery || typeof window.jQuery.fn.DataTable !== 'function') return;

      window.jQuery(panel).find('table[data-h-datatable]').each(function () {
        const $table = window.jQuery(this);
        if (window.jQuery.fn.DataTable.isDataTable(this)) return;

        const endpoint = $table.data('endpoint');
        if (!endpoint) return;

        const columns = [];
        $table.find('thead th[data-col]').each(function () {
          columns.push({ data: window.jQuery(this).data('col'), name: window.jQuery(this).data('col') });
        });

        $table.DataTable({
          processing: true,
          serverSide: true,
          searching: true,
          ordering: true,
          pageLength: Number($table.data('pageLength') || 10),
          ajax: endpoint,
          columns,
        });
      });
    }

    function renderClientErrors(panel) {
      if (!panel || !window.HDebug) return;

      const host = panel.querySelector('#h-client-errors');
      const clearBtn = panel.querySelector('#clear-client-errors');
      if (!host) return;

      const rows = window.HDebug.read ? window.HDebug.read() : [];
      if (!Array.isArray(rows) || rows.length === 0) {
        host.textContent = 'No browser errors captured yet.';
      } else {
        host.textContent = rows
          .map((row) => '[' + (row.time || '-') + '] ' + (row.type || 'error') + ' :: ' + (row.message || 'Unknown error')
            + (row.source ? ' @ ' + row.source + (row.line ? ':' + row.line : '') : ''))
          .join('\n');
      }

      if (clearBtn) {
        clearBtn.onclick = function () {
          try {
            localStorage.removeItem('h_client_errors');
          } catch (error) {
            // Ignore storage failures.
          }
          host.textContent = 'No browser errors captured yet.';
        };
      }
    }

    function ensureOpsPanel(panel) {
      if (!panel) return;

      if (window.HTabs) {
        window.HTabs.init(panel);
      }

      const activeOpsTab = panel.querySelector('.h-ops-tabs')?.dataset?.activeTab || '';
      if (activeOpsTab === 'ops-logs') {
        renderClientErrors(panel);
      }
    }

    function activateTabById(container, tabId) {
      if (!container || !tabId) return;
      const button = container.querySelector('[data-tab-btn="' + tabId + '"]');
      if (button) {
        button.click();
      }
    }

    function activateTabsFromQuery() {
      const params = new URLSearchParams(window.location.search);
      const topTab = params.get('tab') || '';
      const opsTab = params.get('ops_tab') || '';

      const mainTabs = document.getElementById('settings-main-tabs');
      const opsTabs = document.getElementById('settings-ops-tabs');

      if (topTab) {
        activateTabById(mainTabs, topTab);
      }

      if (topTab === 'settings-ops' && opsTab) {
        setTimeout(() => activateTabById(opsTabs, opsTab), 60);
      }
    }

    function bindRoleModal() {
      const editForm = document.getElementById('role-edit-form');
      if (!editForm) return;

      const template = editForm.getAttribute('data-action-template') || '';
      const roleNameInput = document.getElementById('role-edit-name');
      const permissionBoxes = Array.from(editForm.querySelectorAll('input[name="permissions[]"]'));

      document.querySelectorAll('[data-role-edit-open]').forEach((button) => {
        button.addEventListener('click', () => {
          let role;
          try {
            role = JSON.parse(button.getAttribute('data-role') || '{}');
          } catch (error) {
            role = {};
          }

          const roleId = String(role.id || '').trim();
          const roleName = String(role.name || '').trim();
          const rolePermissions = Array.isArray(role.permissions) ? role.permissions.map((item) => String(item)) : [];

          if (template && roleId) {
            editForm.setAttribute('action', template.replace('__ID__', roleId));
          }

          if (roleNameInput) {
            roleNameInput.value = roleName;
          }

          permissionBoxes.forEach((checkbox) => {
            checkbox.checked = rolePermissions.includes(String(checkbox.value));
          });

          if (window.HModal) {
            window.HModal.open('role-edit-modal');
          }
        });
      });
    }

    function bindUserModal() {
      const editForm = document.getElementById('user-edit-form');
      if (!editForm) return;

      const actionTemplate = editForm.getAttribute('data-action-template') || '';
      const nameField = document.getElementById('user-edit-name');
      const emailField = document.getElementById('user-edit-email');
      const passwordField = document.getElementById('user-edit-password');
      const roleField = document.getElementById('user-edit-role');
      const chatField = document.getElementById('user-edit-chat');
      const inAppField = document.getElementById('user-edit-inapp');
      const tgField = document.getElementById('user-edit-tg');
      const browserField = document.getElementById('user-edit-browser');
      const permsSelect = document.getElementById('user-edit-permissions');

      document.querySelectorAll('[data-user-edit-open]').forEach((button) => {
        button.addEventListener('click', () => {
          let user;
          try {
            user = JSON.parse(button.getAttribute('data-user') || '{}');
          } catch (error) {
            user = {};
          }

          const userId = String(user.id || '').trim();
          if (actionTemplate && userId) {
            editForm.setAttribute('action', actionTemplate.replace('__ID__', userId));
          }

          if (nameField) nameField.value = String(user.name || '');
          if (emailField) emailField.value = String(user.email || '');
          if (passwordField) passwordField.value = '';
          if (roleField) roleField.value = String(user.role || '');
          if (chatField) chatField.value = String(user.telegram_chat_id || '');
          if (inAppField) inAppField.checked = Boolean(user.receive_in_app_notifications);
          if (tgField) tgField.checked = Boolean(user.receive_telegram_notifications);
          if (browserField) browserField.checked = Boolean(user.browser_notifications_enabled);

          if (permsSelect) {
            const selectedPermissions = Array.isArray(user.permissions)
              ? user.permissions.map((item) => String(item))
              : [];

            Array.from(permsSelect.options).forEach((option) => {
              option.selected = selectedPermissions.includes(String(option.value));
            });

            if (window.jQuery && window.jQuery(permsSelect).hasClass('select2-hidden-accessible')) {
              window.jQuery(permsSelect).trigger('change');
            }
          }

          if (window.HModal) {
            window.HModal.open('user-edit-modal');
          }
        });
      });
    }

    if (audience) {
      audience.addEventListener('change', syncAudienceFields);
      syncAudienceFields();
    }

    ensurePermissionSelects(document);
    bindRoleModal();
    bindUserModal();
    activateTabsFromQuery();

    const onTabsChanged = function (event) {
      const detail = event.detail || {};
      if (detail.tabId === 'settings-broadcast') {
        syncAudienceFields();
        ensureRemoteSelect(detail.panel || null);
        return;
      }

      if (detail.tabId === 'settings-access') {
        ensurePermissionSelects(detail.panel || null);
        ensureDataTables(detail.panel || null);
        return;
      }

      if (detail.tabId === 'settings-ops') {
        ensureOpsPanel(detail.panel || null);
        return;
      }

      if (detail.tabId === 'ops-logs') {
        renderClientErrors(detail.panel || null);
      }
    };

    document.addEventListener('h:tabs:changed', onTabsChanged);
    window[stateKey] = {
      onTabsChanged,
      onAudienceChange: syncAudienceFields,
      audienceEl: audience || null,
    };
  })();
</script>
@endsection
