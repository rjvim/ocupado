<?php 
namespace Betalectic\FileManager\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {

	protected $table = 'file_manager_attachments';

    protected $fillable = [];

    protected $guarded = [];
    
    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function media()
    {
        return $this->belongsTo('Betalectic\FileManager\Models\MediaLibrary', 'media_library_id');
    }

    public function of()
    {
        return $this->morphTo(); 
    }
}
