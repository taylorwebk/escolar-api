<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Trabajo extends Model
{
    protected $guarded = [];
    protected $table = 'trabajo';
    public $timestamps = false;
    public function instruye() {
      return $this->belongsTo('\Models\Instruye');
    }
    public function bimestre() {
      return $this->belongsTo('\Models\Bimestre');
    }
}

