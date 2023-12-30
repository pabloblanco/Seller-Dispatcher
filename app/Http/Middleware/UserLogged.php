<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;

class UserLogged
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
        $routeName = $request->route()->getName();
        $routes = session('permits_route');

        if(empty($user)){
            session()->flash('message_class', 'alert-danger');
            session()->flash('message_error', 'Debes iniciar session.'); 

            return redirect()->route('login');
        }elseif(!in_array($routeName, $routes)){
            session()->flash('message_class', 'alert-danger');
            session()->flash('message_error', 'No tienes permisos para ver esta sección.');
             
            return redirect()->route('dashboard');
        }else{
            $userBD = User::canLogin(session('user'));

            if(!empty($userBD) && $userBD->reset_session == 'N'){
                return $next($request);
            }

            session()->flush();
            return redirect()->route('login');            
        }
    }
}
