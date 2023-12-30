<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberServiceZone extends Model
{
  use HasFactory;

  protected $table = 'islim_fiber_service_zone';

  protected $fillable = [
    'id',
    'fiber_zone_id',
    'service_id',
    'service_pk',
    'status'
  ];

  public $timestamps = false;

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
}
