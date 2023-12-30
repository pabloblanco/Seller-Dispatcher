<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProva extends Model
{
    use HasFactory;

    protected $table = 'islim_stock_prova';

	protected $fillable = [
        'file_name',
        'date_reg',
        'status'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Models\StockProva
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new StockProva;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}
