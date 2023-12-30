@extends('layouts.ajax')

@section('ajax')
@if(empty($packs->status) || $packs->status == false)
<div class="alert alert-danger">
  <ul>
    <li>
      {{ empty($packs->message) ? 'Ocurrio un error.' : $packs->message }}
    </li>
  </ul>
</div>
@else
<div class="col-md-12">
  <div class="col-md-12">
    <label>
      Plan a asignar
    </label>
    <div class="form-group">
      @if(!$isOptionTelmov)
      <select class="form-control" id="plan">
        <option value="">
          Seleccione un plan.
        </option>
        @foreach($packs->packs as $pack)
        <option data-sale="{{ $pack->sale_type }}" data-type="{{$pack->servicio->type}}" value="{{compoundId(base64_encode($pack->id))}}">
          {{$pack->title}}
        </option>
        @endforeach
      </select>
      @else
      <select class="form-control" id="plan" disabled>
        <option data-sale="{{ $packs->packs[0]->sale_type }}" data-type="{{$packs->packs[0]->servicio->type}}" value="{{compoundId(base64_encode($packs->packs[0]->id))}}">
          {{$packs->packs[0]->title}}
        </option>
      </select>
      @endif
    </div>
  </div>

  <div class="col-md-12">
    <label>
      Venta para:
    </label>
    <div class="form-group px-3">
      <label class="custom-control custom-radio">
        <input checked="" class="custom-control-input" name="saleTo" type="radio" value="C"/>
        <span class="custom-control-indicator">
        </span>
        <span class="custom-control-description">
          Cliente
        </span>
      </label>
      <label class="custom-control custom-radio">
        <input class="custom-control-input" name="saleTo" type="radio" value="A"/>
        <span class="custom-control-indicator">
        </span>
        <span class="custom-control-description">
          Asesor
        </span>
      </label>
    </div>
  </div>

  @foreach($packs->packs as $pack)
  <div class="block-plan p-l-0 col-md-12 @if(!$isOptionTelmov) hidden @endif {{compoundId(base64_encode($pack->id))}}">

    @include('seller.InfoPlan', ['pack' => $pack])

    @if($pack->sale_type == 'Q' && !empty($pack->config) && !$isOptionTelmov)
    <input id="n-quotes-{{compoundId(base64_encode($pack->id))}}" type="hidden" value="{{ $pack->config->quotes }}"/>
    <input id="t-amount-{{compoundId(base64_encode($pack->id))}}" type="hidden" value="{{ ($pack->servicio->price_pack + $pack->servicio->price_serv) }}"/>
    <div class="col-sm-12 col-md-6">
      <label>
        Cuotas :
      </label>
      <p class="quotes-client-{{compoundId(base64_encode($pack->id))}}">
        {{($pack->config->quotes)}}
      </p>
    </div>
    @php
    $min = ($pack->servicio->price_pack + $pack->servicio->price_serv) * ($pack->config->firts_pay / 100);
    @endphp
    <div class="col-sm-12 col-md-6">
      <label>
        Monto mínimo primera cuota:
      </label>
      <p class="price-client-{{compoundId(base64_encode($pack->id))}}">
        $ {{$min}}
      </p>
    </div>
    <div class="col-sm-12 col-md-6">
      <label>
        Monto abonado:
      </label>
      <input class="form-control abono min-abo-{{compoundId(base64_encode($pack->id))}}" min="{{$min}}" type="number" value="{{$min}}"/>
      <small>
        Monto que pagara el cliente en la primera cuota
      </small>
    </div>
    <div class="col-sm-12 col-md-6">
      <label>
        Monto cuota(s) restante(s):
      </label>
      <p class="quotes-price-{{compoundId(base64_encode($pack->id))}}">
        $ {{(($pack->servicio->price_pack + $pack->servicio->price_serv) - $min) / ($pack->config->quotes - 1)}}
      </p>
    </div>

    @endif

    @if($pack->servicio->type == 'CR' && $pack->sale_type != 'Q' && !$isOptionTelmov)
    <div class="col-sm-12 col-md-6">
      <label>
        Monto Financiado:
      </label>
      <p>
        $ {{($pack->servicio->amount_financing)}}
      </p>
    </div>
    @endif
    @if(!$isOptionTelmov)
    <div class="col-sm-12 col-md-6 router-{{compoundId(base64_encode($pack->id))}}">
      <label>
        MSISDN:
      </label>
      <input class="typeahead form-control msisdn @if(count($pack->servicio->articles) == 1) one-msisdn @endif" placeholder="MSISDN" type="text" value="@if(count($pack->servicio->articles) == 1){{$pack->servicio->articles[0]->msisdn}}@endif"/>
    </div>
    <div class="p-l-0 col-sm-12 col-md-12 detail-{{compoundId(base64_encode($pack->id))}}">
      @if(count($pack->servicio->articles) == 1)
      <div class="col-sm-12 p-l-0 col-md-12 m-t-20 arti-detail {{$pack->servicio->articles[0]->msisdn}}">
        @php
          $equip  = new \stdClass;
          $equip->brand = $pack->servicio->articles[0]->brand;;
          $equip->model = $pack->servicio->articles[0]->model;;
          $equip->imei = $pack->servicio->articles[0]->imei;
          $equip->serial = $pack->servicio->articles[0]->serial;;
          $equip->iccid = $pack->servicio->articles[0]->iccid;
          $equip->msisdn = $pack->servicio->articles[0]->msisdn;
        @endphp
        @include('seller.InfoEquip', ['equip' => $equip])

      </div>
      @else
      @foreach($pack->servicio->articles as $arti)
      <div class="col-sm-12 p-l-0 col-md-12 m-t-20 hidden arti-detail {{$arti->msisdn}}">
        @php
          $equip  = new \stdClass;
          $equip->brand = $arti->brand;;
          $equip->model = $arti->model;;
          $equip->imei = $arti->imei;
          $equip->serial = $arti->serial;;
          $equip->iccid = $arti->iccid;
          $equip->msisdn = $arti->msisdn;
        @endphp
        @include('seller.InfoEquip', ['equip' => $equip])

      </div>
      @endforeach
      @endif
    </div>
    @else
      <div class="router-{{compoundId(base64_encode($pack->id))}}">
        <input class="typeahead form-control msisdn one-msisdn" type="text" hidden value="{{ $pack->servicio->articles[0]->msisdn }}"/>
      </div>
    @endif
    <div class="col-md-12 m-t-20">
      @if($pack->sale_type == 'Q' && !empty($pack->config) && !$isOptionTelmov)
      <button class="btn btn-success waves-effect waves-light show-confirmation" data-art="{{compoundId(base64_encode($pack->id))}}" type="button">
        @if(session('user_type') != 'vendor')
        Procesar venta en abono
        @else
        Solicitar venta en abono
        @endif
      </button>
      @else
      @if(!empty($pack->is_visible_coppel) && $pack->is_visible_coppel == 'Y')
        @if(!hasPermit('SEL-COP'))
        <div class="alert alert-danger alert-dismissable">
          No tienes permiso para vender con financiamiento Coppel.
        </div>
        @endif
      @else
        @if($pack->valid_identity == 'Y' && !$isOptionTelmov)
      <div class="alert alert-danger alert-dismissable alert-vi-danger" hidden="" id="alert-vi-danger-{{compoundId(base64_encode($pack->id))}}">
        Falló la verificación de identidad.
      </div>
      <div class="alert alert-success alert-dismissable alert-vi-success" hidden="" id="alert-vi-{{compoundId(base64_encode($pack->id))}}">
        Identidad verificada exitosamente.
      </div>
      <button class="btn btn-success waves-effect waves-light show-identity-veri" data-art="{{compoundId(base64_encode($pack->id))}}" id="btn-vi-{{compoundId(base64_encode($pack->id))}}" type="button">
        Verificar identidad
      </button>
      @endif
      <div class="refered-container d-none row mb-5">
        <div class="col-md-12">
          <label>
            Venta por Referido:
          </label>
          <div class="form-group px-3">
            <label class="custom-control custom-radio">
              <input checked="" class="custom-control-input" name="refopt" id="refN" type="radio" value="N"/>
              <span class="custom-control-indicator">
              </span>
              <span class="custom-control-description">
                No
              </span>
            </label>
            <label class="custom-control custom-radio">
              <input class="custom-control-input" name="refopt" id="refY" type="radio" value="Y"/>
              <span class="custom-control-indicator">
              </span>
              <span class="custom-control-description">
                Si
              </span>
            </label>
          </div>
        </div>
        <div class="col-12 form-group refered-container-data d-none">
          <div class="row">
            <div class="col-12 col-md-6">
              <label for="">
                MSISDN de referencia
              </label>
              <input class="form-control msisdn-ref" id="msisdn-ori-ref" type="text"/>
            </div>
          </div>
          <div class="row">
            <div class="col-12 col-md-6">
              <label for="">
                Repita MSISDN de referencia
              </label>
              <input class="form-control msisdn-ref" id="msisdn-rep-ref" type="text"/>
            </div>
          </div>
        </div>
        <div class="col-12 refered-client-data d-none">
          <input id="msisdn_refered" name="msisdn_refered" type="hidden"/>
          <label for="">
            Referido por:
          </label>
          <p id="name_ref">
          </p>
          <p id="email_ref">
          </p>
        </div>
      </div>
      <button type="button" id="btn-reg-{{compoundId(base64_encode($pack->id))}}" class="btn btn-success waves-effect waves-light show-confirmation" data-art="{{compoundId(base64_encode($pack->id))}}" @if($pack->valid_identity == 'Y') hidden @endif>
          Procesar alta
      </button>
      @endif

      @if(!empty($pack->is_visible_coppel) && $pack->is_visible_coppel == 'Y' && hasPermit('SEL-COP'))
      @if($pack->valid_identity == 'Y')
      <div class="alert alert-danger alert-dismissable alert-vi-danger" hidden="" id="alert-vi-danger-{{compoundId(base64_encode($pack->id))}}">
        Falló la verificación de identidad.
      </div>
      <div class="alert alert-success alert-dismissable alert-vi-success" hidden="" id="alert-vi-{{compoundId(base64_encode($pack->id))}}">
        Identidad verificada exitosamente.
      </div>
      <button class="btn btn-success waves-effect waves-light show-identity-veri" data-art="{{compoundId(base64_encode($pack->id))}}" id="btn-vi-{{compoundId(base64_encode($pack->id))}}" type="button">
        Verificar identidad
      </button>
      @endif
      <button type="button" id="btn-reg-{{compoundId(base64_encode($pack->id))}}" class="btn btn-success waves-effect waves-light show-confirmation-coppel" data-art="{{compoundId(base64_encode($pack->id))}}" @if($pack->valid_identity == 'Y') hidden @endif>
        Pagar con Coppel
      </button>
      @endif
      @endif
    </div>
  </div>
  @endforeach
  <div class="col-md-12 m-t-20" hidden="true" id="content-coppel-payment">
    <div id="cpplPay">
      <div class="loading-coppel">
        <div class="content">
          <i class="fa fa-spin fa-circle-o-notch">
          </i>
        </div>
      </div>
    </div>
    <div class="text-center" hidden="true" id="pr-coppel-content">
      <button class="btn btn-success waves-effect waves-light process-coppel" type="button">
        Confirmar
      </button>
    </div>
  </div>
  <div class="col-md-12 m-t-20" hidden="true" id="content-identity">
    <iframe id="frm-identity" style="height:800px;width:100%;border:none;">
    </iframe>
  </div>
</div>
@endif
<script type="text/javascript">
  let getDataAlta = function(num) {
    let latitud = $('#lat').val(),
        longitud = $('#lon').val(),
        token = "{{ csrf_token() }}",
        pack = $('#plan').val(),
        service = $('.service-' + pack).val(),
        address = $('#addressClient').val(),
        typeB = '',
        client = $('#client').val(),
        min_inst = $('.min-abo-' + pack).val(),
        min_per = $('.min-abo-' + pack).attr('min'),
        type_sell = $('#type-sell').val(),
        imei = $('#imei').val(),
        saleTo = $('input[name=saleTo]:checked').val();

    $('[name = "cocr"]').each(function(e) {
      if ($(this).is(':checked')) typeB = $(this).val();
    });

    typeB = $('#plan').find(':selected').data('type');
    typeSale = $('#plan').find(':selected').data('sale');

    if (typeB != '' &&
      (
        (type_sell == 'home' && latitud != '' && longitud != '') ||
        (type_sell == 'mov' && imei != '') ||
        type_sell == 'mov-ph' ||
        type_sell == 'mifi' ||
        (type_sell == 'mifi-h' && latitud != '' && longitud != '')
      ) &&
      num != '' && token != '' && service != '' && client != '' && pack != '') {
      let datos = {
        _token: token,
        lat: latitud,
        lon: longitud,
        num: num,
        type: typeB,
        typeSale: typeSale,
        service: service,
        address: address,
        client: client,
        plan: pack,
        min_inst: min_inst,
        min_per: min_per,
        type_sell: type_sell,
        imei: imei,
        isPort: false,
        saleTo: saleTo
      };

      if ($('#form-port-content').is(':visible')) {
        valid = $("#formPort").valid();
        if (valid) {
          datos.nip = $('#nip').val();
          datos.dnPort = $('#dn_port').val();
          datos.companyPort = $('#port-prov').val();

          {{--
            let dnf = document.getElementById('dni-front').files[0];
            datos.dnf = dnf;

            let dnb = document.getElementById('dni-back').files[0];
            datos.dnb = dnb;
          --}}

          datos.isPort = true;
        } else {
          return false;
        }
      }

      if($('#type-payment').length && $('#type-payment-content').is(':visible')){
        datos.typePaymentF = $('#type-payment').val();
      }

      return datos;
    } else {
      return false;
    }
  }

  $(function() {
    var typeH = [],
        numSelected = false;

    @if($packs->status)
    @foreach($packs->packs as $pack)
    var arr = [];
    @foreach($pack->servicio->articles as $arti)
    arr.push({{ $arti->msisdn }});
    @endforeach
    typeT = $('.router-{{compoundId(base64_encode($pack->id))}} .typeahead').typeahead({
      hint: true
      , highlight: true
      , minLength: 1
      , dynamic: true
    }, {
      name: 'articles'
      , source: substringMatcher(arr)
    }).on('typeahead:selected', function(evt, data) {
      dnSelected(data);
    });

    typeH['{{compoundId(base64_encode($pack->id))}}'] = typeT;
    @endforeach
    @endif

    let validInterval;

    function calcQuotes(min_p, min_i) {
      var min_inst = min_i,
          min_per = min_p,
          total = $('#t-amount-' + $('#plan').val()).val(),
          quotes = $('#n-quotes-' + $('#plan').val()).val() - 1;

      if (min_inst && min_inst != '' && min_per <= min_inst) {
        $('.quotes-price-' + $('#plan').val()).text('$ ' + ((total - min_inst) / quotes));
        return true;
      } else {
        showMessageAjax('alert-danger', 'El monto a abonar no puede ser menor que $' + min_per + '.');

        return false;
      }
    }

    $('.abono').on('blur', function(e) {
      calcQuotes($(e.currentTarget).attr('min'), $(e.currentTarget).val());
    });

    $(".refered-container input:radio[name=refopt]").on('change',function(){
      console.log("Ref "+$(this).val());
      if($(this).val()=='Y'){
        $('.refered-container-data').removeClass('d-none');
      }
      else{
        $('.refered-container-data').addClass('d-none');
        $('.refered-client-data').addClass('d-none');
        $('#msisdn-ori-ref').val("");
        $('#msisdn-rep-ref').val("");
        $('#msisdn_refered').val("");
        $('#name_ref').html("");
        $('#email_ref').html("");
      }
    });

    $('.msisdn-ref').on('blur', function(e) {
     // console.log('1-> '+$('#msisdn-ori-ref').val());
     // console.log('2-> '+$('#msisdn-rep-ref').val());

    if($('#msisdn-ori-ref').val().trim().length == 10 && $('#msisdn-rep-ref').val().trim().length == 10 && $('#msisdn-ori-ref').val().trim() == $('#msisdn-rep-ref').val().trim()){

        $('.loading-ajax').show();

        $.ajax({
          type: 'POST',
          url: "{{route('client.getByDn')}}",
          dataType: "json",
          data: {
            _token: "{{ csrf_token() }}",
            msisdn: $('#msisdn-ori-ref').val(),
          },
          success: function(res) {
            $('.loading-ajax').hide();
            //console.log(res);
            if(res.error){
              showMessageAjax('alert-danger', 'No se conseguieron registros para el MSISDN: '+($('#msisdn-ori-ref').val()));
            }
            else{
              $('.refered-container-data').addClass('d-none');
              $('.refered-client-data').removeClass('d-none');
              $('#msisdn_refered').val($('#msisdn-ori-ref').val());
              $('#name_ref').html('<strong>Cliente:</strong> '+ res.client.name+" "+res.client.last_name);
              $('#email_ref').html('<strong>Email:</strong> '+ res.client.email);
            }
          },
          error: function() {
            $('.loading-ajax').hide();
            showMessageAjax('alert-danger', 'Ups Ocurrio un error, por favor intenta de nuevo.');
          }
        });
      }
    });

    $('#plan').on('change', function(e) {
      var block = $('#plan').val();
      $('.block-plan').addClass('hidden');

      if (block && block != '') {
        $('.' + block).removeClass('hidden');
      }

      if (!$('.router-' + block + ' .msisdn').hasClass('one-msisdn')) {
        $('.router-' + block + ' .msisdn').val('');
      }

      $("#refN").prop("checked", true);
      //$(".refered-container input:radio[name=refopt]").val('N');
      $('.refered-client-data').addClass('d-none');

      //mostrando u ocultando seccion de referidos
      if($('.container-btn-type').data('val') == 'mov-ph'){
        $('.refered-container').removeClass('d-none');
      }
      else{
        $('.refered-container').addClass('d-none');
      }

      //reiniciando botones para verificar identidad
      $('#content-identity').attr('hidden', true);

      $('.alert-vi-success').each(function(){
        //if($(this).is(':visible')){
          $(this).siblings('.show-identity-veri').attr('hidden', null);
          $(this).siblings('.show-identity-veri').attr('disabled', null);
          $(this).siblings('.show-confirmation').attr('hidden', true);
          $(this).siblings('.show-confirmation-coppel').attr('hidden', true);
          $(this).attr('hidden', true);
        //}
      });

      $('.alert-vi-danger').each(function(){
        //if($(this).is(':visible')){
          $(this).siblings('.show-identity-veri').attr('hidden', null);
          $(this).siblings('.show-identity-veri').attr('disabled', null);
          $(this).siblings('.show-confirmation').attr('hidden', true);
          $(this).siblings('.show-confirmation-coppel').attr('hidden', true);
          $(this).attr('hidden', true);
        //}
      });

      if(validInterval){
        clearInterval(validInterval);
      }
      //Fin de reinicio verificar identidad
    });

    validNumberSale = function(num, article, typePay){

      var dataAlta = getDataAlta(num);

      //Verificando monto abonado para primera cuota
      if (dataAlta.typeSale == 'Q') {
        if (!calcQuotes(dataAlta.min_per, dataAlta.min_inst)){
          return;
        }
      }

      $('.loading-ajax').show();

      $.ajax({
        type: 'POST',
        url: "{{route('seller.validNumberSale')}}",
        data: {
          _token: "{{ csrf_token() }}",
          msisdn: num,
          pack: $('#plan').val()
        },
        success: function(data) {
          $('.loading-ajax').hide();

          if (data.message == 'TOKEN_EXPIRED') {
            showMessageAjax('alert-danger', 'Su session a expirado, por favor actualice la página.');
          } else {
            if (data.error == false) {
              if (dataAlta) {
                var tyb = dataAlta.type == 'CR' ? 'Crédito' : 'Contado';

                if (dataAlta.typeSale == 'Q'){
                  tyb += ' - ' + 'Abono';
                }

                $('#sale-confirmation .name-client').text($('.client-name-full').text().trim());
                $('#sale-confirmation .ine-client').text($('.client-dni').text().trim());
                $('#sale-confirmation .type-client').text(tyb);
                $('#sale-confirmation .plan-client').text($('#plan option:selected').text().trim());
                $('#sale-confirmation .msisdn-client').text(dataAlta.num);
                $('#sale-confirmation .price-client').text($('.price-client-' + dataAlta.plan).text().trim());

                $('#sale-confirmation .btnBuy').attr('hidden', true);

                if(typePay == 'seller' || typePay == 'telmovpay'){
                  $('#sale-confirmation .btnBuy').data('art', article);
                  $('#sale-confirmation .btnBuy').attr('hidden', null);
                }

                if (dataAlta.typeSale == 'Q') {
                  //Sobre escribiendo monto abonado
                  $('#sale-confirmation .price-client').text('$ ' + dataAlta.min_inst);
                  $('#sale-confirmation .type-pay-n').attr('hidden', true);
                  $('#sale-confirmation .quotes-price').text($('.quotes-price-' + dataAlta.plan).text().trim());
                  $('#sale-confirmation .quotes-client').text($('.quotes-client-' + dataAlta.plan).text().trim());
                  $('#sale-confirmation .type-pay-q').attr('hidden', null);
                } else {
                  $('#sale-confirmation .quotes-price').text('');
                  $('#sale-confirmation .quotes-client').text('');
                  $('#sale-confirmation .type-pay-q').attr('hidden', true);
                  $('#sale-confirmation .type-pay-n').attr('hidden', null);
                }

                if (dataAlta.type_sell == 'home') {
                  $('#type-sell-txt').text('(Internet Hogar)');
                }

                if (dataAlta.type_sell == 'mov') {
                  $('#type-sell-txt').text('(Telefonía (SimCard))');
                }

                $('#info-payjoy-content').attr('hidden', true);
                $('#info-refered-content').attr('hidden', true);

                if (dataAlta.type_sell == 'mov-ph') {
                  $('#type-sell-txt').text('(Telefonía celular)');

                  $('#sale-confirmation .btnBuy-coppel').attr('hidden', true);

                  if(typePay == 'coppel'){
                    $('#sale-confirmation .btnBuy-coppel').data('art', article);
                    $('#sale-confirmation .btnBuy-coppel').attr('hidden', null);
                  }

                  /*$('#info-telmovpay-content').attr('hidden', true);
                  if(typePay == 'telmovpay'){
                    $('#info-telmovpay-content').attr('hidden', null);
                  }*/

                  if((typePay == 'seller') && (dataAlta.typePaymentF == 'payjoy' || dataAlta.typePaymentF == 'paguitos')){
                    $('#info-payjoy-content').attr('hidden', null);
                  }

                  $('#info-refered-content').attr('hidden', null);
                  refText = ($('#msisdn_refered').val().length>0)?$('#msisdn_refered').val() : "S/N";
                  $('#sale-confirmation .refered-by').text(refText);
                }

                if (dataAlta.type_sell == 'mifi') {
                  $('#type-sell-txt').text('(Internet móvil Nacional)');
                }

                if (dataAlta.type_sell == 'mifi-h') {
                  $('#type-sell-txt').text('(Internet móvil Huella altan)');
                }

                $('#sale-confirmation').modal('show');
              } else {
                showMessageAjax('alert-danger', 'No se pudo procesar el alta, faltan datos.');
              }
            } else {
              if (data.msg) {
                showMessageAjax('alert-danger', data.msg);
              } else {
                showMessageAjax('alert-danger', 'El Artículo no esta asociado con el plan seleccionado.');
              }
            }
          }
        },
        error: function() {
          $('.loading-ajax').hide();
          showMessageAjax('alert-danger', 'Ups Ocurrio un error, por favor intenta de nuevo.');
        }
      });
    }

    isNumeric = function(val) {
      return /^\d+$/.test(val);
     // return /^-?\d+$/.test(val);
    }

    validMSISDN = function(e){
      let num = typeH[$(e.currentTarget).data('art')].val();
      let art = $(e.currentTarget).data('art');
      console.log('num '+num);
      console.log('art '+art);

      let dataR = {
        'success': true,
        'num': num,
        'art': art};

      if (!num || num == '') {
        showMessageAjax('alert-danger', 'Debe seleccionar el MSISDN');
        dataR['success'] = false;
        return dataR;
      }else{
        if(num.length != 10 || !isNumeric(num) ){
          showMessageAjax('alert-danger', 'Debe ingresar un MSISDN valido');
          dataR['success'] = false;
          return dataR;
        }
      }
      return dataR;
    }

    $('.show-confirmation').on('click', function(e) {
      dataR = validMSISDN(e);
      if(dataR['success']){

        @if(!empty($pack->is_visible_telmovPay) && $pack->is_visible_telmovPay == 'Y' && hasPermit('SEL-TLP'))
          Torigen = 'telmovpay';
        @else
          Torigen = 'seller';
        @endif
        //console.log(Torigen);
        validNumberSale(dataR['num'], dataR['art'], Torigen);
      }
    });

    $('.show-confirmation-coppel').on('click', function(e) {
      dataR = validMSISDN(e);
      if(dataR['success']){
        validNumberSale(dataR['num'], dataR['art'], 'coppel');
      }
    });

    const resetVerIdentity = function(){
      $('#content-identity').attr('hidden', true);
      $('.show-identity-veri').attr('hidden', null);
      $('.show-identity-veri').attr('disabled', false);
      $('.alert-vi-success').attr('hidden', true);
      $('.show-confirmation-coppel').attr('hidden', true);
      $('.show-confirmation').attr('hidden', true);

      if(validInterval){
        clearInterval(validInterval);
      }
    }

    {{-- Verificando identidad con truora --}}
    $('.show-identity-veri').on('click', function(e){
      if(!validMSISDN(e)){
        return false;
      }

      $('.loading-ajax').show();
      $('.show-identity-veri').attr('disabled', true);

      {{-- solicitando url de redirección para verificar identidad --}}
      $.ajax({
        type: 'POST',
        url: "{{route('seller.validIdentity')}}",
        data: {
          _token: "{{ csrf_token() }}",
          msisdn: num,
          client: $('#buscar').val()
        },
        success: function(data) {
          $('.loading-ajax').hide();

          if(!data.error){
            {{-- Si la identidad no ha sido verificada se carga la url en el iframe y se consulta cada cierto tiempo el estatus de la verificación --}}
            if(data.data.status === 'redirect'){
              $('#content-identity').attr('hidden', null);
              $('#frm-identity').attr('src', data.data.url);

              if(validInterval){
                clearInterval(validInterval);
              }

              validInterval = setInterval(function(){
                $.ajax({
                  type: 'POST',
                  url: "{{route('seller.checkValidIdentity')}}",
                  data: {
                    _token: "{{ csrf_token() }}",
                    id: data.data.id
                  },
                  success: function(resVer){
                    if(!resVer.error){
                      if(resVer.status == 'failure' || resVer.status == 'success'){
                        //resetVerIdentity();
                        clearInterval(validInterval);

                        $('#content-identity').attr('hidden', true);
                        $('#btn-vi-'+art).attr('hidden', true);

                        if(resVer.status == 'success'){
                          $('#alert-vi-'+art).attr('hidden', null);
                          $('#btn-reg-'+art).attr('hidden', null);
                        }

                        if(resVer.status == 'failure'){
                          $('#alert-vi-danger-'+art).text(resVer.status_detail);
                          $('#alert-vi-danger-'+art).attr('hidden', null);
                          $('#btn-reg-'+art).attr('hidden', true);
                        }
                      }
                    }else{
                      if (resVer.message == 'TOKEN_EXPIRED') {
                        resetVerIdentity();
                        showMessageAjax('alert-danger', 'Su sesión a expirado, por favor actualice la página.');
                      } else {
                        resetVerIdentity();
                        showMessageAjax('alert-danger', resVer.msg);
                      }
                    }
                  },
                  error: function(err){
                    resetVerIdentity();
                    console.log(err);
                    showMessageAjax('alert-danger', 'Ups Ocurrio un error, consultando el proceso de verificación, por favor intente de nuevo.');
                  }
                });
              }, (20 * 1000));
            }

            {{-- Si la identidad ya fue verificada se muestra mensaje de identidad verificada --}}
            if(data.data.status === 'verified'){
              $('#alert-vi-'+art).attr('hidden', null);
              $('#btn-vi-'+art).attr('hidden', true);
              $('#btn-reg-'+art).attr('hidden', null);
            }
          }else{
            if (data.message == 'TOKEN_EXPIRED') {
              showMessageAjax('alert-danger', 'Su sesión a expirado, por favor actualice la página.');
            } else {
              showMessageAjax('alert-danger', data.msg);
            }
          }
        },
        error: function(err){
          console.log(err);
          resetVerIdentity();
          $('.loading-ajax').hide();
          showMessageAjax('alert-danger', 'Ups Ocurrio un error, por favor intenta de nuevo.');
        }
      });
    });

    $('#sale-confirmation').on('hide.bs.modal', function(event) {
      $('#sale-confirmation .name-client').text('');
      $('#sale-confirmation .ine-client').text('');
      $('#sale-confirmation .email-client').text('');
      $('#sale-confirmation .type-client').text('');
      $('#sale-confirmation .plan-client').text('');
      $('#sale-confirmation .msisdn-client').text('');
      $('#sale-confirmation .price-client').text('');
      $('#sale-confirmation .type-pay-n').attr('hidden', null);
      $('#sale-confirmation .quotes-price').text('');
      $('#sale-confirmation .quotes-client').text('');
      $('#sale-confirmation .type-pay-q').attr('hidden', true);
    });

    isCoppelReady = function(){
      $('#pr-coppel-content').attr('hidden', null);
    }

    var isCoppelCancel = function(){
      $('#pr-coppel-content').attr('hidden', true);
      $('#content-coppel-payment').attr('hidden', true);
      $('#cpplPay').html('<div class="loading-coppel">'+
        '<div class="content">'+
          '<i class="fa fa-spin fa-circle-o-notch"></i>'+
        '</div>'+
      '</div>');

      swal({
        title: "El pago fue cancelado.",
        text: "No se proceso el pago porque fue cancelado.",
        icon: "error",
        button: {
          text: "OK"
        },
      });
    }

    let doBuy = function(num, isCoppel = false, objCoppel = false, telmovpay = false){

      var processUrl = "{{route('seller.processSale')}}";
      dataAlta = getDataAlta(num);

      $('#sale-confirmation').modal('hide');

      if (dataAlta) {
        let activationType = dataAlta.typeSale;

        @if(session('user_type') != 'vendor')
        activationType = 'N';
        @endif

        let messageWait = 'Procesando alta';
        if(activationType == 'Q'){
          messageWait = 'Solicitando Venta en Abono';
        }
        if(isCoppel){
          if(objCoppel){
            messageWait = 'procesando alta y confirmando pago en Coppel';
          }else{
            messageWait = 'Generando formulario de pago con Coppel';
          }
        }

        swal({
          title: messageWait,
          text: "Por favor no cierre ni refresque el navegador.",
          icon: "warning",
          closeOnClickOutside: false,
          button: {
            visible: false
          }
        });

        let params = new FormData();
        params.append('_token', dataAlta._token);
        params.append('lat', dataAlta.lat);
        params.append('lon', dataAlta.lon);
        params.append('num', dataAlta.num);
        params.append('type', dataAlta.type);
        params.append('typeSale', dataAlta.typeSale);
        params.append('service', dataAlta.service);
        params.append('address', dataAlta.address);
        params.append('client', dataAlta.client);
        params.append('plan', dataAlta.plan);
        params.append('min_inst', dataAlta.min_inst);
        params.append('min_per', dataAlta.min_per);
        params.append('type_sell', dataAlta.type_sell);
        params.append('isPort', dataAlta.isPort);
        params.append('imei', dataAlta.imei);
        params.append('saleTo', dataAlta.saleTo);
        params.append('isBandTE', $("#is-band-te").val());
        params.append('isCoppel', isCoppel);
        params.append('dnReferred', $('#msisdn_refered').val());

        params.append('isTelmovPay', telmovpay);
        if(telmovpay){
          @if(hasPermit('SEL-TLP'))
            params.append('brand', $('#brand-mov').val());
            params.append('art_inv_model', $('#modelPhone').val());
          @endif
        }
        if(objCoppel){
          params.append('blackbox', objCoppel.blackbox);
          params.append('token', objCoppel.token);
        }

        if (dataAlta.isPort) {
          params.append('nip', dataAlta.nip);
          params.append('dnPort', dataAlta.dnPort);
          params.append('companyPort', dataAlta.companyPort);
          {{--
          params.append('dnf', dataAlta.dnf);
          params.append('dnb', dataAlta.dnb);
          --}}
        }

        if(dataAlta.typePaymentF){
          params.append('typePaymentF', dataAlta.typePaymentF);
        }

        $.ajax({
          type: 'POST',
          url: processUrl,
          data: params,
          contentType: false,
          processData: false,
          cache: false,
          async: true,
          mimeType: "multipart/form-data",
          dataType: "json",
          success: function(data) {
            //data = JSON.parse(data);

            if (!data.error) {
              swal.close();

              if(!isCoppel && !telmovpay){
                  $("html, body").animate({
                    scrollTop: "0px"
                  });

                  $('#buscar').val('');
                  $('#showClient').html('');
                  $('#buscar').data('selectize').setValue("");

                  swal({
                    title: "Éxito",
                    text: activationType == 'Q' ? "Venta Solicitada." : "Alta procesada exitosamente.",
                    icon: "success",
                    button: {
                      text: "OK"
                    }
                  });
                }else{
                  if(!objCoppel && !telmovpay){
                    $('#content-coppel-payment').attr('hidden', null);
                    CPLPY.init(data.request);
                    @if(env('APP_ENV') == 'local' || env('APP_ENV') == 'test')
                      CPLPY.sandbox = true;
                    @endif
                    CPLPY.open();
                  }else{
                    $("html, body").animate({
                      scrollTop: "0px"
                    });

                    $('#buscar').val('');
                    $('#showClient').html('');
                    $('#buscar').data('selectize').setValue("");

                    swal({
                      title: "Éxito",
                      text: "Alta procesada exitosamente.",
                      icon: "success",
                      button: {
                        text: "OK"
                      }
                    }).then(() => {
                      /*if(telmovpay){
                        //Sere redirigido
                        var win = window.open("{route('telmovpay.asociateFinanceTelmov')}}", '_self');
                        win.focus();
                      }*/
                    });
                  }
              }
            } else {
              $("html, body").animate({
                scrollTop: "0px"
              });

              if (data.message == 'TOKEN_EXPIRED') {
                showMessageAjax('alert-danger', 'Su sesión a expirado, por favor actualice la página.');
              } else {
                if(isCoppel && objCoppel){
                  if(data.errorAltan){
                    $('.show-confirmation-coppel:visible').attr('hidden', true);
                  }
                }

                if(!telmovpay){
                  $('#pr-coppel-content').attr('hidden', true);
                  $('#content-coppel-payment').attr('hidden', true);
                  $('#cpplPay').html('<div class="loading-coppel">'+
                    '<div class="content">'+
                      '<i class="fa fa-spin fa-circle-o-notch"></i>'+
                    '</div>'+
                  '</div>');
                }
                swal({
                  title: activationType == 'Q' ? "No se pudo solicitar la venta." : "No se pudo procesar el alta.",
                  text: data.message ? data.message : 'No se pudo procesar la venta.',
                  icon: "error",
                  button: {
                    text: "OK"
                  },
                });
              }
            }
          },
          error: function(error) {
            if(!telmovpay){
              $('#pr-coppel-content').attr('hidden', true);
              $('#content-coppel-payment').attr('hidden', true);
              $('#cpplPay').html('<div class="loading-coppel">'+
                '<div class="content">'+
                  '<i class="fa fa-spin fa-circle-o-notch"></i>'+
                '</div>'+
              '</div>');
            }
            swal({
              title: "Error",
              text: activationType == 'Q' ? "No se pudo solicitar la venta, Falló comunicación con el servidor." : "No se pudo procesar el alta. Falló comunicación con el servidor.",
              icon: "error",
              button: {
                text: "OK"
              }
            });
          }
        });
      } else {
        $("html, body").animate({
          scrollTop: "0px"
        });
        showMessageAjax('alert-danger', 'No se pudo procesar el alta, faltan datos.');
      }
    }

    $('#pr-coppel-content').on('click', function(e){
      let dn = $('#pr-coppel-content').data('dn');
      let blackbox = CPLPY.blackbox;
      let token = CPLPY.token;

      if(dn && dn != '' && blackbox && token){
        doBuy(dn, true, {blackbox: blackbox, token: token});
      }
    });

    //Este boton esta en /seller/index.blade.php
    $('.btnBuy-coppel').unbind('click');
    $('.btnBuy-coppel').on('click', function(e){
      var num = typeH[$(e.currentTarget).data('art')].val();
      $('#pr-coppel-content').data('dn', num);
      doBuy(num, true);
    });

    //Este boton esta en /seller/index.blade.php
    $('.btnBuy').unbind('click');
    $('.btnBuy').on('click', function(e) {
      var num = typeH[$(e.currentTarget).data('art')].val();

      @if(!empty($pack->is_visible_telmovPay) && $pack->is_visible_telmovPay == 'Y' && hasPermit('SEL-TLP'))
        //alert("TELMOVPAY");
        doBuy(num, false, false, true);
      @else
        doBuy(num, false);
      @endif
    });

    let dnSelected = function(dn) {
      if (!$('.' + dn).length) {
        showMessageAjax('alert-danger', 'El msisdn no existe.');
      }

      if ($('.' + dn).hasClass('hidden')) {
        $('.arti-detail').addClass('hidden');
        $('.' + dn).removeClass('hidden');
      }
    }

    $('.msisdn').focusin(function(e) {
      $('.arti-detail').addClass('hidden');
    });

    $('.msisdn').focusout(function(e) {
      if ($(this).val() != '' && $(this).val().length == 10) {
        dnSelected($(this).val());
      }
    });

  });
</script>
@stop
