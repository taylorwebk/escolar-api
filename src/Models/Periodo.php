<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Periodo extends Model
{
    protected $guarded = [];
    protected $table = 'periodo';
    public $timestamps = false;
    public function dia() {
      return $this->belongsTo('\Models\Dia');
    }
}

