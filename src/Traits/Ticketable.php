<?php

declare(strict_types=1);

namespace Yanselmask\Bookings\Traits;

use Illuminate\Database\Eloquent\Model;
use Yanselmask\Bookings\Models\TicketableBooking;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Ticketable
{
    /**
     * Register a saved model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    abstract public static function saved($callback);

    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    abstract public static function deleted($callback);

    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    /**
     * Get the ticket model name.
     *
     * @return string
     */
     public function getTicketModel(): string
     {
         return config('yanselmask.bookings.models.ticket');
     }

    /**
     * Boot the Ticketable trait for the model.
     *
     * @return void
     */
    public static function bootTicketable()
    {
        static::deleted(function (self $model) {
            $model->bookings()->delete();
        });
    }

    /**
     * The resource may have many tickets.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function tickets(): MorphMany
    {
        return $this->morphMany(static::getTicketModel(), 'ticketable', 'ticketable_type', 'ticketable_id');
    }

}
