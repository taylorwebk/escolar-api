<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Instruye extends Model
{
  protected $guarded = [];
  protected $table = 'instruye';
  public $timestamps = false;

  public function cursa() {
    return $this->belongsTo('\Models\Cursa');
  }
  public function gestion() {
    return $this->belongsTo('\Models\Gestion');
  }
  public function profesor() {
    return $this->belongsTo('\Models\Profesor');
  }
  public function trabajos() {
    return $this->hasMany('\Models\Trabajo');
  }
}
