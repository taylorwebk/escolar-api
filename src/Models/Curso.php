<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
use \Models\Utils;
class Curso extends Model
{
  protected $guarded = [];
  protected $table = 'curso';
  public $timestamps = false;

  public function cursas() {
    return $this->hasMany('\Models\Cursa');
  }
  public function inscribes() {
    return $this->hasMany('\Models\Inscribe');
  }
  public function inscribescurrent() {
    return $this->hasMany('\Models\Inscribe')->where('gestion_id', Utils::getCurrentYear()->id);
  }
}
