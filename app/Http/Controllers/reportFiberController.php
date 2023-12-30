<?php
namespace App\Http\Controllers;

use App\Models\Installations;
use Carbon\Carbon;

use Illuminate\Http\Request;

class reportFiberController extends Controller
{
  public function getReportFiberPending()
  {
    $data = Installations::getPendingInstalations(session('user'));
    
    return view('reports.fiber.viewPending', array("data"=>$data));
  }

  
}
