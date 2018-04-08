<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Profesor extends Model
{
    protected $guarded = [];
    protected $table = 'profesor';
    public $timestamps = false;
    public function materias() {
      return $this->belongsToMany('\Models\Materia')->withPivot('estado');
    }
}

