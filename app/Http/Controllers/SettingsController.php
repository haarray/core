<?php

namespace App\Http\Controllers;

use App\Http\Services\AdvancedMLService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Process\Process;
use Throwable;

class SettingsController extends Controller
{
    public function index(): View
    {
        $viewer = request()->user();

        $fields = $this->fields();
        $values = $this->readEnvValues(array_keys($fields));
        $permissionTablesReady = $this->permissionTablesReady();
        $opsUiEnabled = $this->opsUiEnabled($values);

        foreach ($fields as $key => $meta) {
            if (($values[$key] ?? '') === '' && array_key_exists('default', $meta)) {
                $values[$key] = (string) $meta['default'];
            }
        }

        $canManageSettings = (bool) ($viewer && $viewer->can('manage settings'));
        $canManageUsers = (bool) ($viewer && $viewer->can('manage users'));

        $roles = collect();
        $roleNames = $this->availableRoleNames();
        $permissionOptions = collect($this->availablePermissionNames());
        $roleAccessMap = [];
        $roleExtraPermissionMap = [];
        $accessModules = $this->accessModules();
        $modulePermissionNames = $this->modulePermissionNames($accessModules);

        if ($permissionTablesReady) {
            $roles = Role::query()->with('permissions')->orderBy('name')->get();
            $roleNames = $roles->pluck('name')->values()->all();
            $roleAccessMap = $this->buildRoleAccessMap($roles, $accessModules);
            $roleExtraPermissionMap = $this->buildRoleExtraPermissionMap($roles, $modulePermissionNames);
        }

        $users = collect();
        if ($canManageUsers) {
            $userQuery = User::query()->orderBy('name');
            if ($permissionTablesReady) {
                $userQuery->with(['roles:id,name', 'permissions:id,name']);
            }
            $users = $userQuery->get();
        }

        $opsSnapshot = [];
        if ($canManageSettings && $opsUiEnabled) {
            $opsSnapshot = $this->buildOpsSnapshot();
        }

        return view('settings.index', [
            'fields'      => $fields,
            'values'      => $values,
            'sections'    => $this->sections(),
            'envWritable' => $this->envWritable(),
            'users'       => $users,
            'isAdmin'     => $canManageSettings || $canManageUsers || (bool) ($viewer && $viewer->isAdmin()),
            'roles' => $roles,
            'roleNames' => $roleNames,
            'permissionOptions' => $permissionOptions,
            'accessModules' => $accessModules,
            'modulePermissionNames' => $modulePermissionNames,
            'roleAccessMap' => $roleAccessMap,
            'roleExtraPermissionMap' => $roleExtraPermissionMap,
            'canManageSettings' => $canManageSettings,
            'canManageUsers' => $canManageUsers,
            'hasSpatiePermissions' => $permissionTablesReady,
            'opsUiEnabled' => $opsUiEnabled,
            'opsSnapshot' => $opsSnapshot,
            'opsOutput' => (string) session('ops_output', ''),
            'mlDiagnostics' => $this->buildMlDiagnostics(),
            'mlProbeResult' => session('ml_probe_result', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->assertCan($request, 'manage settings', 'Only authorized admins can change system settings.');

        if (!$this->envWritable()) {
            return back()->with('error', 'The .env file is not writable. Update file permissions and try again.');
        }

        $fields = $this->fields();
        $validated = $request->validate($this->rules($fields));

        $updates = [];
        foreach ($fields as $key => $meta) {
            $rawValue = (string) ($validated[$key] ?? '');
            $updates[$key] = $this->formatEnvValue($rawValue, $meta['type'] ?? 'text');
        }

        try {
            $this->writeEnvValues($updates);
            Artisan::call('config:clear');
        } catch (Throwable $e) {
            return back()->with('error', 'Settings could not be saved: ' . $e->getMessage());
        }

        return back()->with('success', 'Environment settings were updated successfully.');
    }

    public function updateUserAccess(Request $request, User $user): RedirectResponse
    {
        $this->assertCan($request, 'manage users', 'Only users with access-management rights can update user permissions.');

        $roles = $this->availableRoleNames();
        $permissions = $this->availablePermissionNames();
        $permissionItemRules = ['string'];
        if (!empty($permissions)) {
            $permissionItemRules[] = Rule::in($permissions);
        } else {
            $permissionItemRules[] = 'max:120';
        }

        $rawPermissions = $request->input('permissions', []);
        if (is_string($rawPermissions)) {
            $rawPermissions = explode(',', $rawPermissions);
        }
        if (!is_array($rawPermissions)) {
            $rawPermissions = [];
        }

        $request->merge([
            'permissions' => array_values(array_filter(array_map(
                fn ($permission) => trim((string) $permission),
                $rawPermissions
            ))),
        ]);

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in($roles)],
            'receive_in_app_notifications' => ['nullable', 'boolean'],
            'receive_telegram_notifications' => ['nullable', 'boolean'],
            'browser_notifications_enabled' => ['nullable', 'boolean'],
            'telegram_chat_id' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => $permissionItemRules,
        ]);

        $directPermissions = array_values(array_filter(array_map(
            fn ($permission) => trim((string) $permission),
            $validated['permissions'] ?? []
        )));

        $roleName = $validated['role'];

        $user->update([
            'role' => $roleName,
            'receive_in_app_notifications' => (bool) $request->boolean('receive_in_app_notifications', false),
            'receive_telegram_notifications' => (bool) $request->boolean('receive_telegram_notifications', false),
            'browser_notifications_enabled' => (bool) $request->boolean('browser_notifications_enabled', false),
            'telegram_chat_id' => $validated['telegram_chat_id'] ?? null,
        ]);

        if ($this->permissionTablesReady()) {
            $user->syncRoles([$roleName]);
            $user->syncPermissions($directPermissions);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return back()->with('success', "Access settings updated for {$user->name}.");
    }

    public function updateRoleMatrix(Request $request): RedirectResponse
    {
        $this->assertCan($request, 'manage settings', 'Only authorized admins can change role access matrix.');

        if (!$this->permissionTablesReady()) {
            return back()->with('error', 'Spatie permission tables are not ready. Run migrations first.');
        }

        $roleModules = $request->input('role_modules', []);
        $extraPermissions = $request->input('extra_permissions', []);
        if (!is_array($roleModules)) {
            return back()->with('error', 'Invalid role matrix payload.');
        }
        if (!is_array($extraPermissions)) {
            $extraPermissions = [];
        }

        $modules = $this->accessModules();
        $modulePermissionNames = $this->modulePermissionNames($modules);
        $allPermissions = Permission::query()->orderBy('name')->pluck('name')->all();
        $allowedPermissionSet = array_fill_keys($allPermissions, true);
        $roles = Role::query()->orderBy('name')->get();

        foreach ($roles as $role) {
            $moduleLevels = $roleModules[$role->name] ?? [];
            if (!is_array($moduleLevels)) {
                $moduleLevels = [];
            }
            $roleExtraPermissions = $extraPermissions[$role->name] ?? [];
            if (!is_array($roleExtraPermissions)) {
                $roleExtraPermissions = [];
            }

            $granted = [];

            foreach ($modules as $moduleKey => $meta) {
                $level = strtolower((string) ($moduleLevels[$moduleKey] ?? 'none'));
                if (!in_array($level, ['none', 'view', 'manage'], true)) {
                    $level = 'none';
                }

                if ($level === 'view' || $level === 'manage') {
                    $granted[] = $meta['view_permission'];
                }

                if ($level === 'manage') {
                    $granted[] = $meta['manage_permission'];
                }
            }

            foreach ($roleExtraPermissions as $permissionName) {
                $permission = trim((string) $permissionName);
                if ($permission === '') {
                    continue;
                }
                if (!isset($allowedPermissionSet[$permission])) {
                    continue;
                }
                if (in_array($permission, $modulePermissionNames, true)) {
                    continue;
                }
                $granted[] = $permission;
            }

            if ($role->name === 'admin') {
                $granted = $allPermissions;
            }

            $role->syncPermissions(array_values(array_unique($granted)));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Role access matrix updated successfully.');
    }

    public function updateMySecurity(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $validated = $request->validate([
            'two_factor_enabled' => ['nullable', 'boolean'],
            'telegram_chat_id' => ['nullable', 'string', 'max:255'],
            'receive_in_app_notifications' => ['nullable', 'boolean'],
            'receive_telegram_notifications' => ['nullable', 'boolean'],
            'browser_notifications_enabled' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'two_factor_enabled' => (bool) $request->boolean('two_factor_enabled', false),
            'telegram_chat_id' => $validated['telegram_chat_id'] ?? null,
            'receive_in_app_notifications' => (bool) $request->boolean('receive_in_app_notifications', false),
            'receive_telegram_notifications' => (bool) $request->boolean('receive_telegram_notifications', false),
            'browser_notifications_enabled' => (bool) $request->boolean('browser_notifications_enabled', false),
        ]);

        return back()->with('success', 'Your security and notification preferences were updated.');
    }

    public function runOpsAction(Request $request): RedirectResponse
    {
        $this->assertCan($request, 'manage settings', 'Only authorized admins can run maintenance actions.');

        if (!$this->opsUiEnabled()) {
            return back()->with('error', 'DevOps UI is disabled. Enable HAARRAY_ALLOW_SHELL_UI first.');
        }

        $validated = $request->validate([
            'action' => ['required', Rule::in([
                'git_status',
                'git_pull',
                'git_push',
                'composer_dump_autoload',
                'optimize_clear',
                'migrate_status',
            ])],
        ]);

        $action = (string) $validated['action'];

        $result = match ($action) {
            'git_status' => $this->runShell('git status --short --branch'),
            'git_pull' => $this->runShell('git pull --ff-only'),
            'git_push' => $this->runShell('git push'),
            'composer_dump_autoload' => $this->runShell('composer dump-autoload -o', 90),
            'optimize_clear' => $this->runShell('php artisan optimize:clear', 90),
            'migrate_status' => $this->runShell('php artisan migrate:status --no-ansi', 90),
            default => ['ok' => false, 'exit_code' => 1, 'output' => 'Invalid action.'],
        };

        $message = $result['ok']
            ? 'Maintenance action completed: ' . str_replace('_', ' ', $action) . '.'
            : 'Maintenance action failed: ' . str_replace('_', ' ', $action) . '.';

        return back()
            ->with($result['ok'] ? 'success' : 'error', $message)
            ->with('ops_output', (string) $result['output']);
    }

    public function runMlProbe(Request $request): RedirectResponse
    {
        $this->assertCan($request, 'manage settings', 'Only authorized admins can run ML diagnostics.');

        $validated = $request->validate([
            'food_ratio' => ['required', 'numeric', 'between:0,1'],
            'entertainment_ratio' => ['required', 'numeric', 'between:0,1'],
            'savings_rate' => ['required', 'numeric', 'between:0,1'],
        ]);

        /** @var AdvancedMLService $ml */
        $ml = app(AdvancedMLService::class);

        $result = $ml->classifySpendingProfile(
            (float) $validated['food_ratio'],
            (float) $validated['entertainment_ratio'],
            (float) $validated['savings_rate'],
        );

        return back()
            ->with('success', 'ML diagnostic probe completed.')
            ->with('ml_probe_result', [
                'input' => [
                    'food_ratio' => (float) $validated['food_ratio'],
                    'entertainment_ratio' => (float) $validated['entertainment_ratio'],
                    'savings_rate' => (float) $validated['savings_rate'],
                ],
                'output' => $result,
            ]);
    }

    /**
     * @return array<string, array{title: string, description: string}>
     */
    private function sections(): array
    {
        return [
            'app' => [
                'title'       => 'Application',
                'description' => 'Core app identity and runtime behavior.',
            ],
            'haarray' => [
                'title'       => 'Haarray Features',
                'description' => 'Branding and feature flags from config/haarray.php.',
            ],
            'telegram' => [
                'title'       => 'Telegram Bot API',
                'description' => 'Bot token, username, and webhook endpoint configuration.',
            ],
            'ml' => [
                'title'       => 'ML Suggestion Engine',
                'description' => 'Thresholds used by the suggestion service.',
            ],
            'realtime' => [
                'title'       => 'Realtime & Broadcast',
                'description' => 'Polling behavior now, plus Pusher keys for optional realtime transports.',
            ],
            'database' => [
                'title'       => 'Database',
                'description' => 'Connection details used by Laravel database config.',
            ],
            'mail' => [
                'title'       => 'Mail',
                'description' => 'Mailer and sender details.',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function fields(): array
    {
        return [
            'APP_NAME' => [
                'section'  => 'app',
                'label'    => 'App Name',
                'type'     => 'text',
                'required' => true,
                'default'  => 'Haarray Core',
            ],
            'APP_URL' => [
                'section'  => 'app',
                'label'    => 'App URL',
                'type'     => 'url',
                'required' => true,
                'default'  => 'http://localhost',
            ],
            'APP_ENV' => [
                'section'  => 'app',
                'label'    => 'App Environment',
                'type'     => 'select',
                'required' => true,
                'options'  => ['local', 'staging', 'production'],
                'default'  => 'local',
            ],
            'APP_DEBUG' => [
                'section'  => 'app',
                'label'    => 'Debug Mode',
                'type'     => 'bool',
                'required' => true,
                'default'  => 'true',
            ],
            'HAARRAY_ALLOW_SHELL_UI' => [
                'section'  => 'app',
                'label'    => 'Enable DevOps UI (Git / DB / Logs)',
                'type'     => 'bool',
                'required' => true,
                'default'  => 'false',
            ],
            'APP_TIMEZONE' => [
                'section'  => 'app',
                'label'    => 'Timezone',
                'type'     => 'text',
                'required' => true,
                'default'  => 'UTC',
            ],
            'HAARRAY_BRAND' => [
                'section'  => 'haarray',
                'label'    => 'Brand Name',
                'type'     => 'text',
                'required' => true,
                'default'  => 'Haarray',
            ],
            'HAARRAY_INITIAL' => [
                'section'  => 'haarray',
                'label'    => 'Brand Initial',
                'type'     => 'text',
                'required' => true,
                'default'  => 'H',
            ],
            'HAARRAY_SHOW_TG' => [
                'section'  => 'haarray',
                'label'    => 'Show Telegram Status',
                'type'     => 'bool',
                'required' => true,
                'default'  => 'false',
            ],
            'HAARRAY_PWA' => [
                'section'  => 'haarray',
                'label'    => 'Enable PWA',
                'type'     => 'bool',
                'required' => true,
                'default'  => 'true',
            ],
            'HAARRAY_ML' => [
                'section'  => 'haarray',
                'label'    => 'Enable ML Suggestions',
                'type'     => 'bool',
                'required' => true,
                'default'  => 'true',
            ],
            'TELEGRAM_BOT_TOKEN' => [
                'section'  => 'telegram',
                'label'    => 'Bot Token',
                'type'     => 'password',
                'required' => false,
                'default'  => '',
            ],
            'TELEGRAM_BOT_USERNAME' => [
                'section'  => 'telegram',
                'label'    => 'Bot Username',
                'type'     => 'text',
                'required' => false,
                'default'  => 'HariLogBot',
            ],
            'TELEGRAM_BOT_WEBHOOK_URL' => [
                'section'  => 'telegram',
                'label'    => 'Webhook URL',
                'type'     => 'url',
                'required' => false,
                'default'  => '',
            ],
            'HAARRAY_ML_IDLE_CASH_THRESHOLD' => [
                'section'  => 'ml',
                'label'    => 'Idle Cash Threshold (NPR)',
                'type'     => 'number',
                'required' => true,
                'default'  => '5000',
            ],
            'HAARRAY_ML_FOOD_BUDGET_WARNING' => [
                'section'  => 'ml',
                'label'    => 'Food Budget Warning Ratio',
                'type'     => 'decimal',
                'required' => true,
                'default'  => '0.35',
            ],
            'HAARRAY_ML_SAVINGS_RATE_TARGET' => [
                'section'  => 'ml',
                'label'    => 'Savings Rate Target',
                'type'     => 'decimal',
                'required' => true,
                'default'  => '0.30',
            ],
            'HAARRAY_ML_RETRAIN_DAYS' => [
                'section'  => 'ml',
                'label'    => 'Model Retrain Interval (days)',
                'type'     => 'number',
                'required' => true,
                'default'  => '7',
            ],
            'HAARRAY_NOTIFY_POLL_SECONDS' => [
                'section'  => 'realtime',
                'label'    => 'Notification Poll Interval (seconds)',
                'type'     => 'number',
                'required' => true,
                'default'  => '20',
            ],
            'BROADCAST_CONNECTION' => [
                'section'  => 'realtime',
                'label'    => 'Broadcast Driver',
                'type'     => 'select',
                'required' => true,
                'options'  => ['log', 'pusher', 'reverb', 'null'],
                'default'  => 'log',
            ],
            'PUSHER_APP_ID' => [
                'section'  => 'realtime',
                'label'    => 'Pusher App ID',
                'type'     => 'text',
                'required' => false,
                'default'  => '',
            ],
            'PUSHER_APP_KEY' => [
                'section'  => 'realtime',
                'label'    => 'Pusher App Key',
                'type'     => 'text',
                'required' => false,
                'default'  => '',
            ],
            'PUSHER_APP_SECRET' => [
                'section'  => 'realtime',
                'label'    => 'Pusher App Secret',
                'type'     => 'password',
                'required' => false,
                'default'  => '',
            ],
            'PUSHER_HOST' => [
                'section'  => 'realtime',
                'label'    => 'Pusher Host',
                'type'     => 'text',
                'required' => false,
                'default'  => '',
            ],
            'PUSHER_PORT' => [
                'section'  => 'realtime',
                'label'    => 'Pusher Port',
                'type'     => 'port',
                'required' => true,
                'default'  => '443',
            ],
            'PUSHER_SCHEME' => [
                'section'  => 'realtime',
                'label'    => 'Pusher Scheme',
                'type'     => 'select',
                'required' => true,
                'options'  => ['http', 'https'],
                'default'  => 'https',
            ],
            'PUSHER_APP_CLUSTER' => [
                'section'  => 'realtime',
                'label'    => 'Pusher Cluster',
                'type'     => 'text',
                'required' => false,
                'default'  => 'mt1',
            ],
            'PUSHER_USE_TLS' => [
                'section'  => 'realtime',
                'label'    => 'Pusher TLS',
                'type'     => 'bool',
                'required' => true,
                'default'  => 'true',
            ],
            'DB_HOST' => [
                'section'  => 'database',
                'label'    => 'DB Host',
                'type'     => 'text',
                'required' => true,
                'default'  => '127.0.0.1',
            ],
            'DB_PORT' => [
                'section'  => 'database',
                'label'    => 'DB Port',
                'type'     => 'port',
                'required' => true,
                'default'  => '3306',
            ],
            'DB_DATABASE' => [
                'section'  => 'database',
                'label'    => 'DB Database',
                'type'     => 'text',
                'required' => true,
                'default'  => 'harray_core',
            ],
            'DB_USERNAME' => [
                'section'  => 'database',
                'label'    => 'DB Username',
                'type'     => 'text',
                'required' => true,
                'default'  => 'root',
            ],
            'DB_PASSWORD' => [
                'section'  => 'database',
                'label'    => 'DB Password',
                'type'     => 'password',
                'required' => false,
                'default'  => '',
            ],
            'MAIL_MAILER' => [
                'section'  => 'mail',
                'label'    => 'Mail Driver',
                'type'     => 'select',
                'required' => true,
                'options'  => ['log', 'smtp', 'sendmail', 'mailgun'],
                'default'  => 'log',
            ],
            'MAIL_HOST' => [
                'section'  => 'mail',
                'label'    => 'Mail Host',
                'type'     => 'text',
                'required' => true,
                'default'  => '127.0.0.1',
            ],
            'MAIL_PORT' => [
                'section'  => 'mail',
                'label'    => 'Mail Port',
                'type'     => 'port',
                'required' => true,
                'default'  => '2525',
            ],
            'MAIL_USERNAME' => [
                'section'  => 'mail',
                'label'    => 'Mail Username',
                'type'     => 'text',
                'required' => false,
                'default'  => '',
            ],
            'MAIL_PASSWORD' => [
                'section'  => 'mail',
                'label'    => 'Mail Password',
                'type'     => 'password',
                'required' => false,
                'default'  => '',
            ],
            'MAIL_FROM_ADDRESS' => [
                'section'  => 'mail',
                'label'    => 'From Address',
                'type'     => 'email',
                'required' => true,
                'default'  => 'hello@example.com',
            ],
            'MAIL_FROM_NAME' => [
                'section'  => 'mail',
                'label'    => 'From Name',
                'type'     => 'text',
                'required' => true,
                'default'  => 'Haarray Core',
            ],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     * @return array<string, array<int, mixed>>
     */
    private function rules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $key => $meta) {
            $type = $meta['type'] ?? 'text';
            $required = ($meta['required'] ?? false) ? 'required' : 'nullable';
            $options = $meta['options'] ?? [];

            $rules[$key] = match ($type) {
                'url'    => [$required, 'url', 'max:255'],
                'email'  => [$required, 'email', 'max:255'],
                'number' => [$required, 'integer', 'between:0,1000000000'],
                'port' => [$required, 'integer', 'between:1,65535'],
                'decimal' => [$required, 'numeric', 'between:0,100'],
                'bool'   => [$required, 'in:true,false,1,0,on,off'],
                'select' => array_merge([$required, 'string', 'max:255'], $options ? ['in:' . implode(',', $options)] : []),
                default  => [$required, 'string', 'max:255'],
            };
        }

        return $rules;
    }

    private function envPath(): string
    {
        return base_path('.env');
    }

    private function envWritable(): bool
    {
        $path = $this->envPath();
        return File::exists($path) ? is_writable($path) : is_writable(base_path());
    }

    /**
     * @param array<int, string> $keys
     * @return array<string, string>
     */
    private function readEnvValues(array $keys): array
    {
        $path = $this->envPath();
        if (!File::exists($path) && File::exists(base_path('.env.example'))) {
            File::copy(base_path('.env.example'), $path);
        }

        $content = File::exists($path) ? File::get($path) : '';
        $values = [];

        foreach ($keys as $key) {
            $pattern = '/^' . preg_quote($key, '/') . '=(.*)$/m';

            if (preg_match($pattern, $content, $matches) === 1) {
                $values[$key] = $this->decodeEnvValue($matches[1]);
            } else {
                $values[$key] = '';
            }
        }

        return $values;
    }

    private function decodeEnvValue(string $value): string
    {
        $value = trim($value);

        if ($value === '' || strtolower($value) === 'null') {
            return '';
        }

        $startsWithQuote = Str::startsWith($value, '"') || Str::startsWith($value, "'");
        $endsWithQuote = Str::endsWith($value, '"') || Str::endsWith($value, "'");

        if ($startsWithQuote && $endsWithQuote) {
            $value = substr($value, 1, -1);
            $value = str_replace(['\\"', '\\\\'], ['"', '\\'], $value);
        }

        return $value;
    }

    private function formatEnvValue(string $value, string $type): string
    {
        if ($type === 'bool') {
            return filter_var($value, FILTER_VALIDATE_BOOL) ? 'true' : 'false';
        }

        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'/', $value) === 1) {
            return '"' . addcslashes($value, "\\\"") . '"';
        }

        return $value;
    }

    /**
     * @param array<string, string> $updates
     */
    private function writeEnvValues(array $updates): void
    {
        $path = $this->envPath();

        if (!File::exists($path) && File::exists(base_path('.env.example'))) {
            File::copy(base_path('.env.example'), $path);
        }

        $content = File::exists($path) ? File::get($path) : '';

        foreach ($updates as $key => $value) {
            $line = $key . '=' . $value;
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            if (preg_match($pattern, $content) === 1) {
                $content = preg_replace($pattern, $line, $content, 1) ?? $content;
            } else {
                $content = rtrim($content) . PHP_EOL . $line . PHP_EOL;
            }
        }

        File::put($path, $content);
    }

    /**
     * @return array<string>
     */
    private function availableRoleNames(): array
    {
        if ($this->permissionTablesReady()) {
            $names = Role::query()->orderBy('name')->pluck('name')->all();
            if (!empty($names)) {
                return $names;
            }
        }

        return ['admin', 'manager', 'user'];
    }

    /**
     * @return array<string>
     */
    private function availablePermissionNames(): array
    {
        if (!$this->permissionTablesReady()) {
            return [];
        }

        return Permission::query()->orderBy('name')->pluck('name')->all();
    }

    /**
     * @return array<string, array{label:string, description:string, view_permission:string, manage_permission:string}>
     */
    private function accessModules(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'description' => 'Route and widgets for /dashboard.',
                'view_permission' => 'view dashboard',
                'manage_permission' => 'manage dashboard',
            ],
            'docs' => [
                'label' => 'Docs',
                'description' => 'Starter kit docs and implementation guidance.',
                'view_permission' => 'view docs',
                'manage_permission' => 'manage docs',
            ],
            'settings' => [
                'label' => 'Settings',
                'description' => 'System settings screen and env editor.',
                'view_permission' => 'view settings',
                'manage_permission' => 'manage settings',
            ],
            'users' => [
                'label' => 'Users & Roles',
                'description' => 'Role assignment and user access updates.',
                'view_permission' => 'view users',
                'manage_permission' => 'manage users',
            ],
            'notifications' => [
                'label' => 'Notifications',
                'description' => 'In-app feed and broadcast actions.',
                'view_permission' => 'view notifications',
                'manage_permission' => 'manage notifications',
            ],
            'integrations' => [
                'label' => 'Integrations',
                'description' => 'Facebook, Telegram, and external connectors.',
                'view_permission' => 'view integrations',
                'manage_permission' => 'manage integrations',
            ],
            'ml' => [
                'label' => 'ML Tools',
                'description' => 'ML suggestions and model controls.',
                'view_permission' => 'view ml',
                'manage_permission' => 'manage ml',
            ],
            'exports' => [
                'label' => 'Export/Import',
                'description' => 'Excel and data transfer actions.',
                'view_permission' => 'view exports',
                'manage_permission' => 'manage exports',
            ],
        ];
    }

    /**
     * @param array<string, array{label:string, description:string, view_permission:string, manage_permission:string}> $modules
     * @return array<string>
     */
    private function modulePermissionNames(array $modules): array
    {
        $permissions = [];

        foreach ($modules as $module) {
            $permissions[] = $module['view_permission'];
            $permissions[] = $module['manage_permission'];
        }

        return array_values(array_unique($permissions));
    }

    /**
     * @param \Illuminate\Support\Collection<int, Role> $roles
     * @param array<string, array{label:string, description:string, view_permission:string, manage_permission:string}> $modules
     * @return array<string, array<string, string>>
     */
    private function buildRoleAccessMap($roles, array $modules): array
    {
        $map = [];

        foreach ($roles as $role) {
            $permissions = $role->permissions->pluck('name')->all();

            foreach ($modules as $moduleKey => $meta) {
                if (in_array($meta['manage_permission'], $permissions, true)) {
                    $map[$role->name][$moduleKey] = 'manage';
                } elseif (in_array($meta['view_permission'], $permissions, true)) {
                    $map[$role->name][$moduleKey] = 'view';
                } else {
                    $map[$role->name][$moduleKey] = 'none';
                }
            }
        }

        return $map;
    }

    /**
     * @param \Illuminate\Support\Collection<int, Role> $roles
     * @param array<string> $modulePermissionNames
     * @return array<string, array<string>>
     */
    private function buildRoleExtraPermissionMap($roles, array $modulePermissionNames): array
    {
        $map = [];

        foreach ($roles as $role) {
            $map[$role->name] = $role->permissions
                ->pluck('name')
                ->filter(fn (string $permission) => !in_array($permission, $modulePermissionNames, true))
                ->values()
                ->all();
        }

        return $map;
    }

    private function permissionTablesReady(): bool
    {
        $tables = config('permission.table_names', []);

        if (!is_array($tables) || empty($tables)) {
            return false;
        }

        $requiredTableKeys = [
            'permissions',
            'roles',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
        ];

        foreach ($requiredTableKeys as $tableKey) {
            if (empty($tables[$tableKey])) {
                return false;
            }
        }

        return Schema::hasTable($tables['permissions'])
            && Schema::hasTable($tables['roles'])
            && Schema::hasTable($tables['model_has_permissions'])
            && Schema::hasTable($tables['model_has_roles'])
            && Schema::hasTable($tables['role_has_permissions']);
    }

    private function assertCan(Request $request, string $permission, string $message): void
    {
        $user = $request->user();

        if (!$user || !$user->can($permission)) {
            abort(403, $message);
        }
    }

    /**
     * @param array<string, string> $values
     */
    private function opsUiEnabled(array $values = []): bool
    {
        if (array_key_exists('HAARRAY_ALLOW_SHELL_UI', $values)) {
            return filter_var((string) $values['HAARRAY_ALLOW_SHELL_UI'], FILTER_VALIDATE_BOOL);
        }

        return (bool) config('haarray.ops.allow_shell_ui', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOpsSnapshot(): array
    {
        $branch = $this->runShell('git rev-parse --abbrev-ref HEAD');
        $status = $this->runShell('git status --short --branch');

        return [
            'git_branch' => $branch['output'],
            'git_status' => $status['output'],
            'db_tables' => $this->databaseTableSnapshot(),
            'log_tail' => $this->tailFile(storage_path('logs/laravel.log'), 140),
        ];
    }

    /**
     * @return array<int, array{name:string,row_count:int|null,columns:string}>
     */
    private function databaseTableSnapshot(): array
    {
        $tables = [];
        try {
            $listing = Schema::getTableListing();
        } catch (Throwable $exception) {
            return [];
        }
        sort($listing);

        foreach ($listing as $tableName) {
            $rowCount = null;
            $columns = [];

            try {
                $rowCount = (int) DB::table($tableName)->count();
            } catch (Throwable $exception) {
                $rowCount = null;
            }

            try {
                $columns = Schema::getColumnListing($tableName);
            } catch (Throwable $exception) {
                $columns = [];
            }

            $previewColumns = array_slice($columns, 0, 8);
            $columnLabel = implode(', ', $previewColumns);
            if (count($columns) > 8) {
                $columnLabel .= ' ...';
            }

            $tables[] = [
                'name' => $tableName,
                'row_count' => $rowCount,
                'columns' => $columnLabel,
            ];
        }

        return $tables;
    }

    /**
     * @return array{ok:bool, exit_code:int, output:string}
     */
    private function runShell(string $command, int $timeoutSeconds = 30): array
    {
        try {
            $process = Process::fromShellCommandline($command, base_path());
            $process->setTimeout($timeoutSeconds);
            $process->run();

            $output = trim($process->getOutput());
            $errorOutput = trim($process->getErrorOutput());
            if ($errorOutput !== '') {
                $output = trim($output . PHP_EOL . $errorOutput);
            }

            return [
                'ok' => $process->isSuccessful(),
                'exit_code' => (int) $process->getExitCode(),
                'output' => $output !== '' ? $output : '(no output)',
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'exit_code' => 1,
                'output' => $exception->getMessage(),
            ];
        }
    }

    private function tailFile(string $path, int $lines = 120): string
    {
        if (!File::exists($path)) {
            return 'Log file not found.';
        }

        $allLines = file($path, FILE_IGNORE_NEW_LINES);
        if (!is_array($allLines)) {
            return 'Unable to read log file.';
        }

        $tail = array_slice($allLines, -1 * max(20, $lines));
        return trim(implode(PHP_EOL, $tail));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMlDiagnostics(): array
    {
        /** @var AdvancedMLService $ml */
        $ml = app(AdvancedMLService::class);

        $foodWarning = (float) config('haarray.ml.food_budget_warning', 0.35);
        $savingsTarget = (float) config('haarray.ml.savings_rate_target', 0.30);
        $idleCashThreshold = (float) config('haarray.ml.idle_cash_threshold', 5000);

        $sampleProfile = $ml->classifySpendingProfile(
            $foodWarning,
            max(0.05, $foodWarning * 0.5),
            $savingsTarget,
        );

        $scenarioMap = [
            'Conservative plan' => [
                'food_ratio' => 0.16,
                'entertainment_ratio' => 0.08,
                'savings_rate' => 0.42,
            ],
            'Balanced plan' => [
                'food_ratio' => 0.27,
                'entertainment_ratio' => 0.14,
                'savings_rate' => 0.30,
            ],
            'Risky spend plan' => [
                'food_ratio' => 0.42,
                'entertainment_ratio' => 0.26,
                'savings_rate' => 0.11,
            ],
        ];

        $scenarioOutputs = [];
        foreach ($scenarioMap as $label => $input) {
            $scenarioOutputs[] = [
                'label' => $label,
                'input' => $input,
                'output' => $ml->classifySpendingProfile(
                    (float) $input['food_ratio'],
                    (float) $input['entertainment_ratio'],
                    (float) $input['savings_rate'],
                ),
            ];
        }

        $clusterSeed = [120, 180, 220, 450, 620, 890, 1200, 1450, 2500, 3200, 4500, 5600, 7200];
        $clusterSummary = $ml->clusterAmounts($clusterSeed, 3);

        $checks = [
            [
                'title' => 'PHP-ML package',
                'status' => class_exists(\Phpml\Classification\KNearestNeighbors::class),
                'note' => class_exists(\Phpml\Classification\KNearestNeighbors::class)
                    ? 'KNN class loaded.'
                    : 'Package missing. Run composer install.',
            ],
            [
                'title' => 'ML feature flag',
                'status' => (bool) config('haarray.enable_ml', true),
                'note' => (bool) config('haarray.enable_ml', true)
                    ? 'HAARRAY_ML is enabled.'
                    : 'HAARRAY_ML is disabled in environment settings.',
            ],
            [
                'title' => 'Threshold sanity',
                'status' => $foodWarning > 0 && $foodWarning < 1 && $savingsTarget > 0 && $savingsTarget < 1,
                'note' => 'Food warning and savings target must be between 0 and 1.',
            ],
        ];

        return [
            'phpml_loaded' => class_exists(\Phpml\Classification\KNearestNeighbors::class),
            'feature_enabled' => (bool) config('haarray.enable_ml', true),
            'checks' => $checks,
            'thresholds' => [
                'idle_cash_threshold' => $idleCashThreshold,
                'food_budget_warning' => $foodWarning,
                'savings_rate_target' => $savingsTarget,
            ],
            'scenario_outputs' => $scenarioOutputs,
            'cluster_seed' => $clusterSeed,
            'cluster_summary' => $clusterSummary,
            'sample_profile' => $sampleProfile,
            'probe_defaults' => [
                'food_ratio' => max(0.05, min(0.95, $foodWarning)),
                'entertainment_ratio' => max(0.05, min(0.95, round($foodWarning * 0.55, 2))),
                'savings_rate' => max(0.05, min(0.95, $savingsTarget)),
            ],
        ];
    }
}
