<?php

namespace App\Models;

use App\Models\FiberResponse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberQuestions extends Model
{
  use HasFactory;

  protected $table = 'islim_fiber_questions';

  protected $fillable = [
    'id',
    'description',
    'type',
    'status',
    'destiny',
    'order',
    'platform'];

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

  public static function getQuestionsByPlatform($dest = false)
  {
    $questions = self::getAllQuestion($dest);

    foreach ($questions as $value) {

      $value->options = FiberResponse::getOptionByQuestion($value->id);
    }

    return $questions;
  }

  public static function getAllQuestion($dest = false)
  {
    $pre = self::getConnect('R')
      ->where('status', 'A')
      ->where('platform', 'SELLER');

    if ($dest) {
      $pre = $pre->where('destiny', $dest);
    }
    return $pre->get();
  }

  public static function getQuestionForInstall($idInstaller)
  {
    return self::getConnect('R')
      ->select(
        'islim_fiber_questions.id',
        'islim_fiber_questions.description as q_description',
        'islim_fiber_questions.destiny',
        'islim_fiber_questions_result.question_result')
      ->join('islim_fiber_question_response', 'islim_fiber_question_response.question_id', 'islim_fiber_questions.id')
      ->join('islim_fiber_questions_result', 'islim_fiber_questions_result.question_response_id', 'islim_fiber_question_response.id')
      ->where('islim_fiber_questions_result.installation_id', $idInstaller)
      ->where('islim_fiber_questions.platform', 'SELLER')
      ->orderBy('islim_fiber_questions.order', 'ASC')
      ->distinct()
      ->get();
  }
}
