@extends('layouts.admin')

@section('customCSS')
<link href="{{ asset('css/selectize.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet"/>
@stop

@section('content')
  @include('components.messages')
  @include('components.messagesAjax')

@if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>
      {{ $error }}
    </li>
    @endforeach
  </ul>
</div>
@endif
<div class="row bg-title">
  <div class="col-lg-3 col-md-4 col-sm-4 col-12  text-sm-left text-center">
    <h4 class="page-title">
      Módulo de post-instalación de fibra
    </h4>
  </div>
  <div class="col-lg-9 col-md-8 col-sm-8 col-12 d-flex justify-content-sm-end justify-content-center">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          Ventas
        </a>
      </li>
      <li class="active">
        Alta de cliente fibra. <span>#{{$data->id}}</span>
      </li>
    </ol>
  </div>
</div>
@php
  $arr_status=['P','PA','EC','E'];
@endphp
@if( in_array($data->status,$arr_status) )
<div class="row">
  <div class="col-md-12">
    <div class="white-box">
      @include('fiber.infoClientFiber', ['data' => $data ])
      <div class="row">
        <h3 class="box-title col-md-12 p-t-10">
          Datos de la instalación
        </h3>

        @include('fiber.plan', ['plan' => $data->info_plan, 'bundle' => (!empty($data->bundle_id))?true:null])

        <div class="col-12">
          <label>
            Dirección de instalación
          </label>
          <p>
            {{$data->address_instalation}}
          </p>
        </div>
        {{-- @if(empty($data->bundle_id))
          <div class="col-md-6">
            <label>
              Msisdn
            </label>
            <p>
              {{$data->msisdn}}
            </p>
          </div>
        @endif --}}
        @if(!empty($htmlBundle))
        <div class="col-sm-12" id="blockBundleResult">
          {!!$htmlBundle!!}
        </div>
        @endif

        <h3 class="box-title col-md-12 p-t-10">
          Información Post-Instalación
        </h3>
        @if(count($questions) > 0)
          @if(count($answers) == 0)
          <div class="col-md-12">
            <form id="form_questions">
              <div class="row">
                @foreach($questions as $value)

                  <div class="col-md-6 form-group p-4">
                    <label for="question-{{ $value->id }}">{{ $value->description }}</label><br>

                    @if($value->options->count() > 0 || $value->type == 'TX')

                      @switch($value->type)

                        @case('SS')
                          <select name="question-{{ $value->id }}" id="question-{{ $value->id }}" class="form-control question-{{ $value->id }}" placeholder="Seleccione una Opción" data-type="SS">
                            <option value="">--Seleccione una Opción--</option>
                            @foreach($value->options as $option)
                              @if($option->type != 'TX')
                              <option value="{{ $option->id }}">{{ $option->description }}</option>
                              @else
                              <option value="{{ $option->id }}" class="other_text">{{ $option->description }}</option>
                              @endif
                            @endforeach
                          </select>
                          <br>
                          <input type="text" name="question-{{ $value->id }}-ot" id="question-{{ $value->id }}-ot" class="question-{{ $value->id }} other_text form-control" style="display: none;" placeholder="Escriba su respuesta">
                          @break

                        @case('TX')
                          <textarea name="question-{{ $value->id }}" id="question-{{ $value->id }}" maxlength="250" rows="1" class="form-control question-{{ $value->id }}" data-type="TX"></textarea>
                          @break

                        @case('MS')

                          @foreach($value->options as $option)
                            <div class="form-check col-md-6">
                              <input class="form-check-input question-{{ $value->id }}" type="checkbox" value="{{ $option->id }}" id="question-{{ $value->id }}-{{ $option->id }}" name="question-{{ $value->id }}-{{ $option->id }}" data-type="MS">
                              <label class="form-check-label" for="question-{{ $value->id }}-{{ $option->id }}">&nbsp;{{ $option->description }}</label>
                            </div>
                          @endforeach
                          @break

                      @endswitch
                    @else
                      <div class="alert alert-warning">Esta pregunta no posee opciones de respuesta, por favor verifique.</div>
                    @endif
                  </div>
                @endforeach
              </div>
            </form>
          </div>
          @else
          <div class="col-md-12">
            <div class="row">
              @foreach($answers as $value)

                <div class="col-md-6 form-group p-4">
                  <h5>{{ $value->q_description }}</h5><br>

                  @if(count($value->answers) == 0)
                    <label>{{ $value->question_result }}</label>
                  @endif

                  @if(count($value->answers) == 1)
                    @if($value->answers[0]->type == 'TX')
                      <label>{{ $value->answers[0]->description }} ( {{ $value->question_result }} )</label>
                    @else
                      <label>{{ $value->answers[0]->description }}</label>
                    @endif
                  @endif

                  @if(count($value->answers) > 1)
                    @foreach($value->answers as $option)
                      <div class="col-md-6">
                        &#x2022;
                        <label>{{ $option->description }}</label>
                      </div>
                    @endforeach
                  @endif
                </div>
              @endforeach
            </div>
          </div>
          @endif
        @else
        <div class="alert alert-danger">
          Actualmente no hay preguntas disponibles.
        </div>
        @endif
        @if($data->is_payment_forcer == 'Y')
        <hr>
        <div class="col-md-12">
          <div class="row justify-content-center">
            @if(!empty($Qr_svg))
            <h3 class="box-title col-md-12 p-t-10">
            Confirmación Post-instalación
            </h3>
            @endif
            <div class="col-md-8 text-center py-4" id="qr_tyc">
              {!!$Qr_svg!!}
            </div>
            <input type="hidden" name="tyc" id="tyc" value="{{compoundId(base64_encode($tyc))}}">
            <div class="col-md-12 text-right" id="block_share_tyc">
              {!!$htmlShare!!}
            </div>
          </div>
        </div>
        @endif
        <br>
        @if(count($answers) == 0)
        <div class="col-md-12 d-flex justify-content-end">
          <button class="btn btn-success waves-effect waves-light" id="btn-submit" type="button">
          Procesar
        </button>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@else
  <div class="alert alert-danger" >
    La instalación no esta disponible para el proceso de Post-instalación.
  </div>
@endif
@stop

@if( in_array($data->status,$arr_status) )

<script type="text/javascript">
  var intervalTime = null;
</script>

@section('scriptJS')

<script src="{{ asset('js/validator.js') }}">
</script>
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
<script src="{{ asset('js/selectize.js')}}">
</script>

<script as="script" rel="preload">

  var questions = @php echo json_encode($questions) @endphp;
  var install_id = {{$data->id}};
  var idIntervaloActive = 0;

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  $(function () {

    @if($data->is_payment_forcer == 'Y')
      @if($type_content == 'QR')
        $('#qr_tyc svg').attr('width', '300px');
        $('#qr_tyc svg').attr('height', '300px');
      @endif

      @if($type_content == 'LOADING')
        //cada 20 seg hacemos la verificacion
        idIntervaloActive  = setInterval(checkingActiveFiber, 20000);
        console.log('Inicio un proceso de peticion');
      @endif
    @endif
  });

  $('select').on('change', function(){
    if($(this).data('type') == 'SS'){

      if(this.selectedOptions[0].classList.contains('other_text')){
        $('#' + this.id + '-ot').val('');
        $('#' + this.id + '-ot').show();
      }else{
        $('#' + this.id + '-ot').hide();
      }
    }
  })

  $('#btn-submit').on('click', function(){
    if (!validate_questions()) {
      //showMessageAjax('alert-warning', 'Por Favor, Complete las Preguntas!');
      swal({
            title: "Acción Requerida",
            text: "Por Favor, Complete el area 'información post-instalación' para poder continuar.",
            icon: "warning",
            button: {
              text: "OK"
            }
          });
      return 0;
    }

    @if(!empty($htmlBundle))
      if($('#process_fail').data('continue') == "FAIL"){
        RefresMsg('FAIL');
        return 0;
      }
    @endif

    $('.loading-ajax').fadeIn();
    let closePostinstall=false;
    @if($data->is_payment_forcer == 'Y')
      let isAceptClient = verifyQrClient();
      isAceptClient.then(function(promesa_acept_tyc) {
        if(!promesa_acept_tyc['success']){
          $('.loading-ajax').fadeOut();
          swal({
            title: promesa_acept_tyc['title'],
            text: promesa_acept_tyc['msg'],
            icon: promesa_acept_tyc['icon']
          });
          return false;
        }else{
          //Registro el tyc de post-instalacion que se acepto
          let isAceptTyc = registerAceptacionTyc();
          isAceptTyc.then(function(promesa_save_tyc) {
            if(!promesa_save_tyc['success']){
              $('.loading-ajax').fadeOut();
              swal({
                title: promesa_save_tyc['title'],
                text: promesa_save_tyc['msg'],
                icon: promesa_save_tyc['icon']
              });
              return false;
            }else{
              //Se registra las preguntas que se cargaron
              registerPostInstall();
            }
          });
        }
      });
    @else
       closePostinstall = true;
    @endif

    if(closePostinstall){
      registerPostInstall();
    }
  });

  @if($data->is_payment_forcer == 'Y')

    function verifyQrClient(){
      $('.loading-ajax').show();
      return new Promise((resolve, reject) => {
        doPostAjax(
          '{{ route('sellerFiber.verifyQr') }}',
          function(res){
            //$('.loading-ajax').fadeOut();
            return resolve(res);
          },
          {
            dni: '{{$data->clients_dni}}',
            tyc: $('#tyc').val()
          },
          $('meta[name="csrf-token"]').attr('content')
        );
      });
    }

    function registerAceptacionTyc(){
      return new Promise((resolve, reject) => {
        doPostAjax(
          '{{ route('sellerFiber.setQrForce') }}',
          function(res){
            return resolve(res);
          },
          {
            id: {{$data->id}},
            tyc: $('#tyc').val()
          },
          $('meta[name="csrf-token"]').attr('content')
        );
      });
    }

    function checkingActiveFiber(){
      doPostAjax(
        "{{ route('sellerFiber.checkingActiveFiber') }}",
        function(res){
          var today = new Date();
          var now = today.toLocaleString();
          console.log('RESPUESTA '+res.code +' - '+now);

          if(res.code == "ACTIVE"){
            $('#qr_tyc').html(res.Qr_svg);
            $('#block_share_tyc').html(res.htmlShare);
            clearInterval(idIntervaloActive);
          }else{
            if(res.message == 'TOKEN_EXPIRED'){
                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
            }else{
              /*@handheld
                swal({
                  title: res.title,
                  text: res.msg,
                  icon: res.icon
                });
              @elsehandheld
                var tipy="alert-danger";
                showMessageAjax(tipy, res.msg);
              @endhandheld*/
            }
          }
        },
        {
          install_id: '{{$data->id}}'
        },
        $('meta[name="csrf-token"]').attr('content')
      );

  }

  @endif

  function registerPostInstall(){
    let form = new FormData($('#form_questions')[0]);
    form.append('install_id', install_id);

    $.ajax({
      url: '{{ route('sellerFiber.doSurvey') }}',
      type: 'post',
      data: form,
      processData: false,
      contentType: false,
      success: (res) => {

        if(!res.error){

          $('.loading-ajax').fadeOut();
          swal({
            title: "Exito",
            text: "Información de post-instalación ha sido procesada exitosamente.",
            icon: "success",
            button: {
              text: "OK"
            }
          })
          .then((value) => {
            $('.loading-ajax').fadeIn();
            window.location.href = '{{ route('dashboard') }}';
          });

        }else{
          $('.loading-ajax').fadeOut();
          if(res.message == 'TOKEN_EXPIRED'){
              showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
          }else{
            showMessageAjax('alert-danger',res.message);
          }
        }
      },
      error: (err) => {
        let message = 'Ocurrió un error al intentar guardar el Cambio.'
        if (err.responseJSON.message) {
          message = err.responseJSON.message
        }
        $('.loading-ajax').fadeOut();
        showMessageAjax('alert-danger',message);
      }
    });
  }

  function validate_questions() {

      let valid = true;

      $(questions).each(function(key, value){
          if(value.type == 'SS'){
            let single = $('select' + '.question-' + value.id);
            if (single.val() == '') {
              valid = false; return;

            }else{
              if (single[0].selectedOptions[0].classList.contains('other_text')) {
                if ($('#question-' + value.id + '-ot').val().trim() == '') {
                  valid = false; return;
                }
              }
            }
          }

          if(value.type == 'TX'){
            if ($('textarea' + '.question-' + value.id).val().trim() == '') { valid = false; return; }
          }

          if(value.type == 'MS'){
            if ($('input' + '.question-' + value.id).length) {

              var valid_check = false;
              $('input' + '.question-' + value.id).each(function(key2, value2){

                if ($(value2).prop('checked')) { valid_check = true; return; }

              });

              valid = valid_check;
            }
          }
      });
      return valid;
    }
</script>

@stop

@endif
