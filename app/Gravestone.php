<?php

namespace Blegrator;

use Illuminate\Database\Eloquent\Model;

class Gravestone extends Model
{

    protected $table = 'gravestones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'orderitem_id',
        'cemetery_id',
        'description',
        'stonesize',
        'grave_block',
        'grave_column',
        'grave_row',
        'identefier_code'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Get the Deceased for the Gravestone.
     */
    public function deceased()
    {
        return $this->hasMany(Deceased::class);
    }


    /**
     * Get the OrderItem for the Gravestone.
     */
    public function orderitem()
    {
        return $this->belongsTo(Orderitem::class);
    }


    /**
     * Get the Cemetery for the Gravestone.
     */
    public function cemetery()
    {
        return $this->belongsTo(Cemetery::class);
    }
}
