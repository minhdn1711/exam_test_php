<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    /**
     * Directory (under public/assets/img) where uploaded hotel images are stored
     */
    public const IMAGE_DIR = 'hotel';

    /**
     * @var string
     */
    protected $primaryKey = 'hotel_id';

    /**
     * @var array
     */
    protected $guarded = ['hotel_id'];

    /**
     * @return BelongsTo
     */
    public function prefecture(): BelongsTo
    {
        return $this->belongsTo(Prefecture::class, 'prefecture_id', 'prefecture_id');
    }

    /**
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'hotel_id', 'hotel_id');
    }

    /**
     * Search hotel by hotel name (partial match) and prefecture
     *
     * @param string|null $hotelName
     * @param int|null $prefectureId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    static public function getHotelListByName(?string $hotelName, ?int $prefectureId = null, int $perPage = 20): LengthAwarePaginator
    {
        $result = Hotel::when($hotelName !== null && $hotelName !== '', function ($query) use ($hotelName) {
                // escape LIKE wildcards so that "%" or "_" are searched literally
                $query->where('hotel_name', 'like', '%' . addcslashes($hotelName, '\\%_') . '%');
            })
            ->when($prefectureId !== null, function ($query) use ($prefectureId) {
                $query->where('prefecture_id', $prefectureId);
            })
            ->with('prefecture')
            // most recently updated first
            ->orderByDesc('updated_at')
            ->orderByDesc('hotel_id')
            ->paginate($perPage);

        return $result;
    }

    /**
     * Whether the image was uploaded from the admin screen (seeded sample images must not be deleted)
     *
     * @return bool
     */
    public function hasUploadedImage(): bool
    {
        return $this->file_path !== null && str_starts_with($this->file_path, self::IMAGE_DIR . '/');
    }

    /**
     * Override serializeDate method to customize date format
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
