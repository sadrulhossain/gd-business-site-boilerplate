<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('excerpt_2nd');
            $table->string('seo_title_2nd')->nullable()->after('seo_title');
            $table->string('seo_description', 320)->nullable()->after('seo_title_2nd');
            $table->string('seo_description_2nd', 320)->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_title_2nd', 'seo_description', 'seo_description_2nd']);
        });
    }
};
