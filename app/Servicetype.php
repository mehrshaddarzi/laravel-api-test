<?php

namespace Blegrator;

use Illuminate\Database\Eloquent\Model;

class Servicetype extends Model
{
    protected $table = 'servicetypes';
    public static $Image_Path = 'upload/images/services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'coverphoto',
        'icon',
        'description'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Get the Services for the Service Type.
     */
    public function service()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the User for the Service type.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
