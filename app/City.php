<?php

namespace Blegrator;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{

    protected $table = 'cities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'region_id',
        'name',
        'lat',
        'long',
        'address',
        'photo'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Get the Cemeteries for the City.
     */
    public function cemetery()
    {
        return $this->hasMany(Cemetery::class);
    }


    /**
     * Get the Region for the City.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
