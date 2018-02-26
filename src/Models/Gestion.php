<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Gestion extends Model
{
    protected $guarded = [];
    protected $table = 'gestion';
    public $timestamps = false;
    public function bimestres() {
      return $this->belongsToMany('\Models\Bimestre')->withPivot('active');
    }
    public function current() {
      return $this->belongsToMany('\Models\Bimestre')->wherePivot('active', 1);
    }
}

