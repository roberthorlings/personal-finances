<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AccountStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->unsigned();

            $table->integer('month');
            $table->integer('year');

            $table->decimal('balance');
            $table->timestamps();

            $table->index(['account_id', 'year', 'month'], 'account_month_year');

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
        Schema::dropIfExists('account_stats');
    }
}
