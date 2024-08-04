<?php

declare(strict_types=1);

namespace Yanselmask\Bookings\Traits;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Yanselmask\Bookings\Models\BookableBooking;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Bookable
{
    use BookingScopes;

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
     * Get the booking model name.
     *
     * @return string
     */
    public static function getBookingModel(): string
    {
        return config('yanselmask.bookings.models.booking');
    }

    /**
     * Get the rate model name.
     *
     * @return string
     */
    public static function getRateModel(): string
    {
        return config('yanselmask.bookings.models.bookable_rate');
    }

    /**
     * Get the rate model name.
     *
     * @return string
     */
    public static function getPriceModel(): string
    {
        return config('yanselmask.bookings.models.bookable_price');
    }

    /**
     * Get the availability model name.
     *
     * @return string
     */
    public static function getAvailabilityModel(): string
    {
        return config('yanselmask.bookings.models.availability');
    }

    /**
     * Boot the Bookable trait for the model.
     *
     * @return void
     */
    public static function bootBookable()
    {
        static::deleted(function (self $model) {
            $model->bookings()->delete();
        });
    }

    /**
     * Attach the given bookings to the model.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array $ids
     * @param mixed                                                                         $bookings
     *
     * @return void
     */
    public function setBookingsAttribute($bookings): void
    {
        static::saved(function (self $model) use ($bookings) {
            $this->bookings()->sync($bookings);
        });
    }

    /**
     * Attach the given rates to the model.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array $ids
     * @param mixed                                                                         $rates
     *
     * @return void
     */
    public function setRatesAttribute($rates): void
    {
        static::saved(function (self $model) use ($rates) {
            $this->rates()->sync($rates);
        });
    }

    /**
     * Attach the given rates to the model.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array $ids
     * @param mixed $rates
     *
     * @return void
     */
    public function setPricesAttribute($prices): void
    {
        static::saved(function (self $model) use ($prices) {
            $this->prices()->sync($prices);
        });
    }

    /**
     * Attach the given availabilities to the model.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array $ids
     * @param mixed $availabilities
     *
     * @return void
     */
    public function setAvailabilitiesAttribute($availabilities): void
    {
        static::saved(function (self $model) use ($availabilities) {
            $this->availabilities()->sync($availabilities);
        });
    }

    /**
     * The resource may have many bookings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bookings(): MorphMany
    {
        return $this->morphMany(static::getBookingModel(), 'bookable', 'bookable_type', 'bookable_id');
    }

    /**
     * Get bookings by the given customer.
     *
     * @param \Illuminate\Database\Eloquent\Model $customer
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bookingsBy(Model $customer): MorphMany
    {
        return $this->bookings()->where('customer_type', $customer->getMorphClass())->where('customer_id', $customer->getKey());
    }

    /**
     * The resource may have many availabilities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function availabilities(): MorphMany
    {
        return $this->morphMany(static::getAvailabilityModel(), 'bookable', 'bookable_type', 'bookable_id')
                    ->orderByDesc('priority');
    }

    /**
     * The resource may have many availabilities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function availabilitiesBookable(): MorphMany
    {
        return $this->availabilities()->where('is_bookable', true);
    }

    /**
     * The resource may have many availabilities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function availabilitiesNotBookable(): MorphMany
    {
        return $this->availabilities()->where('is_bookable', false);
    }


    public function openingHours()
    {
        $openingHours = $this->availabilitiesBookable()->get()
                             ->flatMap(function($availabilitiesBookable) {
                         return [$availabilitiesBookable->range => $availabilitiesBookable->data];
                         })->toArray();
        $hours =  \Spatie\OpeningHours\OpeningHours::create($openingHours);

        return $hours;
    }

    /**
     * This will allow you to display things like
     * @param $time
     * @return void
     */

    public function availabilitiesRange($time = null): void
    {
        if(!$time) {
            $time = new DateTime('now');
        }

        $range = $this->openingHours()->currentOpenRange($time);

        if ($range) {
            echo "It's open since ".$range->start()."\n";
            echo "It will close at ".$range->end()."\n";
        } else {
            echo "It's closed since ".$this->openingHours()->previousClose($time)->format('l H:i')."\n";
            echo "It will re-open at ".$this->openingHours()->nextOpen($time)->format('l H:i')."\n";
        }
    }

    /**
     * The object can be queried for a day in the week, which will return a result based on the regular schedule
     * @param $time
     * @return bool
     */
    public function AvailabilityIsOpenOn($time): bool
    {
        return $this->openingHours()->isOpenOn($time);
    }

    /**
     * It can also be queried for a specific date and time
     * @param $time
     * @return bool
     */
    public function AvailabilityIsOpenAt($time): bool
    {
        return $this->openingHours()->isOpenAt($time);
    }
    /**
     * OpeningHoursForDay object for the regular schedule
     * @param $time
     * @return \Spatie\OpeningHours\OpeningHoursForDay
     */
    public function AvailabilityForDay($time): \Spatie\OpeningHours\OpeningHoursForDay
    {
        return $this->openingHours()->forDay($time);
    }
    /**
     * OpeningHoursForDay[] for the regular schedule, keyed by day name
     * @return array
     */
    public function AvailabilityForWeek(): array
    {
        return $this->openingHours()->forWeek();
    }
    /**
     * Array of day with same schedule for the regular schedule, keyed by day name, days combined by working hours
     * @return array
     */
    public function AvailabilityForWeekCombined(): array
    {
        return $this->openingHours()->forWeekCombined();
    }
    /**
     * OpeningHoursForDay object for a specific day
     * @param $time
     * @return \Spatie\OpeningHours\OpeningHoursForDay
     */
    public function AvailabilityForDate($time): \Spatie\OpeningHours\OpeningHoursForDay
    {
        return $this->openingHours()->forDate($time);
    }
    /**
     * OpeningHoursForDay object for a specific day
     * @return array
     */
    public function AvailabilityExceptions(): array
    {
        return $this->openingHours()->exceptions();
    }
    /**
     * It can also return the next open or close DateTime from a given DateTime
     * @param $time
     */
    public function AvailabilityNextOpen($time)
    {
        return $this->openingHours()->nextOpen($time);
    }
    /**
     * It can also return the next open or close DateTime from a given DateTime
     * @param $time
     */
    public function AvailabilityNextClose($time)
    {
        return $this->openingHours()->nextClose($time);
    }
    /**
     * Checks if the business is closed on a day in the regular schedule.
     * @param $time
     * @return bool
     */
    public function AvailabilityIsClosedOn($time): bool
    {
        return $this->openingHours()->isClosedOn($time);
    }
    /**
     * Checks if the business is closed on a specific day, at a specific time.
     * @param $time
     * @return bool
     */
    public function AvailabilityIsClosedAt($time): bool
    {
        return $this->openingHours()->isClosedAt($time);
    }
    /**
     * Checks if the business is open right now.
     * @return bool
     */
    public function AvailabilityIsClosed(): bool
    {
        return $this->openingHours()->isClosed();
    }

    /**
     * The resource may have many rates.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function rates(): MorphMany
    {
        return $this->morphMany(static::getRateModel(), 'bookable', 'bookable_type', 'bookable_id');
    }

    /**
     * The resource may have many rates.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function prices(): MorphMany
    {
        return $this->morphMany(static::getPriceModel(), 'bookable', 'bookable_type', 'bookable_id');
    }

    /**
     * Book the model for the given customer at the given dates with the given price.
     *
     * @param \Illuminate\Database\Eloquent\Model $customer
     * @param string                              $startsAt
     * @param string                              $endsAt
     *
     * @return \Yanselmask\Bookings\Models\BookableBooking
     */
    public function newBooking(Model $customer, string $startsAt, string $endsAt): BookableBooking
    {
        return $this->bookings()->create([
            'bookable_id' => static::getKey(),
            'bookable_type' => static::getMorphClass(),
            'customer_id' => $customer->getKey(),
            'customer_type' => $customer->getMorphClass(),
            'starts_at' => (new Carbon($startsAt))->toDateTimeString(),
            'ends_at' => (new Carbon($endsAt))->toDateTimeString(),
        ]);
    }
    /**
     * Book the model for the given customer at the given dates with the given Availability.
     *
     * @param string $range
     * @param string $from
     * @param string $to
     * @param bool $bookeable
     * @param int $priority
     *
     * @return \Yanselmask\Bookings\Models\BookableAvailability
     */
    public function newAvailability(string $range, array $data, bool $bookeable = true, int $priority = 10):  \Yanselmask\Bookings\Models\BookableAvailability
    {
        return $this->availabilities()->create([
            'bookable_id' => static::getKey(),
            'bookable_type' => static::getMorphClass(),
            'range' => $range,
            'data' => $data,
            'is_bookable' => $bookeable,
            'priority' => $priority
        ]);
    }

    /**
     * Book the model for the given customer at the given dates with the given price.
     *
     * @param string $percentage
     * @param string $operator
     * @param int $amount
     *
     * @return \Yanselmask\Bookings\Models\BookableRate
     */
    public function newRate(int $percentage, string $operator, int $amount): \Yanselmask\Bookings\Models\BookableRate
    {
        return $this->rates()->create([
            'bookable_id' => static::getKey(),
            'bookable_type' => static::getMorphClass(),
            'percentage' => $percentage,
            'operator' => $operator,
            'amount' => $amount,
        ]);
    }

    /**
     * Book the model for the given customer at the given dates with the given price.
     *
     * @param string $range
     * @param string $from
     * @param string $to
     * @param string $percentage
     *
     * @return \Yanselmask\Bookings\Models\BookablePrice
     */
    public function newPrice($range, $from, $to, $percentage):  \Yanselmask\Bookings\Models\BookablePrice
    {
        return $this->prices()->create([
            'bookable_id' => static::getKey(),
            'bookable_type' => static::getMorphClass(),
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'percentage' => $percentage
        ]);
    }
}
