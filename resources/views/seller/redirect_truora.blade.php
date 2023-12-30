<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" type="image/png" sizes="16x16" href="plugins/images/favicon.png">
        <meta http-equiv="Expires" content="0">
        <meta http-equiv="Last-Modified" content="0">
        <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
        <meta http-equiv="Pragma" content="no-cache">

        <title>Validaci&oacute;n de identidad con Truora</title>

        <link rel="stylesheet" href="{{ asset('bootstrap/dist/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-extension/css/bootstrap-extension.css') }}">
    </head>

    <body>
        <section id="wrapper" class="login-register">
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-warning alert-dismissable">
                Los datos para la verificación se estan procesando, por favor espera...
              </div>
            </div>
          </div>
        </section>

        <!-- jQuery -->
        <script src="{{ asset('plugins/bower_components/jquery/dist/jquery.min.js') }}"></script>
    </body>
</html>