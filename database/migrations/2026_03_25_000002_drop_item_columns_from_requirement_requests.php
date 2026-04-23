<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->dropColumn(['item', 'qty', 'dimensions']);
        });
    }

    public function down(): void
    {
        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->string('item')->nullable();
            $table->integer('qty')->nullable();
            $table->string('dimensions')->nullable();
        });
    }
};
