<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberQuestionResponse extends Model
{
  use HasFactory;
  protected $table = 'islim_fiber_question_response';

  protected $fillable = [
    'id',
    'question_id',
    'response_id',
    'order',
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

  public static function getByQuestion($questionId)
  {
    return self::getConnect('R')
      ->where('question_id', $questionId)
      ->where('status', 'A')
      ->orderBy('order', 'ASC')
      ->get();
  }

  public static function getQuestionTX($questionId)
  {
    return self::getConnect('R')
      ->select('id')
      ->where('question_id', $questionId)
      ->where('response_id', null)
      ->orderBy('order', 'ASC')
      ->first();
  }

}
