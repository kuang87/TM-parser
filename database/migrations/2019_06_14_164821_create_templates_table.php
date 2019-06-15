<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('vendor_id');
            $table->uuid('author_id');
            $table->unsignedBigInteger('template_id');
            $table->boolean('state');
            $table->decimal('price', 12, 3);
            $table->unsignedBigInteger('downloads');
            $table->string('preview');
            $table->timestamps();

            $table->primary('id');

            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('author_id')->references('id')->on('authors');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('templates');
    }
}
