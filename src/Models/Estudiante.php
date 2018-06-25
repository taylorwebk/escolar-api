<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
use \Models\Utils;
class Estudiante extends Model
{
    protected $guarded = [];
    protected $table = 'estudiante';
    protected $hidden = ['password'];
    public $timestamps = false;
    public function apoderado() {
      return $this->belongsTo('\Models\Apoderado');
    }
    public function inscribes() {
      return $this->hasMany('\Models\Inscribe');
    }
    public function mainInscribe() {
      return $this->hasMany('\Models\Inscribe')->where('gestion_id', Utils::getCurrentYear()->id)->with('curso');
    }
    public function trabajos() {
      return $this->belongsToMany('\Models\Trabajo')->withPivot('nota');
    }
}

