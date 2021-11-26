<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('log_id');

            $table->string('host');

            $table->bigInteger('address');

            $table->timestamp('stamp');

            $table->string('method');

            $table->text('path');

            $table->text('query')->nullable();

            $table->foreign('log_id')
                ->references('id')->on('logs')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entries');
    }
}
