<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
class Service extends Model
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
 public function ratings()
 {
  return $this->hasMany('App\Models\Rating');
}

function user()
{
 return $this->belongsTo('App\Models\User');
}

function service_partition()
{
 return $this->hasMany('App\Models\ServicePartition','service_id','id');
}
}
