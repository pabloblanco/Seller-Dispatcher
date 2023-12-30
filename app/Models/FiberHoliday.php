<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberHoliday extends Model
{
  use HasFactory;
  protected $table = 'islim_fiber_holiday';

  protected $fillable = [
    'id',
    'day',
    'month',
    'year',
    'type',
    'status'];

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
/**
 * [getListFeriado Obtiene los dias feriados]
 * @param  boolean $type      [indica si es tipo full o media]
 * @param  boolean $all       [muestras todas las fechas que se repitan todos los anos o en fechas especificas]
 * @param  boolean $recursive [fechas que se repiten cada ano o no]
 * @return [type]             [description]
 */
  public static function getFeriado($type = false, $all = true, $recursive = false)
  {
    $data = self::getConnect('R')
      ->where('status', 'A');

    if ($type) {
      $data = $data->where('type', $type);
    }
    if (!$all) {
      if ($recursive) {
        $data = $data->whereNotNull('year');
      } else {
        $data = $data->whereNull('year');
      }
    }
    return $data->orderBy('day', 'ASC')
      ->orderBy('month', 'ASC')
      ->get();
  }

/**
 * [getDayFeriado Retorna la lista de dias del ano en curso y el siguiente que sera feriado incluyendo los medios dias]
 * @param  array  $BlockDay [array de dias feriados]
 * @return [type]           [full, media]
 */
  public static function getDayFeriado($BlockDay = [], $type)
  {
    //  $BlockDay = ["1-12-2022", "26-12-2022"];
    // [Dia - mes - ano]
    $holiday = self::getFeriado($type, true);
    if (!empty($holiday)) {
      foreach ($holiday as $itemDay) {
        $festive = new \stdClass;
        if (empty($itemDay->year)) {
          //repetitivo
          $festive->fecha = $itemDay->day . '-' . $itemDay->month . date('-Y');
          $festive->type = $itemDay->type;
          array_push($BlockDay, $festive);
          //Siguiente ano
          $proximoY = date("-Y", strtotime("+ 1 year", time()));
          $festive = new \stdClass;
          $festive->fecha = $itemDay->day . '-' . $itemDay->month . $proximoY;
          $festive->type = $itemDay->type;
          array_push($BlockDay, $festive);
        } elseif (strtotime($itemDay->year) == date('Y')) {
          //Ano en especifico
          $festive->fecha = $itemDay->day . '-' . $itemDay->month . '-' . $itemDay->year;
          $festive->type = $itemDay->type;
          array_push($BlockDay, $festive);
        }
      }
    }
    return $BlockDay;
  }

}
