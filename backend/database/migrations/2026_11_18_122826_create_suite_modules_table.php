<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Public content of the corporate page: the modules of SoftNeos Suite.
     */
    public function up(): void
    {
        Schema::create('suite_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('summary', 255);
            $table->boolean('is_new')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suite_modules');
    }
};
