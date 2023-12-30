<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException){
            session()->flash('msg_type', 'error');
            session()->flash('flash_msg', 'Tu sesión ha vencido, intenta de nuevo.');

            if($request->ajax()){
                return response()->json([
                            'error' => true,
                            'message' => 'TOKEN_EXPIRED'
                        ]);
            }else{
                return redirect()->back();
            }
        }

        return parent::render($request, $e);
    }
}
