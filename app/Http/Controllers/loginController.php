<?php

namespace App\Http\Controllers;

use App\Mail\resetPassword;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\SellerWare;
use App\Models\User;
use App\Models\UserRole;
use App\Utilities\Common;
use App\Utilities\Google;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class loginController extends Controller
{
  public function index(Request $request)
  {
    if ($request->isMethod('post')) {
      $rg = Google::veifyCaptchaGoogle($request->get('recaptcha'), $request->ip(), $request);
      if ($rg['success']) {
        $inputs = $request->all();
        $routeName = $request->route()->getName();

        $this->validate($request, [
          'emailLogin' => 'required',
          'passLogin' => 'required',
        ]);

        $user = User::getUser($inputs['emailLogin'], 'R', ['A', 'D']);

        if (!empty($user) && Hash::check($inputs['passLogin'], $user->password)) {
          $routes = UserRole::getRolPolicies($user->email, ($user->status == 'D'));

          $mapRoutes = Common::getMapRoute();

          $routes = $routes->pluck('code');
          $permits = [];
          $permitsCode = [];
          foreach ($routes as $route) {
            if (!empty($mapRoutes[$route])) {
              $permits = array_merge($mapRoutes[$route], $permits);
            }
            $permitsCode[] = $route;
          }

          if (in_array($routeName, $permits)) {
            session(['permits_route' => $permits]);
            session(['permits_code' => $permitsCode]);

            $org = false;
            $wh = false;
            if (!empty($user->id_org)) {
              $userOrg = Organization::getOrg($user->id_org);

              if (!empty($userOrg)) {
                $org = $userOrg->type;
                $wh = SellerWare::getIdWare($user->email)
                  ->pluck('id_ware');
              }
            }

            //Apagando bandera de reinicio de sesión
            if ($user->reset_session == 'Y') {
              User::setResetSession($user->email, 'N');
            }

            $lowH = Profile::getLowHer('commercial');

            session([
              'user' => $user->email,
              'name' => $user->name,
              'user_dni' => $user->dni,
              'user_type' => $user->platform,
              'last_name' => $user->last_name,
              'hierarchy' => $user->hierarchy,
              'low_hierarchy' => $lowH->hierarchy,
              'profile' => $user->name_profile,
              'profile_id' => $user->id_profile,
              'org' => !empty($user->id_org) ? $user->id_org : false,
              'org_type' => $org,
              'wh' => $wh,
            ]);

            return redirect()->route('dashboard');
          } else {
            session()->flash('message_class', 'alert-danger');
            session()->flash('message_error', 'No tiene permisos para acceder.');
          }
        } else {
          session()->flash('message_class', 'alert-danger');
          session()->flash('message_error', 'Usuario o contraseña incorrecta.');
        }
      } else {
        session()->flash('message_class', 'alert-danger');
        session()->flash('message_error', 'Error en captcha.');
      }
    }

    if (!empty(session('user'))) {
      return redirect()->route('dashboard');
    }

    return view('login.access');
  }

  public function resetPassword(Request $request)
  {
    if ($request->isMethod('post')) {
      $email = $request->input('emailLoginReset');
      if (!empty($email)) {
        $user = User::getOnliyUser($email, 'W');

        if (!empty($user)) {
          //Guardar estos campo en la bd, para poder comprobar autenticidad del hash
          $dateToken = date("Y-m-d H:i:s");
          $chain = $user->name . '-' . $user->last_name . '-' . $user->date_reg . '-' . $dateToken;
          $newToken = hash_hmac('sha256', $chain, env('HASH_KEY'));

          $user->dateToken = $dateToken;
          $user->tokenPassword = $newToken;
          $user->hash = $newToken;
          $user->save();

          try {
            Mail::to($user->email)->send(new resetPassword($user));
          } catch (\Exception $e) {
            Log::error('No se pudo enviar correo de recuperar contraseña al usuario: ' . $email . ' Error: ' . $e->getMessage());

            return redirect()->route('login')
              ->with('message_class', 'alert-danger')
              ->with('message_error', 'No se pudo enviar el email de recuperar la contraseña.');
          }

          return redirect()->route('login')
            ->with('message_class', 'alert-success')
            ->with('message_error', 'Email enviado.');
        } else {
          return redirect()->route('login')
            ->with('message_class', 'alert-danger')
            ->with('message_error', 'Usuario no registrado.');
        }
      }
    }
    return redirect()->route('login');
  }

  public function changePassword(Request $request, $hash = null)
  {
    if (!empty($hash)) {
      $isHashExist = User::findHash($hash);

      if (empty($isHashExist)) {
        return redirect()->route('login')
          ->with('message_class', 'alert-danger')
          ->with('message_error', 'Enlace no válido.');
      }

      if ($request->isMethod('post')) {
        $inputs = $request->all();

        if (!empty($inputs['email']) && !empty($inputs['password'])) {
          $user = User::getOnliyUser($inputs['email'], 'W');

          if (!empty($user)) {
            $chain = $user->name . '-' . $user->last_name . '-' . $user->date_reg . '-' . $user->dateToken;

            //Hash no válido
            if ($hash != hash_hmac('sha256', $chain, env('HASH_KEY'))) {
              return redirect()->route('login')
                ->with('message_class', 'alert-danger')
                ->with('message_error', 'Token no válido.');
            }

            $b = new DateTime($user->dateToken);
            $dif = floor((time() - $b->getTimestamp()) / 60);
            $limiTime = env('RECOVER_PASSWORD_EXPIRE', 120);

            if ($dif < $limiTime) {
              $user->password = Hash::make($inputs['password']);
              $user->tokenPassword = null;
              $user->hash = null;
              $user->dateToken = null;
              $user->save();

              return redirect()->route('login')
                ->with('message_class', 'alert-success')
                ->with('message_error', 'Contraseña actualizada exitosamente.');
            } else {
              return redirect()->route('login')
                ->with('message_class', 'alert-danger')
                ->with('message_error', 'Su token para cambio de contraseña expiro.');
            }
          } else {
            return redirect()->route('login')
              ->with('message_class', 'alert-danger')
              ->with('message_error', 'No se puede actualizar contraseña del usuario.');
          }
        } else {
          session()->flash('message_class', 'alert-danger');
          session()->flash('message_error', 'Todos los datos son obligatorios.');
        }
      }

      return view('login.recover', compact('hash'));
    }
    return redirect()->route('login')
      ->with('message_class', 'alert-danger')
      ->with('message_error', 'No puede cambiar la contraseña del usuario.');
  }

  public function logout(Request $request)
  {
    session()->flush();
    return redirect()->route('login');
  }
}
