<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftService extends Model
{
    use HasFactory;

    protected $table = 'islim_gift_services';

	protected $fillable = [
        'msisdn',
        'service_id',
        'id_sale',
        'activation_date',
        'expired_date',
        'activated_date',
        'date_reg',
        'comment',
        'status'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\GiftService
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new GiftService;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}
