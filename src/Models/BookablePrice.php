<?php

declare(strict_types=1);

namespace Yanselmask\Bookings\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookablePrice extends Model
{
    use HasFactory;
    use ValidatingTrait;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'bookable_id',
        'bookable_type',
        'range',
        'from',
        'to',
        'percentage',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'bookable_id' => 'integer',
        'bookable_type' => 'string',
        'range',
        'from',
        'to',
        'percentage',
    ];

    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'validating',
        'validated',
    ];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Whether the model should throw a
     * ValidationException if it fails validation.
     *
     * @var bool
     */
    protected $throwValidationExceptions = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable(config('yanselmask.bookings.tables.bookable_prices'));
        $this->mergeRules([
            'bookable_id' => 'required|integer',
            'bookable_type' => 'required|string|strip_tags|max:150',
            'range' => 'required|in:datetimes,dates,months,weeks,days,times,sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'from' => 'required|string|strip_tags|max:150',
            'to' => 'required|string|strip_tags|max:150',
            'percentage' => 'required|string',
        ]);
        parent::__construct($attributes);
    }

    /**
     * Get the owning resource model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function bookable(): MorphTo
    {
        return $this->morphTo('bookable', 'bookable_type', 'bookable_id', 'id');
    }
}
