<?php

// database/migrations/xxxxxx_add_show_contract_button_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowContractButtonToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // true یعنی دکمه نمایش داده شود، false یعنی مخفی باشد
            $table->boolean('show_contract_button')->default(false); // یا هر جای مناسب
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('show_contract_button');
        });
    }
}

