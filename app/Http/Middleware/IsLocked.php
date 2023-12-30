<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class IsLocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = session('user');

        if(!empty($user)){
            $lock = User::getOnliyUser($user);

            if(!empty($lock) && $lock->is_locked == 'N'){
                return $next($request);
            }

            if($request->ajax()){
                return response()->json([
                            'error' => true,
                            'message' => 'Has sido bloqueado, por favor contacta a tu supervisor.'
                       ]);
            }

            session()->flash('message_class', 'alert-danger');
            session()->flash('message_error', 'Has sido bloqueado, por favor contacta a tu supervisor.'); 

            return redirect()->route('dashboard');
        }
    }
}
