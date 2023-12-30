<?php

namespace App\Models;

use App\Models\FiberQuestionResponse;
use App\Models\FiberQuestions;
use App\Models\FiberResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FiberQuestionResult extends Model
{
  protected $table = 'islim_fiber_questions_result';

  protected $fillable = [
    'installation_id',
    'question_response_id',
    'question_result'];

  public $timestamps = false;

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\FiberQuestionResult
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
      return $obj;
    }
    return null;
  }

  public static function insertAnswers($install, $answers)
  {

    $questions = FiberQuestions::getAllQuestion('END');

    foreach ($questions as $value) {

      if ($value->type == 'MS') {

        $options = FiberQuestionResponse::getByQuestion($value->id);

        foreach ($options as $option) {

          if (!empty($answers['question-' . $value->id . '-' . $option->id])) {
            try {
              self::getConnect('W')
                ->insert([
                  'installation_id' => $install,
                  'question_response_id' => $option->id,
                  'question_result' => null,
                ]);
            } catch (Exception $e) {
              $txmsg = 'Error al insertar una respuesta a una pregunta. ' . (String) json_encode($e->getMessage());
              Log::error($txmsg);
              return array('success' => false, 'msg' => $txmsg);
            }
          }
        }
      } else {

        if (!empty($answers['question-' . $value->id])) {
          if ($value->type == 'TX') {

            $question_response_id = FiberQuestionResponse::getQuestionTX($value->id);

            if (!empty($question_response_id)) {

              try {
                self::getConnect('W')
                  ->insert([
                    'installation_id' => $install,
                    'question_response_id' => $question_response_id->id,
                    'question_result' => $answers['question-' . $value->id],
                  ]);
              } catch (Exception $e) {
                $txmsg = 'Error al insertar una respuesta a una pregunta. ' . (String) json_encode($e->getMessage());
                Log::error($txmsg);
                return array('success' => false, 'msg' => $txmsg);
              }
            }
          } else {
            try {
              self::getConnect('W')
                ->insert([
                  'installation_id' => $install,
                  'question_response_id' => $answers['question-' . $value->id],
                  'question_result' => @$answers['question-' . $value->id . '-ot'],
                ]);
            } catch (Exception $e) {
              $txmsg = 'Error al insertar una respuesta a una pregunta. ' . (String) json_encode($e->getMessage());
              Log::error($txmsg);
              return array('success' => false, 'msg' => $txmsg);
            }
          }
        }
      }
    }
    return array('success' => true, 'msg' => 'OK');
  }

  public static function getAnswersById($id, $dest = false)
  {
    if ($id) {

      $answers = FiberQuestions::getQuestionForInstall($id);
      foreach ($answers as $question) {

        $question->answers = FiberResponse::getQuestionInstall($question->id, $id);
      }
      if (count($answers) > 0 && $dest) {
        $answers = $answers->where('destiny', $dest);
      }

      return $answers;
    }

    return [];
  }
}
