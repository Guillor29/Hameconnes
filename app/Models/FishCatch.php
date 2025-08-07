<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FishCatch extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'catches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'fishing_spot_id',
        'fish_species_id',
        'weight',
        'length',
        'date_caught',
        'description',
        'photo_path',
        'weather_conditions',
        'bait_used',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_caught' => 'datetime',
    ];

    /**
     * Get the user that owns the catch.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the fishing spot that owns the catch.
     */
    public function fishingSpot()
    {
        return $this->belongsTo(FishingSpot::class);
    }

    /**
     * Get the fish species that owns the catch.
     */
    public function fishSpecies()
    {
        return $this->belongsTo(FishSpecies::class);
    }
}
