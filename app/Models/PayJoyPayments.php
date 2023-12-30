<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayJoyPayments extends Model
{
    use HasFactory;

    protected $table = 'islim_payjoy_payments';

	protected $fillable = [
		'id_payjoy',
        'amount',
        'date_reg',
        'status'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\PayJoyPayments
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new PayJoyPayments;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}
