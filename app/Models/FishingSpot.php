<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FishingSpot extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'water_type',
        'access_type',
        'tips',
        'user_id',
    ];

    /**
     * Get the user that owns the fishing spot.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the catches for the fishing spot.
     */
    public function catches()
    {
        return $this->hasMany(FishCatch::class);
    }

    /**
     * Get the fish species associated with the fishing spot.
     */
    public function fishSpecies()
    {
        return $this->belongsToMany(FishSpecies::class, 'fishing_spot_fish_species');
    }
}
