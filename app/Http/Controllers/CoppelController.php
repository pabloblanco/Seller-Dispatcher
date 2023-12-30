<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CoppelController extends Controller
{
    public function testCoppel(Request $request){
        return view('seller.coppel');
    }
}
