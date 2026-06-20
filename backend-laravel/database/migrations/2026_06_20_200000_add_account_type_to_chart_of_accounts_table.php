<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ACCOUNT_TYPE_INCOME = 'income';
    private const ACCOUNT_TYPE_EXPENSE = 'expense';
    private const ACCOUNT_TYPES = [
        self::ACCOUNT_TYPE_INCOME,
        self::ACCOUNT_TYPE_EXPENSE,
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->enum('account_type', self::ACCOUNT_TYPES)
                ->default(self::ACCOUNT_TYPE_EXPENSE)
                ->after('category_id');
        });

        DB::table('chart_of_accounts')
            ->where('code', 'like', '4%')
            ->update(['account_type' => self::ACCOUNT_TYPE_INCOME]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropColumn('account_type');
        });
    }
};
