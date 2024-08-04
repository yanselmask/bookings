<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookableAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('yanselmask.bookings.tables.bookable_availabilities'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->morphs('bookable');
            $table->string('range');
            $table->json('data');
            $table->boolean('is_bookable')->default(false);
            $table->smallInteger('priority')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('yanselmask.bookings.tables.bookable_availabilities'));
    }
}
