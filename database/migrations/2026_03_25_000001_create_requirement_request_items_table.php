<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirement_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_request_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('item');
            $table->integer('qty');
            $table->string('specifications')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['requirement_request_id', 'sort_order'], 'rri_request_sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirement_request_items');
    }
};
