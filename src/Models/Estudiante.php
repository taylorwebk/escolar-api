<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Estudiante extends Model
{
    protected $guarded = [];
    protected $table = 'estudiante';
    protected $hidden = ['password'];
    public $timestamps = false;
    public function apoderado() {
      return $this->belongsTo('\Models\Apoderado');
    }
}

