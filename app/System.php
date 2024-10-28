<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public static function getVersion()
    {
        $row = self::where('key', 'version')
                ->first();

        if (! empty($row)) {
            return $row->value;
        } else {
        }
    }

    public static function updateSystem($rows)
    {
        foreach ($rows as $key => $value) {
            self::where('key', $key)
                ->update(['value' => $value]);
        }
    }

    /**
     * Returns the date formats
     */
    public static function dateFormats()
    {
        return [
            'd-m-Y' => 'dd-mm-yyyy',
            'm-d-Y' => 'mm-dd-yyyy',
            'd/m/Y' => 'dd/mm/yyyy',
            'm/d/Y' => 'mm/dd/yyyy',
        ];
    }

    /**
     * Return the value of the given key
     *
     * @param $key string
     * @return mixed
     */
    public static function getValue($key)
    {
        $row = self::where('key', $key)
                ->first();

        if (isset($row->value)) {
            return $row->value;
        }

        return null;
    }
}
