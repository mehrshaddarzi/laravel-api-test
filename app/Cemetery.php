<?php

namespace Blegrator;

use Illuminate\Database\Eloquent\Model;

class Cemetery extends Model
{

    protected $table = 'cemeteries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'city_id',
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
     * Get the Gravestone for the Cemetery.
     */
    public function gravestone()
    {
        return $this->hasOne(Gravestone::class);
    }


    /**
     * Get the City for the Cemetery.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }


    /**
     * Get the Users for the Cemetery.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
