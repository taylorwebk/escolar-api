<?php
namespace Models;
use Illuminate\Database\Eloquent\Model;
class Admin extends Model
{
    protected $guarded = [];
    protected $table = 'admin';
    protected $hidden = ['password'];
    public $timestamps = false;
}

