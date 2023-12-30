<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerWare extends Model
{
    use HasFactory;

    protected $table = 'islim_seller_ware';

    protected $fillable = [
        'id_ware', 
        'email', 
        'status', 
        'date_reg'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\SellerWare
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new SellerWare;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener la bodega a las bodegas a la que se encuentra asociado un 
     * usuario dado su email
     * @param String $typeCon
     * 
     * @return App\Models\SellerWare
    */
    public static function getIdWare($email){
    	return self::getConnect('R')
    				->select('id_ware')
    				->where([
    					['email', $email],
                      	['status', 'A']
    				])
    				->get();
    }
}
