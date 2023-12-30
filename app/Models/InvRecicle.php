<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvRecicle extends Model
{
    use HasFactory;

    protected $table = 'islim_inv_reciclers';

	protected $fillable = [
		'inv_article_id',
        'warehouses_id',
        'serial',
        'iccid',
        'imei',
        'imsi',
        'date_reception',
        'date_sending',
        'price_pay',
        'date_reg',
        'status',
        'obs',
        'msisdn_sufijo',
        'checkAltan',
        'checkOffert',
        'msisdn',
        'user_netwey',
        'origin_netwey',
        'user_mail',
        'ReciclerType',
        'date_update',
        'detail_error'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\InvRecicle
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new InvRecicle;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
    	return null;
    }

    public static function markToRecicle($msisdn, $user){
        $recicle = self::getConnect('W');

        $recicle->status = 'M';
        $recicle->obs = 'Enviado a reciclar desde el seller';
        $recicle->checkAltan = 'N';
        $recicle->checkOffert = 'N';
        $recicle->msisdn = $msisdn;
        $recicle->user_netwey = $user;
        $recicle->ReciclerType = 'P';
        $recicle->origin_netwey = 'seller';
        $recicle->date_reg = date('Y-m-d H:i:s');
        $recicle->date_update = date('Y-m-d H:i:s');
        $recicle->save();

        return $recicle;
    }

    /**
     * [get_recicler_in_process Revisa si el DN esta en espera de cron de proceso de reciclaje]
     * @param  [type] $msisdn [description]
     * @return [type]         [description]
     */
      public static function get_recicler_in_process($msisdn)
      {
        $existe = self::getConnect('W')
            ->where('msisdn', $msisdn)
            ->whereIn('status', ['C'])
            ->get();

        if (!empty($existe)) {
          return $existe;
        }
        return null;
      }
}
