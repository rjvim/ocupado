<?php 
namespace Betalectic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Betalectic\FileManager\Traits\UUIDTrait;

class Library extends Model {

    use SoftDeletes;
    use UUIDTrait;

	protected $table = 'file_manager_library';

    protected $fillable = [];

    protected $guarded = [];
    
    protected $dates = [];

    protected $UUIDCode = 'uuid';

    protected $casts = [
        'meta' => 'array',
        'tags' => 'array',
    ];

    public static $rules = [
        // Validation rules
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uniquify();
        });

        static::deleting(function ($file) {
            
            foreach($file->attachments as $attachment)
            {
                $attachment->delete();
            }

        });

    }

    public function attachments()
    {
    	return $this->hasMany(Attachment::class,'library_id');
    }

}
