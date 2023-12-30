<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'islim_profiles';

    protected $fillable = [
        'id', 'name', 'description', 'status', 'type', 'hierarchy'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\Profile
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Profile;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Metodo para obtener el cargo mas bajo que pertenece a un tipo de perfil
     * @param String $type
     * 
     * @return App\Models\Profile
    */
    public static function getLowHer($type){
    	return self::getConnect('R')
    				->select('name', 'type', 'hierarchy')
    				->where([
    					['status', 'A'],
    					['type', $type]
    				])
    				->orderBy('hierarchy', 'DESC')
    				->first();
    }
}
