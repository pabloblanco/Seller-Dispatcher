<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" type="image/png" sizes="16x16" href="{{asset('plugins/images/favicon.png')}}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta content="{{ asset('/') }}" name="base-url"/>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <meta http-equiv="Last-Modified" content="0">

        <title>{{ env('APP_NAME') }}</title>
        <!-- Bootstrap Core CSS -->
        <link rel="stylesheet" href="{{ asset('bootstrap/dist/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-extension/css/bootstrap-extension.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=2.1">
        <link rel="stylesheet" href="{{ asset('css/colors/default.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v=2.3">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    @yield('customCSS')
  </head>
  <body class="fix-sidebar">
    <!-- Preloader -->
    <div class="preloader">
      <svg class="circular" viewbox="25 25 50 50">
        <circle class="path" cx="50" cy="50" fill="none" r="20" stroke-miterlimit="10" stroke-width="2">
        </circle>
      </svg>
    </div>
    <div id="wrapper">
        @include('components.topNavigation')
        @include('components.leftNavigation')
      <div id="page-wrapper">
        <!--loading de content-->
        <div class="loading-ajax">
          <div class="content">
            <i class="fa fa-spin fa-circle-o-notch">
            </i>
          </div>
        </div>
        <!--Fin loading de content-->
        <div class="container-fluid">
          @yield('content')
        </div>
        <footer class="footer text-center">
          {{date('Y')}} © Netwey todos los derechos reservados
          <br/>
          Desarrollado por
          <a href="https://gdalab.com/" target="_blank">
            Gdalab
          </a>
        </footer>
      </div>
    </div>
    <!-- jQuery -->
    <script src="{{ asset('plugins/bower_components/jquery/dist/jquery.min.js') }}">
    </script>
    <script src="{{ asset('bootstrap/dist/js/tether.min.js') }}">
    </script>
    <script src="{{ asset('bootstrap/dist/js/bootstrap.min.js') }}">
    </script>
    <script src="{{ asset('plugins/bower_components/bootstrap-extension/js/bootstrap-extension.min.js') }}">
    </script>
    <script src="{{ asset('plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js') }}">
    </script>
    <script src="{{ asset('js/jquery.slimscroll.js') }}">
    </script>
    <script src="{{ asset('js/waves.js') }}">
    </script>
    <script src="{{ asset('js/custom.js') }}?v=2.1">
    </script>
    <script src="{{ asset('plugins/bower_components/styleswitcher/jQuery.style.switcher.js') }}">
    </script>
    <script src="{{ asset('js/global_custom.js?v=2.0') }}">
    </script>

    @yield('scriptJS')
    @yield('fromPortJS')
    @yield('scriptJS2')

    <script type="text/javascript">
      $(function () {
        @if(showMenu(['RMV-DSE']))
            var url = "{{ route('coordination.reception') }}";
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                async: true,
                url: "{{route('coordination.receptionNoti')}}",
                method: 'POST',
                dataType: 'json',
                success: function (res) {
                    if(res.success){
                        $('#numberActivations').text(res.activations);
                        if(res.data.length || res.data_inst.length){
                            var html = '';

                            res.data.forEach(function(ele){
                                html += '<a href="'+url+'/'+ele.email+'">';
                                html += '<h5><i class="ti-check-box"></i>Entrega de efectivo</h5>';
                                html += '<p>'+ele.name+' '+ele.last_name+' $'+ele.amount+'</p>';
                                html += '</a>';
                            });

                            res.data_inst.forEach(function(ele){
                                html += '<a href="'+url+'/'+ele.email+'">';
                                html += '<h5><i class="ti-check-box"></i>Entrega de efectivo</h5>';
                                html += '<h5>(venta a cuotas)</h5>';
                                html += '<p>'+ele.name+' '+ele.last_name+' $'+ele.amount+'</p>';
                                html += '</a>';
                            });


                            $('#notification-content').html(html);
                            $('#icon-notification').attr('hidden', null);
                            $('#icon-rf .waves-effect').removeClass('h-it').addClass('v-it');
                        }else{
                            $('#icon-notification').attr('hidden', true);
                            $('#icon-rf .waves-effect').removeClass('v-it').addClass('h-it');
                        }
                    }else{
                        showMessageAjax('alert-danger', res.msg);
                    }
                },
                error: function (res) {
                    showMessageAjax('alert-danger', 'Fallo la consulta de notificaicones.');
                }
            });
        @endif

        function queryInstallments(){
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                async: true,
                url: "{{route('installments.checkRequest')}}",
                method: 'POST',
                dataType: 'json',
                success: function (res) {
                    if(res.success){
                        $('#n-req').text(res.count);
                        $('#e-pay').text(res.count_pending_S);

                        if(res.count > 0 || res.count_pending_S){
                            $('#sale-ins').removeClass('h-it').addClass('v-it');
                        }else{
                            $('#sale-ins').removeClass('v-it').addClass('h-it');
                        }
                    }else{
                        if(res.message == 'TOKEN_EXPIRED'){
                            showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                        }
                    }
                },
                error: function (res) {
                    showMessageAjax('alert-danger', 'Fallo la consulta de notificaicones cuotas.');
                }
            });
        }

        queryInstallments();

        setInterval(queryInstallments, 1000 * 60);

        @if(session('user_type') == 'vendor' && hasPermit('NMD-DSE'))
            function requestDeny(){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    async: true,
                    url: "{{route('seller.cashDeliveryDeny')}}",
                    method: 'POST',
                    dataType: 'json',
                    success: function (res) {
                        if(res.success){
                            if(res.show){
                                $('#icon-dn').removeClass('h-it').addClass('v-it');
                            }else{
                                $('#icon-dn').removeClass('v-it').addClass('h-it');
                            }
                        }else{
                            if(res.message == 'TOKEN_EXPIRED'){
                                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                            }
                        }
                    },
                    error: function (res) {
                        showMessageAjax('alert-danger', 'Fallo la consulta de notificación de efectivo.');
                    }
                });
            }

            requestDeny();

            setInterval(requestDeny, 5000 * 60);
        @endif
    });
    </script>
  </body>
</html>
