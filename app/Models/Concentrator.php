<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concentrator extends Model
{
    use HasFactory;

    protected $table = 'islim_concentrators';

	protected $fillable = [
        'id',
        'name',
        'rfc',
        'email',
        'dni',
        'business_name',
        'phone',
        'address',
        'balance',
        'commissions',
        'date_reg',
        'status',
        'postpaid',
        'amount_alert',
        'amount_allocate',
        'id_channel'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\models\Concentrator
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Concentrator;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para asignar blance a un concentrador
     *
     * @param String $id
     * @param Double $balance
     * 
     * @return App\models\Concentrator
    */
    public static function setBalance($id, $balance){
    	return self::getConnect('W')
    				->where('id', $id)
    				->update([
    					'balance' => $balance
    				]);
    }
}
