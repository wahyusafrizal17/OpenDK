<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDasDataDesa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('das_data_desa', function($table) {
            $table->dropColumn('kecamatan_id');
        });

        Schema::table('das_data_desa', function (Blueprint $table) {
            $table->integer('profil_id')->after('id')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('das_data_desa', function (Blueprint $table) {
            $table->char('kecamatan_id', 8);
        });

        Schema::table('das_data_desa', function($table) {
            $table->dropColumn('profil_id');
        });
    }
}