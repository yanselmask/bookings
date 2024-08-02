<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookableBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.bookings.tables.bookable_bookings'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->morphs('bookable');
            $table->morphs('customer');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->string('timezone')->nullable();
            $table->decimal('price')->default('0.00');
            $table->integer('quantity')->unsigned();
            $table->decimal('total_paid')->default('0.00');
            $table->string('currency', 3);
            $table->json('formula')->nullable();
            $table->schemalessAttributes('options');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists(config('rinvex.bookings.tables.bookable_bookings'));
    }
}
