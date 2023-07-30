<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
 use HasFactory;
 use SoftDeletes;

 protected $guarded=[];
 public $incrementing=false;

 protected static function boot() {
    parent::boot();
    static::creating(function ($model) {
        $model->id = (string) Str::uuid();
    });
}

function user()
{
   return $this->belongsTo('App\Models\User');
}
}
