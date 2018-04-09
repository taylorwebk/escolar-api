<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Dia extends Model
{
    protected $guarded = [];
    protected $table = 'dia';
    public $timestamps = false;
    public function periodos() {
      return $this->hasMany('\Models\Periodo');
    }
}

