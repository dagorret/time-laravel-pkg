<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('time_test_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->string('status'); // success, warning, error
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('time_test_logs'); }
};
