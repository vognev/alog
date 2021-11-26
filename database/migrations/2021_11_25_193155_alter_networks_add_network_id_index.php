<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNetworksAddNetworkIdIndex extends Migration
{
    public function up()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->index(['network_id']);
        });
    }

    public function down()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex(['network_id']);
        });
    }
}
