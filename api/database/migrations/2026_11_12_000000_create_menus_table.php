<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->cascadeOnDelete();
            // Permission that makes the entry visible; null = group shown only with visible children.
            $table->string('permission_name')->nullable()->unique();
            $table->string('title');
            $table->unsignedInteger('sort')->default(0);
            $table->string('icon')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
