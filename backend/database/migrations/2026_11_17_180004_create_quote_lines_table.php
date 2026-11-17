<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * description, unit_price and vat_rate are snapshots taken when the line
     * is created, so later changes to the reference or to VAT law do not
     * alter quotes already issued.
     */
    public function up(): void
    {
        Schema::create('quote_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->index()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->foreignId('product_reference_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->foreignId('vat_type_id')->index()->constrained()->restrictOnDelete();
            $table->decimal('vat_rate', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_lines');
    }
};
