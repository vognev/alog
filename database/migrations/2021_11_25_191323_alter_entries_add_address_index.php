<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEntriesAddAddressIndex extends Migration
{
    public function up()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->index(['address']);
        });
    }

    public function down()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex(['address']);
        });
    }
}
