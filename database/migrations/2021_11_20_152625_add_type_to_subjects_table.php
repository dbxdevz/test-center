<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('first')->default(false); // negizgi pan
            $table->boolean('second')->default(false); // zharatylys tanu
            $table->boolean('third')->default(false); // gumanatitariya
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('first');
            $table->dropColumn('second');
            $table->dropColumn('third');
        });
    }
}
