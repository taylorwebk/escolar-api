<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Inscribe extends Model
{
    protected $guarded = [];
    protected $table = 'inscribe';
    public $timestamps = false;
    public function curso() {
        return $this->belongsTo('\Models\Curso');
    }
    public function estudiante() {
        return $this->belongsTo('\Models\Estudiante');
    }
}

