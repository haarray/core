<?php
// ============================================================
// HAARRAY / HARILOG — ALL MIGRATIONS
// Run: php artisan migrate
// ============================================================

// ── 1. ADD TELEGRAM + ROLE TO USERS ─────────────────────────
// File: database/migrations/2026_02_17_000001_add_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('email');
            $table->string('telegram_username')->nullable()->after('telegram_chat_id');
            $table->string('role')->default('user')->after('telegram_username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_username', 'role']);
        });
    }
};

// ── 2. CATEGORIES ────────────────────────────────────────────
// File: database/migrations/2026_02_17_000002_create_categories_table.php
/*
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('icon')->default('📦');
    $table->string('color')->default('#9898b8');
    $table->enum('type', ['expense', 'income', 'investment'])->default('expense');
    $table->boolean('is_system')->default(false); // system categories can't be deleted
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null = global
    $table->timestamps();
});
*/

// ── 3. ACCOUNTS ──────────────────────────────────────────────
// File: database/migrations/2026_02_17_000003_create_accounts_table.php
/*
Schema::create('accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');                     // "NMB Bank", "eSewa", "Cash"
    $table->enum('type', [
        'bank', 'esewa', 'khalti', 'cash',
        'connectips', 'fonepay', 'other'
    ])->default('bank');
    $table->decimal('balance', 15, 2)->default(0);
    $table->string('account_number')->nullable();
    $table->string('color')->default('#f5a623');
    $table->string('icon')->default('🏦');
    $table->boolean('is_default')->default(false);
    $table->timestamp('last_synced_at')->nullable();
    $table->timestamps();
});
*/

// ── 4. TRANSACTIONS ──────────────────────────────────────────
// File: database/migrations/2026_02_17_000004_create_transactions_table.php
/*
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('account_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
    $table->decimal('amount', 15, 2);
    $table->enum('type', ['credit', 'debit', 'transfer'])->default('debit');
    $table->text('note')->nullable();
    $table->enum('source', ['manual', 'telegram', 'sms', 'auto'])->default('manual');
    $table->date('transaction_date');
    $table->string('reference')->nullable();   // bank ref number
    $table->json('meta')->nullable();           // extra data from SMS parsing
    $table->timestamps();

    $table->index(['user_id', 'transaction_date']);
    $table->index(['user_id', 'type']);
    $table->index(['user_id', 'category_id']);
});
*/

// ── 5. INVESTMENTS (Portfolio) ───────────────────────────────
// File: database/migrations/2026_02_17_000005_create_investments_table.php
/*
Schema::create('investments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('type', [
        'gold', 'shares', 'mutual_fund', 'ipo',
        'fd', 'rd', 'crypto', 'land', 'other'
    ]);
    $table->string('name');                     // "Nabil Bank shares", "Gold 2 tola"
    $table->decimal('quantity', 15, 4);         // tola, units, etc
    $table->decimal('buy_price', 15, 2);        // price per unit at purchase
    $table->decimal('current_price', 15, 2)->nullable();
    $table->date('purchase_date');
    $table->string('symbol')->nullable();        // NEPSE symbol e.g. "NABIL"
    $table->text('notes')->nullable();
    $table->timestamps();
});
*/

// ── 6. IPOS ──────────────────────────────────────────────────
// File: database/migrations/2026_02_17_000006_create_ipos_table.php
/*
Schema::create('ipos', function (Blueprint $table) {
    $table->id();
    $table->string('company_name');
    $table->string('symbol')->nullable();
    $table->date('open_date');
    $table->date('close_date');
    $table->decimal('price_per_unit', 10, 2)->default(100);
    $table->integer('min_units')->default(10);
    $table->integer('max_units')->nullable();
    $table->enum('status', ['upcoming', 'open', 'closed', 'listed'])->default('upcoming');
    $table->date('allotment_date')->nullable();
    $table->string('sector')->nullable();        // Banking, Hydropower, etc
    $table->text('notes')->nullable();
    $table->timestamps();
});
*/

// ── 7. MARKET CACHE ──────────────────────────────────────────
// File: database/migrations/2026_02_17_000007_create_market_cache_table.php
/*
Schema::create('market_cache', function (Blueprint $table) {
    $table->id();
    $table->string('data_type')->unique(); // 'gold', 'nepse', 'forex', etc
    $table->json('data_json');
    $table->timestamp('fetched_at');
    $table->timestamps();
});
*/

// ── 8. SUGGESTIONS ───────────────────────────────────────────
// File: database/migrations/2026_02_17_000008_create_suggestions_table.php
/*
Schema::create('suggestions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->text('message');
    $table->string('type');         // 'ipo', 'spending', 'investment', 'savings'
    $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
    $table->string('icon')->default('💡');
    $table->boolean('is_read')->default(false);
    $table->timestamps();

    $table->index(['user_id', 'is_read']);
});
*/
