<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete(); // انجام‌دهنده
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();  // مالک پس از تغییر (null = انبار)
            $table->foreignId('previous_owner_id')->nullable()->constrained('users')->nullOnDelete(); // مالک قبل از تغییر
            $table->string('action')->default('update'); // update | release | assign
            $table->json('changes')->nullable();          // {"title": ["قدیم","جدید"], ...}
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_edits');
    }
};
