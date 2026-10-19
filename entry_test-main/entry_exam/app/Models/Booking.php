<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    /**
     * @var string
     */
    protected $primaryKey = 'booking_id';

    /**
     * @var array
     */
    protected $guarded = ['booking_id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'checkin_time' => 'datetime',
            'checkout_time' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'hotel_id');
    }
}
