<?php

use Illuminate\Database\Migrations\Migration;

// This migration is a no-op: the create migration (000001) already creates
// the column as 'specifications', so there is nothing to rename on a fresh install.
return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
