<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentityVerification extends Model
{
    use HasFactory;

    protected $table = 'islim_identity_verification';

	protected $fillable = [
        'clients_dni',
        'msisdn',
        'user',
        'process_id',
        'account_id',
        'url_redirect',
        'resp_process',
        'date_reg',
        'date_update',
        'status',
        'status_code'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Models\IdentityVerification
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new IdentityVerification;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getPrevValidationActive($msisdn, $client){
        return self::getConnect('W')
                    ->select(
                        'id',
                        'user',
                        'process_id', 
                        'status',
                        'url_redirect',
                        'date_update'
                    )
                    ->where([
                        ['clients_dni', $client],
                        ['msisdn', $msisdn]
                    ])
                    ->whereIn('status', ['S', 'I'])
                    ->orderBy('date_update', 'DESC')
                    ->first();
    }

    public static function getVerificationInProcess($id){
        return self::getConnect('W')
                    ->select(
                        'id',
                        'process_id',
                        'user',
                        'status'
                    )
                    ->where([
                        ['id', $id],
                        ['status', 'I']
                    ])
                    ->first();
    }

    public static function getSuccesVerification($msisdn, $client){
        return self::getConnect('R')
                    ->select('id')
                    ->where([
                        ['status', 'S'],
                        ['clients_dni', $client],
                        ['msisdn', $msisdn]
                    ])
                    ->first();
    }
}
