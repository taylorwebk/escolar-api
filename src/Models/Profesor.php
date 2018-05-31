<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Profesor extends Model
{
    protected $guarded = [];
    protected $table = 'profesor';
    protected $hidden = ['password'];
    public $timestamps = false;
    public function materias() {
      return $this->belongsToMany('\Models\Materia')->withPivot('estado');
    }
    public function instruyes() {
      return $this->hasMany('\Models\Instruye');
    }
}

