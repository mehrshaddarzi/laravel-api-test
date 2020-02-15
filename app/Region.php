<?php

namespace Blegrator;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{

    protected $table = 'regions';
    public static $Image_Path = 'upload/images/cemetery';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
     * Get the Cities for the Region.
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
