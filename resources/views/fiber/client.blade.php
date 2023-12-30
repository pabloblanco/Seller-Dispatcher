@extends('layouts.ajax')

@section('ajax')
    @if($client->status == false)
<div class="alert alert-danger">
  <ul>
    <li>
      {{ $client->message }}
    </li>
  </ul>
</div>
@else
<div class="row">
  <div class="col-sm-12">
    @include('seller.InfoClient', ['client' => $client ])
  </div>

  <div class="col-12 alert label-red text-white">
    <p style="font-size: 15px;">
      Por favor, asegurarse que email y teléfono que se muestran son correctos, ya que
      <b>
        se enviara un sms y un email
      </b>
      al cliente luego de registrar la solicitud de instalación.
    </p>
  </div>
  <div class="col-md-12 m-b-20">
    <button class="btn btn-success waves-effect waves-light m-r-10" {{--data-target="#edit-modal" data-toggle="modal"--}} type="button" id="editClient">
      Editar datos del cliente
    </button>
  </div>
    @include('fiber.verify_Phone', ['isVerifyPhone'=> false])
  <div class="col-md-12">
    <div class="form-group" id="blockState">
      <label class="col-md-12">
        Estado
      </label>
      @if(count($states))
      <select class="form-control" id="stateF" name="stateF" placeholder="Seleccione un Estado" required="">
        <option value="">
          Seleccione una estado
        </option>
        @foreach($states as $state)
        <option value="{{ $state->localy_state_id }}">
          {{ $state->location_state }}
        </option>
        @endforeach
      </select>
      <div class="help-block with-errors">
      </div>
      @else
      <div class="alert alert-danger">
        <p>
          Falló consulta de estados por favor actualice la página.
        </p>
      </div>
      @endif
    </div>
    <div class="form-group d-none" id="blockCity">
    </div>
    <div class="form-group d-none" id="blockOlt">
    </div>
    <div class="form-group d-none" id="blockNodes">
    </div>
    <div class="form-group d-none" id="blockMap">
      <h3 class="box-title">
        Dirección de instalación
      </h3>
      <div class="form-group">
        <div class="map-container" id="map" style="height: 60vh;min-height: 320px; padding-top: 20px;">
          <div id="map-content" style="width: 100%;height: 100%;">
          </div>
        </div>
      </div>
      <div class="form-group row justify-content-center align-items-center">
        <span class="alert alert-danger col-12 pb-3">
          <strong>
            Nota importante:
          </strong>
          <br>
          * Asegurese que el puntero esté situado en la dirección correcta. 📌
          <br>
          * Selecciona la forma en que prefieres verificar cobertura de fibra. 👇
        </span>
        <input data-off="Ingresar<br>dirección" data-on="Agendamiento<br>desde ubicación" data-toggle="toggle" id="typeAddress" type="checkbox"/>
      </div>
      {{--
      <div class="form-group row justify-content-center align-items-center text-center" id="blockTypeAddress">
        <h4 class="col-12 box-title text-left">
          Seleccione el tipo de ingreso de dirección
        </h4>
        <label class="col-5">
          Agendamiento desde ubicación
        </label>
        <label class="custom-switch">
          //checked
          <input id="typeAddress" type="checkbox"/>
          <span class="slider round">
          </span>
        </label>
        <label class="col-5">
          Ingresar dirección
        </label>
      </div>
      --}}
    </div>
    <div class="form-group d-none" id="blockTipeAddress">
    </div>
    <div class="form-group d-none" id="blockGPS">
      <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
        <label>
          Latitud:
        </label>
        <input class="form-control lat-map" name="lat" type="text"/>
        <div class="help-block with-errors">
        </div>
      </div>
      <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
        <label>
          Longitud:
        </label>
        <input class="form-control lon-map" name="lon" type="text"/>
        <div class="help-block with-errors">
        </div>
      </div>
      <div class="col-md-4 col-12">
        <button class="btn btn-success waves-effect waves-light m-r-10" data-btn="map" id="btnGeo" name="btnMap" style="margin-top: 26px;" type="button">
          Geolocalizar + Validar Servicialidad
        </button>
        <button class="btn btn-success waves-effect waves-light m-r-10" data-btn="map" id="validGeo" name="btnMap" style="margin-top: 26px;" type="button">
          Validar Servicialidad
        </button>
      </div>
    </div>
    <div class="d-none" id="blockAddressExtra">
      <input class="form-control input-coverage" id="lat_OK" name="lat_OK" type="hidden"/>
      <input class="form-control input-coverage" id="lng_OK" name="lng_OK" type="hidden"/>
      <div class="form-group">
        <label class="col-md-12">
          Alcaldia/municipio
        </label>
        <input class="form-control" id="muni" name="muni" type="text"/>
        <div class="help-block with-errors">
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-12">
          Colonia
        </label>
        <input class="form-control" id="colony" name="colony" type="text"/>
        <div class="help-block with-errors">
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-12">
          Calle
        </label>
        <input class="form-control" id="route" name="route" type="text"/>
        <div class="help-block with-errors">
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-12">
          Número de casa
        </label>
        <input class="form-control" id="numberhouse" name="numberhouse" type="text"/>
        <div class="help-block with-errors">
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-12">
          Referencia *
        </label>
        <input class="form-control" id="reference" name="reference" required="" type="text"/>
        <div class="help-block with-errors">
        </div>
      </div>
      <div class="form-group" id="blockPlan">
      </div>
    </div>
    {{--
    <div class="form-group">
      <input class="form-control" id="address" name="address" placeholder="Escribe la dirección de instalación" required="" type="text" value="{{ !empty($client->address) ? $client->address : '' }}">
        <div class="help-block with-errors">
        </div>
      </input>
    </div>
    --}}

    {{--@if(!empty($isMig) && hasPermit('MIG-FIB'))
    <div class="col-md-12">
      <h3 class="box-title">
        ¿Es Migración?
      </h3>
      <label class="custom-control custom-radio">
        <input checked="" class="custom-control-input" name="migrationcheck" type="radio" value="N">
          <span class="custom-control-indicator">
          </span>
          <span class="custom-control-description">
            No
          </span>
        </input>
      </label>
      <label class="custom-control custom-radio">
        <input class="custom-control-input" name="migrationcheck" type="radio" value="Y">
          <span class="custom-control-indicator">
          </span>
          <span class="custom-control-description">
            Si
          </span>
        </input>
      </label>
    </div>
    @endif--}}
  </div>
  <div class="col-sm-12" id="plan-content">
  </div>

  <div class="col-sm-12 d-none" id="blockValidImei">
    <div class="alert alert-primary alert-dismissable">
      <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
        ×
      </button>
      <span>
        <h4>
          <strong>
            Atención verificación IMEI:
          </strong>
        </h4>
          &#128241; El combo a vender contiene una SimCard que sera ingresado en un equipo celular del cliente:
      </span>
      <br>
      <li>
        <strong>1 -</strong> Debes solicitar el IMEI del equipo celular del cliente para verificar compatibilidad con la red de Netwey.
      </li>
      <li>
        <strong>2 -</strong> El IMEI suministrado para verificar compatibilidad recomendamos debe ser el equipo celular que se tiene pensado utilizar la Simcard de Netwey cuando el instalador lo visite.
      </li>
      <li>
        <strong>3 -</strong> Si el equipo celular del cliente no es compatible con la red de Netwey lo invitamos que intente con otro equipo celular o de lo contrario lamentamos informar que no se podra vender el combo seleccionado.
      </li>

    </div>
    @include('seller.validImei', ['saleFiber' => true])
  </div>

</div>
{{-- Modal de edición --}}
@include('fiber.modalEditClient', ['client' => $client ])

@endif
<script type="text/javascript">
  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

  $(function () {

    //Inicializo los campos de cliente cuando se trate de una verificacion de telefono
    //
    var phoneVerify = document.getElementById('phoneOK');
    // si existe el campo phoneOk tambien debe estar el input donde informamos el telefono del cliente
    if (typeof phoneVerify !== 'undefined') {
       @php
       $textoDN = (isset($client->phone_home) && !empty($client->phone_home))? $client->phone_home : "S/N";
       @endphp
      $("#phoneOK").text({!!$textoDN!!});
      $("#msisdn-contact").val({!!$textoDN!!});
      @if($client->isVerifyPhone == "VERIFIED")
        changerModeVerify('VALID');
      @else
        changerModeVerify('INPUT');
      @endif
      //Podemos ocultar el stado, se lista luego que se verifique
      $('#blockState').attr('hidden', true);
    }

    @if($client->status && $cantState)
      @desktop
        $('#stateF').selectize
      @enddesktop
    @endif
  });

  $('#editClient').on('click', function(e){
    $('#edit-modal').modal('show');
  });

  $('#stateF').change(function (e) {
    e.preventDefault();
    ResetViewFiberCite('states');
    if($('#stateF').val()!==''){

      $('.loading-ajax').show();
      $.ajax({
          headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
          },
          url: '{{ route('sellerFiber.getCitys') }}',
          type: 'POST',
          dataType: 'json',
          data: {
            stateId: $(this).val()
          },
          error: function() {
            $('.loading-ajax').fadeOut();
            showMessageAjax('alert-danger', 'Ocurrio un error al consultar ciudades.');
          },
          success: function(data) {
            if (!data.error) {
              $('.loading-ajax').fadeOut();
              if (data.success) {
                $('#blockCity').removeClass('d-none');
                $('#blockCity').html(data.msg);
              }
            } else {
              $('.loading-ajax').fadeOut();
              if (data.message == 'TOKEN_EXPIRED') {
                showMessageAjax('alert-danger', 'Su session a expirado, por favor actualice la página.');
              } else {
                showMessageAjax('alert-danger', data.message);
              }
            }
          }
        });
    }else{
      ResetViewFiberCite('states');
    }
  });

  $('#typeAddress').on('change',function(){
    $('#blockAddressExtra').addClass('d-none');
    if($(this).is(':checked')){

      $('#blockTipeAddress').removeClass('d-none');
      $('#blockGPS').addClass('d-none');
    } else {

      $('#blockGPS').removeClass('d-none');
      $('#blockTipeAddress').addClass('d-none');
    }
  });

</script>
@stop
