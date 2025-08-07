<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'description',
        'brand',
        'model',
        'price',
        'purchase_date',
        'condition',
        'notes',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'date',
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the equipment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
