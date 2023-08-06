<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class ProductCategory extends Model
{
   use HasFactory;

   protected $guarded=[];
   public $incrementing=false;

   protected static function boot() {
     parent::boot();
     static::creating(function ($model) {
       $model->id = (string) Str::uuid();
    });
  }

  function parent()
  {
   return $this->belongsTo('App\Models\ProductCategory','parent_id','id');
}

function child()
{
   return $this->hasMany('App\Models\ProductCategory','parent_id','id');
}
}
