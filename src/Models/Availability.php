<?php 
namespace Betalectic\Ocupado\Models;

use Illuminate\Database\Eloquent\Model;

use Betalectic\Ocupado\Traits\UUIDTrait;

class Availability extends Model {

    use UUIDTrait;

	protected $table = 'ocupado_availability';

    protected $fillable = [];

    protected $guarded = [];
    
    protected $dates = ['from_date','to_date'];

    protected $UUIDCode = 'uuid';

    protected $casts = [
        'meta' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uniquify();
        });

    }

}
