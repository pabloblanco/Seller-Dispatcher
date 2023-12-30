<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProvaDetail extends Model
{
    use HasFactory;

    protected $table = 'islim_stock_prova_detail';

	protected $fillable = [
        'id_stock_prova',
        'box',
        'sku',
        'msisdn',
        'iccid',
        'imei',
        'branch',
        'name',
        'users',
        'folio',
        'status',
        'statusRecycling',
        'user_assignment',
        'last_user_action',
        'reg_date_action',
        'coo_date_action',
        'comment',
        'date_reg'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\StockProvaDetail
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new StockProvaDetail;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getPendingFoliosByUser($user){
        return self::getConnect('R')
                    ->select('folio','box')
                    ->where([
                        ['users', $user],
                        ['status', 'P']
                    ])
                    ->groupBy('box')
                    ->get();
    }

    public static function getboxDetail($box, $user = ''){
        $data = self::getConnect('R')
                    ->select(
                        'islim_stock_prova_detail.id',
                        'islim_stock_prova_detail.box',
                        'islim_stock_prova_detail.sku',
                        'islim_stock_prova_detail.msisdn',
                        'islim_stock_prova_detail.status',
                        'islim_stock_prova_detail.folio',
                        'islim_stock_prova_detail.comment',
                        'islim_inv_articles.title'
                    )
                    ->leftJoin(
                        'islim_inv_articles',
                        'islim_inv_articles.sku',
                        'islim_stock_prova_detail.sku'
                    )
                    ->where([
                        ['islim_stock_prova_detail.box', $box],
                        ['islim_stock_prova_detail.status', '!=', 'T']
                    ]);

        if(!empty($user)){
            $data->where('islim_stock_prova_detail.users', $user);
        }

        return $data->get();
    }

    public static function getEDDetail($id){
        return self::getConnect('W')
                    ->where('id', $id)
                    ->first();
    }
}
