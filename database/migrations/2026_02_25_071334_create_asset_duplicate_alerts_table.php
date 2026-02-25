<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('asset_duplicate_alerts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('new_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('original_user_id')->constrained('users')->onDelete('cascade');

            $table->string('asset_number');

            $table->timestamp('original_created_at');
            $table->boolean('is_read')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_duplicate_alerts');
    }
};
