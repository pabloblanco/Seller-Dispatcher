<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Log;

class ArticlePack extends Model
{
    use HasFactory;

    protected $table = 'islim_arti_packs';

	protected $fillable = [
        'pack_id',
        'inv_article_id',
        'retail',
        'status'
    ];

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\models\ArticlePack
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new ArticlePack;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener los ids de los packs asociados a una lista de articulos
     * filtrados por el tipo de vendedor del pack "Retail(R)" o "Normal(N)"
     * @param Array $idsArtics
     * @param String $type
     * 
     * @return App\models\ArticlePack
    */
    public static function getRelationPack($idsArtics = [], $type = 'N'){
        if(is_array($idsArtics) && count($idsArtics)){
            $type = $type == 'R' ? 'Y' : 'N';

            $data = self::getConnect('R')
                         ->select('pack_id')
                         ->where([
                            ['status', 'A'],
                            ['retail', $type]
                         ])
                         ->whereIn('inv_article_id', $idsArtics);

            // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
            //     return is_numeric($binding) ? $binding : "'{$binding}'";
            // })->toArray());

            // Log::alert($query);

            return $data->get();
        }

        return [];
    }

    /**
     * Metodo para obtener información de los packs asociados a un articulo y
     * filtrados por el tipo de vendedor del pack "Retail(R)" o "Normal(N)"
     * @param Integer $idsArtics
     * @param String $type
     * 
     * @return App\models\ArticlePack
    */
    public static function getInfoPackByArticle($idArtic, $type = 'N'){
        return self::getConnect('R')
                    ->select(
                        'islim_arti_packs.pack_id',
                        'islim_packs.title',
                        'islim_packs.description'
                    )
                    ->join(
                        'islim_packs',
                        'islim_packs.id',
                        'islim_arti_packs.pack_id'
                    )
                    ->where([
                        ['islim_arti_packs.status', 'A'],
                        ['islim_packs.status', 'A'],
                        ['islim_arti_packs.inv_article_id', $idArtic],
                        ['islim_arti_packs.retail', $type]
                    ])
                    ->get();
    }

    /**
     * Metodo para obtener información de detalle de articulos asociados a un pack
     * @param Integer $idPack
     * @param Array $idsAssig
     * 
     * @return App\models\ArticlePack
    */
    public static function getArticsByAssigAndPack($idPack = false, $idsAssig = []){
        if($idPack && is_array($idsAssig) && count($idsAssig)){
            return self::getConnect('R')
                         ->select(
                            'islim_inv_arti_details.id',
                            'islim_inv_arti_details.inv_article_id',
                            'islim_inv_arti_details.iccid',
                            'islim_inv_arti_details.serial',
                            'islim_inv_arti_details.msisdn',
                            'islim_inv_arti_details.imei',
                            'islim_inv_articles.brand',
                            'islim_inv_articles.model'
                         )
                         ->join(
                            'islim_inv_arti_details',
                            function($join) use ($idsAssig){
                                $join->on(
                                    'islim_arti_packs.inv_article_id',
                                    'islim_inv_arti_details.inv_article_id'
                                )
                                ->where('islim_inv_arti_details.status', 'A')
                                ->whereIn('islim_inv_arti_details.id', $idsAssig);
                            }
                         )
                         ->join('islim_inv_articles',
                            'islim_inv_articles.id',
                            'islim_inv_arti_details.inv_article_id')
                         ->where([
                            ['islim_arti_packs.status', 'A'],
                            ['islim_arti_packs.pack_id', $idPack]
                         ])
                         ->get();                            
        }

        return [];
    }

    public static function getArticPackByPackId($id){
        return self::getConnect('R')
                    ->select(
                        'inv_article_id',
                        'retail'
                    )
                    ->where([
                        ['pack_id', $id],
                        ['status', 'A']
                    ])
                    ->first();
    }
}
