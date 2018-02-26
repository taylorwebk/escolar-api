<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Bimestre extends Model
{
    protected $guarded = [];
    protected $table = 'bimestre';
    public $timestamps = false;
    public function gestiones() {
      return $this->belongsToMany('\Models\Gestion')->withPivot('active');
    }
}

