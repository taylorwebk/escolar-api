<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Curso extends Model
{
  protected $table = 'curso';
  public $timestamps = false;

  public function cursas() {
    return $this->hasMany('\Models\Cursa');
  }
}
