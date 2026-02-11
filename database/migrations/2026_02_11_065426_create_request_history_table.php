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
        Schema::create('request_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_request_id')->constrained('requirement_requests')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // 'created', 'edited', 'approved', 'rejected', 'assigned', 'in_progress', 'completed'
            $table->json('changes')->nullable(); // JSON object with field changes
            $table->text('remarks')->nullable(); // For action-specific remarks
            $table->timestamps();

            $table->index(['requirement_request_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_history');
    }
};
