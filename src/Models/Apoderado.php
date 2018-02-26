<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Apoderado extends Model
{
    protected $guarded = [];
    protected $table = 'apoderado';
    protected $hidden = ['id'];
    public $timestamps = false;
    
    public function estudiante() {
      return $this->hasOne('\Models\Estudiante');
    }
}

