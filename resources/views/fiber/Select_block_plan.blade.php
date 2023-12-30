<div class="col-md-12">
  <h3 class="box-title">
    Plan
  </h3>
  <div class="row justify-content-center align-items-center">
    @php
      $existForce = false;
      $existSuscrip = false;
      $existBundle = false;
    @endphp
    @if(count($packsN) || count($packsF) || count($packsSS) || count($packsCS) || count($packsBun))
      @php
        if(!empty($htmlDocument)){
          $existForce = true;
        }
        if(count($packsCS)){
          $existSuscrip = true;
        }

        if(count($packsBun)){
          $existBundle = true;
        }
      @endphp
    <div class="@if($existForce || $existSuscrip || $existBundle) col-md-6 @else d-none @endif">
      <div class="form-group row justify-content-center align-items-center">
        <span class="alert alert-danger col-12 pb-3">
          * Selecciona el tipo de plan que prefiere el cliente! 👇
        </span>
        <div class="container">
          <div class="row justify-content-center">
          @if($existSuscrip)
            {{--
            <div class="col-lg-8 col-md-9 col-12 text-center py-2">
              <input data-off="Ver planes<br>con suscripción" data-on="Ver planes<br>recarga manual" data-toggle="toggle" data-width="150" data-height="55" id="typePlan_suscrip" type="checkbox" title="Ver planes con pago recurrente"/>
            </div>
            --}}
            <div class="col-lg-8 col-md-9 col-12 text-left py-2">
              <input id="typePlan_suscrip" type="checkbox" class="custom-checkbox" title="Ver planes con pago recurrente"/>
              <span>
                Ver planes con Pago recurrente
              </span>
            </div>
          @endif

          @if($existForce)
            {{--
            <div class="col-lg-8 col-md-9 col-12 text-center py-2">
              <input data-off="Ver planes<br>contrato 12 meses" data-on="Ver planes<br>tradicionales" data-toggle="toggle" data-width="150" data-height="55" id="typePlan" type="checkbox" title="Cambiar el listado de planes con contrato o sin el"/>
            </div>
            --}}
            <div class="col-lg-8 col-md-9 col-12 text-left py-2">
              <input id="typePlan" type="checkbox" class="custom-checkbox" title="Ver planes con contrato 12 meses"/>
              <span>
                Ver planes con contrato 12 meses
              </span>
            </div>
          @endif

          @if($existBundle)
            <div class="col-lg-8 col-md-9 col-12 text-left py-2">
              <input id="typePlan_bundle" type="checkbox" class="custom-checkbox" title="Ver planes combo"/>
              <span>
                Ver planes combo (Fibra + Móvil)
              </span>
            </div>
          @endif
          </div>
        </div>
      </div>
    </div>

    <div class="@if($existForce || $existSuscrip || $existBundle) col-md-6 @else col-md-12 @endif" id="blockSelectPlan">
      @php
      //Inicializacion del titulo que se debe mostrar basado en los tipos de planes disponibles
      $tex = "Planes";
      if($existForce){
        $tex .= " sin contrato";
      }

      if($existSuscrip){
        $tex .= ", recargas manuales";
      }

      if($existBundle){
        $tex .= " y sin combo:";
      }
      @endphp
      <label id="labelTypePlan">{{$tex}}</label>
      <div id="item_select_packs"></div>
      <div class="help-block with-errors">
      </div>
    </div>
    @if($existSuscrip)
    <div class="col-md-12" hidden id="notify_suscription">
      <div class="alert alert-primary alert-dismissable">
        <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
          ×
        </button>
        <span>
          <h4>
            <strong>
              Atención pago recurrente:
            </strong>
          </h4>
            &#128179; Recuerda informarle al cliente sobre el pago recurrente a su tarjeta:
        </span>
        <br>
        <li>
          <strong>1 -</strong> El cobro del servicio se realizará el día de la instalación.
        </li>
        <li>
          <strong>2 -</strong> El registro de la tarjeta lo hace el cliente a través de un enlace de pago que el instalador le proporcionará.
        </li>
        <li>
          <strong>3 -</strong> El servicio del cliente se activara luego que se confirme el pago.
        </li>
        <li>
          <strong>4 -</strong> Si el pago no procede, el cliente debera pagar en efectivo al instalador y <i><u>PIERDE EL BENEFICIO</u></i> del pago recurrente
          {{--OJO: https://netwey-contract.s3.amazonaws.com/contracts/5ba42308e092820230209172526.pdf--}}
        </li>
      </div>
    </div>
    @endif
    @else
    <div class="alert alert-danger">
      <p>
        Falló el listado de planes o no hay planes activos para la OLT's seleccionada que se puedan mostrar.
      </p>
    </div>
    @endif

  </div>
</div>
<script type="text/javascript">
  if(sessionStorage.getItem('firstLoad')!='undefined'){
    sessionStorage.setItem('firstLoad', true);
  }
  $(function () {
    //$('#plan').selectize();
    @if(!empty($htmlDocument))
     // $('#typePlan').bootstrapToggle();
      setTimeout(() => {
        //$('#typePlan').prop('checked', false).trigger('change');
        $('#typePlan').prop('checked', false);//.change();
        sessionStorage.setItem('firstLoad', false);
      }, 1000);
    @endif

    @if($existSuscrip)
      //$('#typePlan_suscrip').bootstrapToggle();
      setTimeout(() => {
        //$('#typePlan').prop('checked', false).trigger('change');
        $('#typePlan_suscrip').prop('checked', false);//.change();
        sessionStorage.setItem('firstLoad', false);
      }, 1000);
    @endif

    $('#notify_suscription').attr('hidden', true);
        setTimeout(() => {
          listPlan("packs");
    }, 1001);

    @if($existBundle)
      setTimeout(() => {
        //$('#typePlan_bundle').prop('checked', false).trigger('change');
        $('#typePlan_bundle').prop('checked', false);//.change();
        sessionStorage.setItem('firstLoad', false);
      }, 1000);

      let validImei = function(res){

        $('.loading-ajax').fadeOut();
        $("#is-band-te").val(false);
        //Aca se valida el imei y no se deberia setear
        $('#imei_copy').val('');
        $('#imei_brand').val('');
        $('#imei_model').val('');
        $('#alert-comp').removeClass('alert-danger').removeClass('alert-success').removeClass('alert-warning');
        $('#alert-comp').removeClass('text-white').removeClass('text-dark');

        if(!res.error){
          //console.log(res.data);
          if(res.data.band28.toUpperCase() == 'NO'){

            $('#alert-comp').addClass('alert-warning');
            $('#alert-comp').addClass('text-dark');
            $('#alert-comp').text('Equipo no es compatible con Banda 28');
            $("#is-band-te").val('N');

          }else{
            if(res.data.volteCapable.toUpperCase() == 'SI' &&
              res.data.blocked.toUpperCase() == "NO"){

              $('#alert-comp').addClass('alert-success');
              $('#alert-comp').addClass('text-white');
              $('#imei_copy').val($('#imei').val());
              $('#imei_brand').val(res.data.brand);
              $('#imei_model').val(res.data.model);
              $("#is-band-te").val('Y');
              $('#alert-comp').text("Equipo: '"+res.data.brand+" "+res.data.model+"' es compatible con la red VoLTE");
             // VerListPlans(true, true);
              $('#installer-content').attr('hidden', null);
            }else{
              $('#alert-comp').addClass('alert-warning');
              $('#alert-comp').addClass('text-dark');
              $('#alert-comp').text('Equipo no compatible con la red VoLTE o se encuentra bloqueado!');
            }
            /*if(res.data.volteCapable == 'no'){
                 $('#alert-comp').text('Equipo compatible con VozApp');
             }*/
          }
          $('#alert-comp').show();

         }else{
           if(res.message == 'TOKEN_EXPIRED'){
               showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
           }else{
             $('#alert-comp').addClass('alert-danger');
             $('#alert-comp').addClass('text-white');
             $('#alert-comp').text('Equipo no compatible o intenta nuevamente');
             $('#alert-comp').show();
           }
         }
      }

      $('#valid-imei').on('click', function(e){
        let imei = $('#imei').val();
        $('#alert-comp').hide();
        $('#imei-error').text('');

          if(imei != '' && String(imei).length == 15 && !isNaN(parseInt(imei))){
            $('.loading-ajax').show();

            doPostAjax(
              '{{ route('seller.validImei') }}',
              validImei,
              {imei: imei, _token: '{{ csrf_token() }}'}
            );
          }else{
            $('#imei-error').text('Imei no válido');
          }
      });
    @endif
  });

  function initCalendar(){
    doPostAjax(
      '{{ route('sellerFiber.getCalendar') }}',
      function(res){
        $('.loading-ajax').fadeOut();

        if(res.sucess){
          $('#cal-content').html(res.html);
          $('#cal-content').attr('hidden', null);

        }else{
          showMessageAjax('alert-danger',res.msg);
        }
      },
      {
        fiberZone: $('#olt').val(),
        city: $('#city').val()
      },
      $('meta[name="csrf-token"]').attr('content')
    );
  }

  function changerOption(shows, hides){
    /*$('select#plan option[data-force="'+(btoa(shows).replaceAll("=",""))+'"]').removeClass('d-none');
    $('select#plan option[data-force="'+(btoa(hides).replaceAll("=",""))+'"]').addClass('d-none');*/
    $('#plan'+shows).removeClass('d-none');
    $('#plan'+hides).addClass('d-none');
    $('#plan'+shows).val('');
  }

  $('#typePlan, #typePlan_suscrip, #typePlan_bundle').on('change',function(){
    $('#plan').val('');
    var btnT = $(this).attr('id');
    var labelSelect = 'Planes';
    var bundler = false;
    ResetViewFiberCite('plan');
    @if($existBundle)
      //console.log('BUNDLE '+ $('#typePlan_bundle').is(':checked'));
      if( $('#typePlan_bundle').is(':checked')){
        bundler = true;
      }
      $('#imei').val('');
      $('#imei_copy').val('');
      $('#blockValidImei').addClass('d-none');
      $('#alert-comp').hide();

    @endif
    if(sessionStorage.getItem('firstLoad')=='false' && (btnT != 'typePlan_bundle' || bundler)){
      //Entra cuando no sea la peticion de carga sino el click sobre la opciones
      //console.log('list planes OK al dar click');
      listPlan('packs');
    }
    if($('#typePlan').is(':checked')){
      labelSelect += ' con contrato 12 meses';
     // changerOption('Y','N');
    } else {
      labelSelect += ' sin contrato';
      //changerOption('N','Y');
    }

    @if($existSuscrip)
      if($('#typePlan_suscrip').is(':checked')){
        $('#notify_suscription').attr('hidden', null);
        labelSelect += ', con pago recurrente';
      } else {
        $('#notify_suscription').attr('hidden', true);
        labelSelect += ', recargas manuales';
      }
    @endif
    @if($existBundle)
      if($('#typePlan_bundle').is(':checked')){
        labelSelect += ' y con combo';
        //Se coloca un imei como parche temporal
       // $('#imei').val('354301560246154');
       // $('#imei_copy').val('354301560246154');
      } else {
        labelSelect += ' y sin combo';
      }
    @endif
    $('#labelTypePlan').text(labelSelect);
  });

</script>
