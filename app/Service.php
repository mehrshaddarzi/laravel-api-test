<?php

namespace Blegrator;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{

    protected $table = 'services';
    public static $Image_Path = 'upload/images/services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'servicetype_id',
        'name',
        'coverphoto',
        'icon',
        'description',
        'avg_price',
        'commission_percentage',
        'commission_desc',
        'is_active'
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
     * Get the Service Type for the Service.
     */
    public function servicetype()
    {
        return $this->belongsTo(Servicetype::class);
    }

    /**
     * Get the Users for the Service.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'service_user'); //->withTimestamps();
    }

    /**
     * Get Service Item List
     */
    public function serviceitem()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Scope a query to only include get by User ID
     *
     */
    public function scopeuser($query, $value)
    {
        return $query->whereHas('users', function ($q) use ($value) {
            $q->where('service_user.user_id', $value);
        });
    }

    /**
     * Set Bootstrap
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($service) {
            $service->users()->detach();
        });
    }
}
