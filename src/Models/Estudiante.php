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
    public function inscribes() {
      return $this->hasMany('\Models\Inscribe');
    }
    public function mainInscribe() {
      return $this->hasMany('\Models\Inscribe')->where('gestion_id', 1)->with('curso');
    }
}

