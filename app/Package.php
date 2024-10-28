<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public static function list()
    {
        $list = [
            'days' => __('messages.days'),
            'months' => __('messages.months'),
            'year' => __('messages.year'),
        ];

        return $list;
    }

    public static function activePackages()
    {
        $packages = self::where('is_active', 1)
                        ->pluck('name', 'id');

        return $packages;
    }
}
