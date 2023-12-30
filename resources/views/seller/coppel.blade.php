@extends('layouts.admin')

@section('customCSS')
  <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css') }}">
  <link href="{{ asset('css/selectize.css') }}" rel="stylesheet">
  <link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet">
@stop

@section('content')
    @include('components.messages')
    @include('components.messagesAjax')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"> Prueba Coppel </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Prueba Coppel</a></li>
            </ol>
        </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="white-box">
          <h1>Probando flujo de coppel</h1>

          <div id="cpplPay"></div>
        </div>
      </div>
    </div>
@stop

@section('scriptJS')
  <script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
  <!-- typehead TextBox Search -->
  <script src="{{ asset('plugins/bower_components/typeahead.js-master/dist/typeahead.bundle.min.js') }}"></script>
  {{-- https://1000hz.github.io/bootstrap-validator/#validator-usage --}}
  <script src="{{ asset('js/validator.js') }}"></script>
  <script src="{{ asset('js/sweetalert.min.js') }}"></script>
  {{-- <script src="{{ asset('plugins/bower_components/dropify/dist/js/dropify.min.js') }}"></script> --}}
  <script src="{{ asset('js/selectize.js')}}"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_KEY') }}&libraries=places"></script>

  <script src="https://sdk.coppelpay.com/coppelpaysdk/CoppelPay.js"></script>
  <script src="https://mpsnare.iesnare.com/snare.js"></script>

  <script type="text/javascript">
    $(function () {
      //CPLPY.init('4e1c32884380a33bfd4989d4637aae86');
      //CPLPY.sandbox = true;
      //CPLPY.open();

      //coppelpay1@yopmail.com  coppel123

      function isCoppelReady(){
        var blackbox = CPLPY.blackbox; 
        var token = CPLPY.token;
      }

      function isCoppelCancel(){
        console.log('pago cancelado');
      }

      /*let client = function(res){
        $('.loading-ajax').fadeOut();

        if(!res.error){
          $('#client').val(res.dni);
          $("#showClient").html(res.html);
        }else{
          if(res.message == 'TOKEN_EXPIRED'){
              showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
          }else{
              showMessageAjax('alert-danger', 'No se consiguio el prospecto.');
          }
        }
      }

      $('#buscar').selectize({
        valueField: 'dni',
        searchField: ['name', 'last_name', 'info'],
        labelField: 'info',
        render: {
            option: function(item, escape) {
                return '<p>'+escape(item.info)+'</p>';
            }
        },
        load: function(query, callback) {
            if (!query.length) return callback();

            doPostAjax(
              '{{ route('seller.findClient') }}',
              function(res){
                  if(!res.error){
                      callback(res);
                  }else{
                      callback();
                  }
              },
              {search: query},
              $('meta[name="csrf-token"]').attr('content')
            );
        }
      });

      $('#buscar').on('change', function(e){
        let dni = $(this).val();

        if(dni){
          $('.loading-ajax').show();
          doPostAjax(
            '{{ route('seller.showClientN') }}',
            client,
            {dni: dni, _token: '{{ csrf_token() }}'}
          );
        }
      });

      let now = new Date(),
          start = new Date(new Date().setFullYear(now.getFullYear() - 18));

      $('#birthday').datepicker({
          language: 'es',
          autoclose: true,
          format: 'dd-mm-yyyy',
          endDate: start
      });

      $('#nextC').datepicker({
          language: 'es',
          autoclose: true,
          format: 'dd-mm-yyyy',
          startDate: now
      });

      let register = function(res){
        $('.loading-ajax').fadeOut();

        $('#register-modal').modal('hide');

        if(!res.error){
          $('.loading-ajax').show();

          doPostAjax(
            '{{ route('seller.showClientN') }}',
            client,
            {dni: res.dni, _token: '{{ csrf_token() }}'}
          );
        }else{
          if(res.message == 'TOKEN_EXPIRED'){
              showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
          }else{
              if(res.message){
                showMessageAjax('alert-danger', res.message);
              }else{
                showMessageAjax('alert-danger', 'No se pudo registrar el prospecto.');
              }
          }
        }
      }

      $('#registerclientform button[type=submit]').unbind('click');

      $('#registerclientform').validator().on('submit', function(e){
        if(!e.isDefaultPrevented()){
          e.preventDefault();

          $('.loading-ajax').show();

          doPostAjax(
            '{{ route('client.registerAjax') }}',
            register,
            $(this).serialize()
          );
        }
      });

      $('#rs-content').hide();

      $('.social').on('click', function(e){
        let s = $(this).val();
        $('#campaign option').attr("selected",false);
        if(s == 'S'){
            $('#campaign option[value="Campaña al Whatsapp"]').attr("selected",true);
            $('#rs-content').show();
        }else{
            $('#rs-content').hide();
        }
      });*/
    });
  </script>
@stop