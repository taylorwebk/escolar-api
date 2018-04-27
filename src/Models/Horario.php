<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Horario extends Model
{
  protected $table = 'horario';
  public $timestamps = false;

  public function cursa() {
    return $this->belongsTo('\Models\Cursa');
  }
  public function gestion() {
    return $this->belongsTo('\Models\Gestion');
  }
  public function periodo() {
    return $this->belongsTo('\Models\Periodo');
  }
}
