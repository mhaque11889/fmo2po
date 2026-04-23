<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reassign sort_order sequentially to fix any existing duplicates
        $categories = DB::table('categories')->orderBy('sort_order')->orderBy('id')->get();
        foreach ($categories as $i => $cat) {
            DB::table('categories')->where('id', $cat->id)->update(['sort_order' => $i + 1]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['sort_order']);
        });
    }
};
