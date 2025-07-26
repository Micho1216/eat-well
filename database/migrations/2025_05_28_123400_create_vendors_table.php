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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id('vendorId');
            $table->unsignedBigInteger('userId');
            // $table->unsignedBigInteger('addressId');
            $table->string('name');
            $table->string('breakfast_delivery')->nullable();
            $table->string('lunch_delivery')->nullable();
            $table->string('dinner_delivery')->nullable();
            // $table->string('description');
            $table->string('logo');
            $table->string('phone_number', 20);
            $table->decimal('rating', 2, 1)->default(0);
            $table->timestamps();

            $table->softDeletes();
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            // $table->foreign('addressId')->references('addressId')->on('addresses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
