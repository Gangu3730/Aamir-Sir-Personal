<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->after('company');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->timestamp('edited_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('comments', fn (Blueprint $t) => $t->dropColumn('edited_at'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('country'));
    }
};
