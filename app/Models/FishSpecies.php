<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FishSpecies extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'scientific_name',
        'description',
        'average_weight',
        'average_length',
        'habitat',
        'season',
        'image_path',
        'fishing_tips',
    ];

    /**
     * Get the catches for the fish species.
     */
    public function catches()
    {
        return $this->hasMany(FishCatch::class);
    }

    /**
     * Get the fishing spots associated with the fish species.
     */
    public function fishingSpots()
    {
        return $this->belongsToMany(FishingSpot::class, 'fishing_spot_fish_species');
    }
}
