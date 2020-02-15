<?php

namespace Blegrator;

use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{

    protected $table = 'service_item';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_id',
        'user_id',
        'name',
        'excerpt',
        'description',
        'is_active',
        'period',
        'price'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Has Many Belongs for Services
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Has Many Belongs for User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set Bootstrap
     */
    protected static function boot()
    {
        parent::boot();

//        static::deleting(function ($service) {
//        });
    }
}
