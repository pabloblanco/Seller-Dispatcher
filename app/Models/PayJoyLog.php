<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayJoyLog extends Model
{
    use HasFactory;

    protected $table = 'islim_log_payjoy';

	protected $fillable = [
        'ip',
        'header',
        'data_in',
        'data_out',
        'type',
        'date_reg'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\PayJoyLog
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new PayJoyLog;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para registrar los logs generados por payjoy
     * @param String $ip
     * @param String $header
     * @param String $data_in
     * 
     * @return App\Models\PayJoyLog
    */
    public static function saveLog($ip = false, $header = false, $data_in = false){
    	$log = self::getConnect('W');

    	if($ip){
    		$log->ip = $ip;
    	}

    	if($header){
    		$log->header = $header;
    	}

    	if($data_in){
    		$log->data_in = $data_in;
    	}

    	$log->type = 'D';
    	$log->date_reg = date('Y-m-d H:i:s');

    	$log->save();

    	return $log;
    }
}
