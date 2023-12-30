<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Migration extends Model
{
    use HasFactory;

    protected $table = 'islim_migrations';

	protected $fillable = [
		'msisdn_old',
        'msisdn_new',
        'type',
        'status',
        'date_reg'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\Migration
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Migration;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
    	return null;
    }

    public static function addMigration($dnOld, $dnNew, $type, $status){
    	return self::getConnect('W')
    				->insert([
    					'msisdn_old' => $dnOld,
    					'msisdn_new' => $dnNew,
    					'type' => $type,
    					'status' => $status,
    					'date_reg' => date('Y-m-d H:i:s')
    				]);
    }
}
