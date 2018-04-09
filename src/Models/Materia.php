<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Materia extends Model
{
    protected $guarded = [];
    protected $table = 'materia';
    public $timestamps = false;
    public function profesores() {
        return $this->belongsToMany('\Models\Profesor')->withPivot('estado');
    }
    public function cursas() {
        return $this->hasMany('\Models\Cursa');
    }
}

