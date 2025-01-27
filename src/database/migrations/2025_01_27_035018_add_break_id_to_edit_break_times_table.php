<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBreakIdToEditBreakTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('edit_break_times', function (Blueprint $table) {
            $table->foreignId('break_id')->nullable()->constrained('breaks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('edit_break_times', function (Blueprint $table) {
            $table->dropForeign(['break_id']);
            $table->dropColumn('break_id');
        });
    }
}
