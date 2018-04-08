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
}

