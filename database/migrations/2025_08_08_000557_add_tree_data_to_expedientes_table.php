<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->longText('tree_data')->nullable()->after('archivo_pdf');
        });
    }

    public function down()
    {
        Schema::table('expedientes', function (Blueprint $table) {
            $table->dropColumn('tree_data');
        });
    }
};
