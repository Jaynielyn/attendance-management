<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNewBreakColumnsFromEditRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('edit_requests', function (Blueprint $table) {
            $table->dropColumn(['new_break_start', 'new_break_end']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('edit_requests', function (Blueprint $table) {
            $table->time('new_break_start')->nullable();
            $table->time('new_break_end')->nullable();
        });
    }
}
