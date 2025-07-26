<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id('packageId');
            $table->unsignedBigInteger('categoryId');
            $table->unsignedBigInteger('vendorId')->nullable();
            $table->string('name');
            $table->string('menuPDFPath')->nullable();
            $table->string('imgPath')->nullable();
            $table->decimal('averageCalories', 8, 2)->nullable();
            $table->decimal('breakfastPrice', 12, 2)->nullable();
            $table->decimal('lunchPrice', 12, 2)->nullable();
            $table->decimal('dinnerPrice', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('categoryId')->references('categoryId')->on('package_categories')->onDelete('restrict');
            $table->foreign('vendorId')->references('vendorId')->on('vendors')->onDelete('cascade');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
