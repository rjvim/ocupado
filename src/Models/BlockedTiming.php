<?php 
namespace Betalectic\Ocupado\Models;

use Illuminate\Database\Eloquent\Model;

use Betalectic\Ocupado\Traits\UUIDTrait;

class BlockedTiming extends Model {

    use UUIDTrait;

	protected $table = 'ocupado_blocked_timings';

    protected $fillable = [];

    protected $guarded = [];
    
    protected $dates = ['start_time','end_time'];

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
