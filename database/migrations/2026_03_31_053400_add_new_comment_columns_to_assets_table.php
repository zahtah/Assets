<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('assets', function (Blueprint $table) {
            $table->boolean('admin_new_comment')->default(0);
            $table->boolean('user_new_comment')->default(0);
        });
    }

    public function down() {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['admin_new_comment', 'user_new_comment']);
        });
    }
};
