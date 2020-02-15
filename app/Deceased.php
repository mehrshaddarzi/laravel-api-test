<?php

namespace Blegrator;

use Illuminate\Database\Eloquent\Model;

class Deceased extends Model
{

    protected $table = 'deceaseds';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'gravestone_id',
        'first_name',
        'last_name'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['birth_date', 'death_date', 'created_at', 'updated_at'];

    /**
     * Get the Gravestone for the Deceased.
     */
    public function gravestone()
    {
        return $this->belongsTo(Gravestone::class);
    }
}
