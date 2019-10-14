<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TransactionForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->bigInteger('category_id')->unsigned()->change();
            $table->bigInteger('account_id')->unsigned()->change();

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('category_id');
            $table->dropForeign('account_id');
        });
    }
}
