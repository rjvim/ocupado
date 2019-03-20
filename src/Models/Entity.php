<?php 
namespace Betalectic\Ocupado\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model {

	protected $table = 'ocupado_entities';

    protected $fillable = [];

    protected $guarded = [];
    
    protected $dates = [];

    public function availability()
    {
    	return $this->hasMany(Availability::class,'entity_id');
    }

}
