<?php

namespace App\Models;

use App\Utilities\Api815;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiberArticleZone extends Model
{
  use HasFactory;
  protected $table = 'islim_fiber_article_zone';

  protected $fillable = [
    'id',
    'fiber_zone_id',
    'article_id',
    'product_pk',
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

  public static function getArticleZone($fiberZone, $idArticle)
  {
    $ArtInstall = self::getConnect('R')
      ->select('islim_fiber_article_zone.article_id',
        'islim_fiber_article_zone.product_pk',
        'islim_inv_articles.title',
        'islim_inv_articles.model',
        'islim_inv_articles.brand')
      ->join('islim_inv_articles',
        'islim_inv_articles.id',
        'islim_fiber_article_zone.article_id')
      ->where([
        ['islim_fiber_article_zone.article_id', $idArticle],
        ['islim_fiber_article_zone.fiber_zone_id', $fiberZone],
        ['islim_fiber_article_zone.status', 'A']])
      ->first();

    if (!empty($ArtInstall)) {

      $responseArt = Api815::getArticle($fiberZone, $ArtInstall->product_pk);
      $name815     = '';
      if ($responseArt['success']) {
        $name815 = $responseArt['data'];
      }

      $artNetwey = '';
      if (!empty($ArtInstall->model) && !empty($ArtInstall->brand)) {
        $artNetwey = $ArtInstall->model . ' - ' . $ArtInstall->brand;
      } else {
        $artNetwey = $ArtInstall->title;
      }

      $description = "";
      if (!empty($name815) && !empty($artNetwey)) {
        $description = $name815 . ' ( ' . $artNetwey . ' )';
      } else {
        $description = $artNetwey;
      }

      return array('success' => true, 'description' => $description, 'article_id' => $ArtInstall->article_id);
    }
    return array('success' => false);
  }
}
