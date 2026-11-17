<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Not named "references": REFERENCES is a reserved word in SQL.
     */
    public function up(): void
    {
        Schema::create('product_references', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('description');
            $table->string('unit', 10);
            $table->decimal('unit_price', 12, 2);
            $table->foreignId('vat_type_id')->index()->constrained()->restrictOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_references');
    }
};
