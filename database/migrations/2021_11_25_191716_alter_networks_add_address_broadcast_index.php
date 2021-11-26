<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNetworksAddAddressBroadcastIndex extends Migration
{
    public function up()
    {
        Schema::table('networks', function (Blueprint $table) {
            $table->index(['address', 'broadcast']);
        });
    }

    public function down()
    {
        Schema::table('networks', function (Blueprint $table) {
            $table->dropIndex(['address', 'broadcast']);
        });
    }
}
