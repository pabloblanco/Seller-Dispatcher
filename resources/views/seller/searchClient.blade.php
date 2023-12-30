@section('customCSS')
<link href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
<link href="{{ asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet"/>
@stop
<div class="white-box">
  @if(hasPermit('RCL-DSE'))
  <div class="text-right p-b-10">
    <a class="btn btn-success waves-effect waves-light" data-target="#register-modal" data-toggle="modal" href="#">
      Registrar Prospecto
    </a>
  </div>
  @endif
  <h3 class="box-title">
    Consultar Prospecto
  </h3>
  <form class="form-horizontal" data-toggle="validator" id="Salesclientform">
    {{ csrf_field() }}
    <div class="form-group">
      <label class="col-md-12">
        Buscar
      </label>
      <div class="col-md-12">
        <select class="form-control" id="buscar" name="buscar" placeholder="Escribe el Nombre o DN del Prospecto">
        </select>
        <input id="client" name="client" type="hidden" value="">
        </input>
      </div>
    </div>
    <div id="showClient">
    </div>
  </form>
</div>
<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="register-modal" role="dialog" style="display: none;" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Registrar prospecto.
        </h4>
      </div>
      <div class="modal-body">
        @include('client.formRegisterProspect')
      </div>
      <div class="modal-footer">
        <button class="btn btn-default waves-effect" data-dismiss="modal" type="button">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>
@section('scriptJS')
{{--@if(!empty($lock) && $lock->is_locked == 'N')--}}
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}">
</script>
<!-- typehead TextBox Search -->
<script src="{{ asset('plugins/bower_components/typeahead.js-master/dist/typeahead.bundle.min.js') }}">
</script>
<script src="{{ asset('js/validator.js') }}">
</script>
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
<script src="{{ asset('js/selectize.js')}}">
</script>
<script type="text/javascript">
  $(function () {
      let client = function(res){
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
              "{{route('seller.findClient')}}",
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
            '{{ $urlRouteViewClient }}',
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
      });
    });
</script>
{{--@endif--}}
@stop
