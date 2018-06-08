<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Comunicado extends Model
{
    protected $guarded = [];
    protected $table = 'comunicado';
    public $timestamps = false;
    public function admin() {
      return $this->belongsTo('\Models\Admin');
    }
}

