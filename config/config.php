<?php

declare(strict_types=1);

return [

    // Manage autoload migrations
    'autoload_migrations' => true,

    // Bookings Database Tables
    'tables' => [
        'bookable_rates' => 'bookable_rates',
        'bookable_prices' => 'bookable_prices',
        'bookable_bookings' => 'bookable_bookings',
        'bookable_availabilities' => 'bookable_availabilities',
        'ticketable_bookings' => 'ticketable_bookings',
        'ticketable_tickets' => 'ticketable_tickets',
    ],

    'models' => [
        'availability' => \Yanselmask\Bookings\Models\BookableAvailability::class,
        'booking' => \Yanselmask\Bookings\Models\BookableBooking::class,
        'bookable_rate' => \Yanselmask\Bookings\Models\BookableRate::class,
        'bookable_price' => \Yanselmask\Bookings\Models\BookablePrice::class,
        'ticket' => \Yanselmask\Bookings\Models\Ticketable::class
    ],

];
