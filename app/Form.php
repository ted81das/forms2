<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use \App\Traits\UsesUuid;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $appends = ['media_url'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'schema' => 'array',
        'mailchimp_details' => 'array',
        'acelle_mail_info' => 'array',
        'webhook_info' => 'array',
    ];

    public function getMediaUrlAttribute()
    {
        return asset('/uploads/'.config('constants.doc_path').'/');
    }

    /**
     * Get the data for the form.
     */
    public function data()
    {
        return $this->hasMany(\App\FormData::class);
    }

    /**
     * Get the created by for the form.
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * Get the assigned user for form.
     */
    public function assignedTo()
    {
        return $this->hasMany(\App\UserForm::class, 'form_id');
    }

    /**
     * Get the visitors for form
     */
    public function visitors()
    {
        return $this->hasMany(\App\Visitor::class, 'form_id');
    }
}
