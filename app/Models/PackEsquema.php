<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackEsquema extends Model
{
    use HasFactory;

    protected $table = 'islim_pack_esquema';

	protected $fillable = [
        'id_pack',
        'id_esquema',
        'date_reg',
        'status'
    ];
    
    public $timestamps = false;

    public static function getEsquemasByPack($pack){
        return self::select(
                        'islim_pack_esquema.id_esquema'
                    )
                    ->where([
                        ['islim_pack_esquema.id_pack', $pack],
                        ['islim_pack_esquema.status', 'A']
                    ])
                    ->get();
    }
}
