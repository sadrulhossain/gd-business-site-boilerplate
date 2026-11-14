<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->default('USD')->after('favicon');
            $table->string('currency_symbol', 10)->nullable()->default('$')->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['currency', 'currency_symbol']);
        });
    }
};
