<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Cursa extends Model
{
  protected $guarded = [];
  protected $table = 'cursa';
  public $timestamps = false;

  public function curso() {
    return $this->belongsTo('\Models\Curso');
  }
  public function materia() {
    return $this->belongsTo('\Models\Materia');
  }
  public function instruyes() {
    return $this->hasMany('\Models\Instruye');
  }
  public function horarios() {
    return $this->hasMany('\Models\Horario');
  }
}
