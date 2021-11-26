<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEntriesAddNetworkId extends Migration
{
    public function up()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->unsignedBigInteger('network_id')
                ->nullable()
                ->after('log_id');
        });
    }

    public function down()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn('network_id');
        });
    }
}
