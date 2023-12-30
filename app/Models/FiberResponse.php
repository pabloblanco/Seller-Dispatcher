<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberResponse extends Model
{
  use HasFactory;
  protected $table = 'islim_fiber_response';

  protected $fillable = [
    'id',
    'description',
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

  public static function getQuestionInstall($question_id, $install_id)
  {
    return self::getConnect('R')
      ->select(
        'islim_fiber_response.description',
        'islim_fiber_response.type')
      ->join('islim_fiber_question_response', 'islim_fiber_question_response.response_id', 'islim_fiber_response.id')
      ->join('islim_fiber_questions_result', 'islim_fiber_questions_result.question_response_id', 'islim_fiber_question_response.id')
      ->where('islim_fiber_question_response.question_id', $question_id)
      ->where('islim_fiber_questions_result.installation_id', $install_id)
      ->get();
  }

  public static function getOptionByQuestion($questionId)
  {
    return self::getConnect('R')
      ->select(
        'islim_fiber_question_response.id',
        'islim_fiber_response.description',
        'islim_fiber_response.type')
      ->join('islim_fiber_question_response', 'islim_fiber_question_response.response_id', 'islim_fiber_response.id')
      ->where('islim_fiber_question_response.status', 'A')
      ->where('islim_fiber_response.status', 'A')
      ->where('islim_fiber_question_response.question_id', $questionId)
      ->orderBy('islim_fiber_question_response.order', 'asc')
      ->get();
  }
}
