<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user settings.
     */
    public function settings()
    {
        return $this->hasOne(\App\UserSetting::class);
    }

    /**
     * Get the assigned form for the user
     */
    public function userForms()
    {
        return $this->hasMany(\App\UserForm::class, 'assigned_to');
    }

    /**
     * Get the forms created by the user
     */
    public function createdForms()
    {
        return $this->hasMany(\App\Form::class, 'created_by');
    }
}
