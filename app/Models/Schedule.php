<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
class Schedule extends Model
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
 public function service()
 {
  return $this->belongsTo('App\Models\Service');
}

public function rating()
{
  return $this->belongsTo('App\Models\Rating');
}

public function rating_user()
{
  return $this->hasMany('App\Models\RatingUser','schedule_id','id');
}

}
