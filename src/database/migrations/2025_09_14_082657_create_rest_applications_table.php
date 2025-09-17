<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rest_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('rest_start_at')->nullable();
            $table->timestamp('rest_end_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rest_applications');
        Schema::dropIfExists('work_applications');
        Schema::dropIfExists('rests');
        Schema::dropIfExists('works');
    }
}
