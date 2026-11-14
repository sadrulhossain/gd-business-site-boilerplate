<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_2nd')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('unit', 80)->nullable();
            $table->string('unit_2nd', 80)->nullable();
            $table->text('description')->nullable();
            $table->text('description_2nd')->nullable();
            // One feature per line.
            $table->text('features')->nullable();
            $table->text('features_2nd')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->index(['service_id', 'is_visible', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricings');
    }
};
