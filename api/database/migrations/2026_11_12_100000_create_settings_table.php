<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            // Not auto-increment: MariaDB cannot put a CHECK on an auto-increment column, and this table only ever holds id 1.
            $table->unsignedTinyInteger('id')->primary();
            $table->string('site_name');
            $table->string('site_name_2nd')->nullable();
            $table->string('tagline')->nullable();
            $table->string('tagline_2nd')->nullable();
            $table->text('address')->nullable();
            $table->text('address_2nd')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->jsonb('hours')->nullable();
            $table->jsonb('social_links')->nullable();
            $table->timestamps();
        });

        // Singleton: the table can only ever hold row 1.
        DB::statement('alter table settings add constraint settings_singleton check (id = 1)');
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
