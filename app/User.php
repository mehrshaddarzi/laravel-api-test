<?php

namespace Blegrator;

use Illuminate\Validation\Rule;
use Mail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Blegrator\Events\User\RequestedPasswordResetEmail;
use Blegrator\Presenters\Traits\Presentable;
use Blegrator\Presenters\UserPresenter;
use Blegrator\Services\Auth\Api\TokenFactory;
use Blegrator\Services\Auth\TwoFactor\Authenticatable as TwoFactorAuthenticatable;
use Blegrator\Services\Auth\TwoFactor\Contracts\Authenticatable as TwoFactorAuthenticatableContract;
use Blegrator\Support\Authorization\AuthorizationUserTrait;
use Blegrator\Support\CanImpersonateUsers;
use Blegrator\Support\Enum\UserStatus;

class User extends Authenticatable implements TwoFactorAuthenticatableContract, JWTSubject, MustVerifyEmail
{
    use TwoFactorAuthenticatable,
        CanResetPassword,
        Presentable,
        AuthorizationUserTrait,
        Notifiable,
        CanImpersonateUsers;

    protected $presenter = UserPresenter::class;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected $dates = ['last_login', 'birthday'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'username', 'first_name', 'last_name', 'phone', 'avatar',
        'address', 'country_id', 'birthday', 'last_login', 'confirmation_token', 'status',
        'remember_token', 'role_id', 'email_verified_at', 'physical_address', 'website', 'city', 'street', 'zipcode',
        'parent_id', 'company_name', 'company_registration_number'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Always encrypt password when it is updated.
     *
     * @param $value
     * @return string
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function setBirthdayAttribute($value)
    {
        $this->attributes['birthday'] = trim($value) ?: null;
    }

    public function gravatar()
    {
        $hash = hash('md5', strtolower(trim($this->attributes['email'])));

        return sprintf("https://www.gravatar.com/avatar/%s?size=150", $hash);
    }

    public function isUnconfirmed()
    {
        return $this->status == UserStatus::UNCONFIRMED;
    }

    public function isActive()
    {
        return $this->status == UserStatus::ACTIVE;
    }

    public function isBanned()
    {
        return $this->status == UserStatus::BANNED;
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function parent()
    {
        return $this->belongsTo('User', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('User', 'parent_id');
    }

    /**
     * Get the Services for the User.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_user'); //->withTimestamps();
    }

    /**
     * Get the Service Types for the User.
     */
    public function servicetypes()
    {
        return $this->hasMany(Servicetype::class);
    }

    /**
     * Has Many Service Item
     */
    public function serviceitem()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the Cemeteries for the User.
     */
    public function cemeteries()
    {
        return $this->belongsToMany(Cemetery::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->id;
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return ['jti' => app(TokenFactory::class)->forUser($this)->id];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        Mail::to($this)->send(new \Blegrator\Mail\ResetPassword($token));

        event(new RequestedPasswordResetEmail($this));
    }
}
