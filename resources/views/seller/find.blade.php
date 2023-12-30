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
  <div class="col-12">
    @include('seller.InfoClient', ['client' => $client ])
  </div>
</div>
@if(hasPermit('SEL-MOV') || hasPermit('SEL-MIF'))
<div class="row p-b-20" id="content-product">
  <label class="col-md-12">
    Producto a vender
  </label>
  <div class="col-md-12 container-btn-type" data-val="">
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10" id="type-home" name="type-home" type="button">
      Internet Hogar
    </button>
    @if(hasPermit('SEL-MOV'))
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10" id="type-mov" name="type-mov" type="button">
      Telefonía (SimCard)
    </button>
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10" id="type-mov-ph" name="type-mov-ph" type="button">
      Telefonía (Smartphone)
    </button>
    @endif

        @if(hasPermit('SEL-MIF'))
    <button class="btn btn-success waves-effect waves-light m-t-10" id="type-mifi" name="type-mifi" type="button">
      MIFI
    </button>
    @endif
  </div>
</div>
@if(hasPermit('SEL-MIF'))
<div class="row p-b-20" hidden="" id="val-content-mifi">
  <div class="col-md-12">
    <label class="col-md-12">
      Tipo de venta
    </label>
    <button class="btn btn-success waves-effect waves-light m-t-10" id="type-mifi-h" name="type-mifi-h" type="button">
      Internet móvil huella altan
    </button>
    <button class="btn btn-success waves-effect waves-light m-t-10" id="type-mifi-n" name="type-mifi-n" type="button">
      Internet móvil nacional
    </button>
  </div>
</div>
@endif
@include('seller.validImei')
<div class="row p-b-20" hidden="" id="type-payment-content">
  <div class="col-md-12">
    <label class="col-md-12">
      Tipo de pago
    </label>
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10 btn-tp" data-type="payjoy" id="payjoy" name="payjoy" type="button">
      Payjoy
    </button>
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10 btn-tp" data-type="paguitos" id="paguitos" name="paguitos" type="button">
      Paguitos
    </button>
    {{--
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10 btn-tp" data-type="coppel" id="coppel" name="coppel" type="button">
      Coppel
    </button>
    --}}

      @if(hasPermit('SEL-TLP'))
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10 btn-tp" data-type="telmovpay" id="telmovpay" name="telmovpay" type="button">
      TelmovPay
    </button>
    @endif
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10 btn-tp" data-type="contado" id="contado" name="contado" type="button">
      Contado
    </button>
    <input id="type-payment" name="type-payment" type="hidden" value=""/>
  </div>
</div>
@if(hasPermit('SEL-TLP'))

<div class="row">
  <div class="col-md-12" id="infoPhone"></div>
</div>

@endif

<div class="row p-b-20" hidden="" id="port-content">
  <div class="col-md-12">
    <label class="col-md-12">
      Tipo de venta
    </label>
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10" id="wto-port" name="wto-port" type="button">
      Sin portabilidad
    </button>
    <button class="btn btn-success waves-effect waves-light m-t-10" id="wt-port" name="wt-port" type="button">
      Con portabilidad
    </button>
  </div>
</div>

@include('seller.formPortability', ['banJs' => 'seller'])

@endif
<div class="row" @if(hasPermit('SEL-MOV') || hasPermit('SEL-MIF')) hidden @endif id="geo-content">
  <input id="type-sell" name="type-sell" type="hidden" value="home"/>
  <label class="col-md-12">
    Geolocalización
  </label>
  @if(hasPermit('ECO-DSE'))
  <div class="col-md-12">
    <input class="form-control form-control-sm" id="address" name="address" placeholder="Escribe la dirección donde estara el Netwey*" type="text"/>
    <div class="col-md-12 col-sm-12 map-container" id="map" style="height: 50vh;min-height: 300px; padding-top: 20px;">
      <div id="map-content" style="width: 100%;height: 100%;">
      </div>
    </div>
  </div>
  @endif
  <div class="row p-t-20 m-l-10">
    <div class="col-xs-12 col-md-4">
       <input class="form-control" id="lat" name="lat" step="0.000000000000001" type="number" placeholder="Latitud" @if(!hasPermit('ECO-DSE')) readonly @endif required>
        <div class="help-block with-errors">
        </div>
      </input>
    </div>
    <div class="col-xs-12 col-md-4">
      <input class="form-control" id="lon" name="lon" step="0.000000000000001" type="number" placeholder="longitud" @if(!hasPermit('ECO-DSE')) readonly @endif required>
        <div class="help-block with-errors">
        </div>
      </input>
    </div>
    <div class="col-xs-12 col-md-4">
      <button class="btn btn-success waves-effect waves-light" id="btnGeo" name="btnGeo" type="button">
        Ubicar
      </button>
    </div>
  </div>
</div>
<div class="row" id="divPack">
</div>
@endif
@if(hasPermit('ECO-DSE'))
<script type="text/javascript">
  function initPlaces(){
            var input = document.getElementById('address'),
                autocomplete = new google.maps.places.Autocomplete(input),
                {{-- geocoder = new google.maps.Geocoder, --}}
                lat = 19.39068,
                lng = -99.2836963;

            geocoder = new google.maps.Geocoder,

            center = new google.maps.LatLng(lat, lng),
            map = new google.maps.Map(document.getElementById('map-content'), {
                            center,
                            zoom: 5
                        });

            marker = new google.maps.Marker({
                        map: map,
                        draggable: true,
                        animation: google.maps.Animation.DROP,
                        position: {lat: lat, lng: lng}
                    });

            map.addListener('click', function(e) {
                marker.setPosition(e.latLng);
                map.panTo(e.latLng);

                lat = marker.getPosition().lat();
                lng = marker.getPosition().lng();

                geocodeLatLng(lat, lng, geocoder, map, marker, true);
            });

            marker.addListener('dragend', function (event){
                lat = marker.getPosition().lat();
                lng = marker.getPosition().lng();

                geocodeLatLng(lat, lng, geocoder, map, marker, true);
            });

            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                var place = autocomplete.getPlace();

                if(place.geometry){
                    lat = place.geometry.location.lat();
                    lng = place.geometry.location.lng();

                    geocodeLatLng(lat, lng, geocoder, map, marker, true);
                }
            });

            $('#address').on('keypress', function(e){
                if(e.which == 10 || e.which == 13){
                    var firstA = $('.pac-container').first().find('.pac-item-query').first().text();
                    firstA += ' ' + $('.pac-container').first().find('.pac-item-query').first().next().text();

                    $('#address').val(firstA);

                    request = {
                        query: firstA,
                        fields: ['geometry']
                    }

                    placeService = new google.maps.places.PlacesService(map);

                    placeService.findPlaceFromQuery(request, function(results, status){
                        if (status == google.maps.places.PlacesServiceStatus.OK){
                            if(results && results[0]){
                                lat = results[0].geometry.location.lat();
                                lng = results[0].geometry.location.lng();

                                geocodeLatLng(lat, lng, geocoder, map, marker, true);
                            }
                        }
                    });
                    e.preventDefault();
                }
            });

            $('#lon').on('blur', function(e){
                if($('#lon').val().trim() != '' && $('#lat').val().trim() != '' && !isNaN(parseFloat($('#lat').val())) && !isNaN(parseFloat($('#lon').val()))){
                    geocodeLatLng(parseFloat($('#lat').val()), parseFloat($('#lon').val()), geocoder, map, marker, true);
                }
            });

            $('#lat').on('blur', function(e){
                if($('#lon').val().trim() != '' && $('#lat').val().trim() != '' && !isNaN(parseFloat($('#lat').val())) && !isNaN(parseFloat($('#lon').val()))){
                    geocodeLatLng(parseFloat($('#lat').val()), parseFloat($('#lon').val()), geocoder, map, marker, true);
                }
            });
        }

        function centerMap(map, marker, lat, lng){
            if(lat != '' && lng != '' && !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng))){
                var latlng = {lat: parseFloat(lat), lng: parseFloat(lng)};

                map.setCenter(latlng);
                marker.setPosition(latlng);
                map.setZoom(16);

                return true;
            }
            return false;
        }

        function geocodeLatLng(lat,lng, geocoder, map, marker, ban) {
            var latlng = {lat:lat, lng: lng};

            geocoder.geocode({'location': latlng}, function(results, status) {
                $('#preloader').hide();
                if (status === 'OK') {
                    if (results[0]){
                        if(ban){
                            $('#address').val(results[0].formatted_address);
                            $('#lon').val(lng),
                            $('#lat').val(lat);

                            $('#btnGeo').text('Consultar');
                            $("#divPack").html('');
                        }
                        centerMap(map, marker, lat, lng);
                    }else{
                        showMessageAjax('alert-danger','No se encontro la dirección del punto marcado.');
                    }
                }else{
                    showMessageAjax('alert-danger','Ocurrio un error cargando la dirección, por favor intente mas tarde.');
                }
            });
        }
</script>
@endif

    @if($client->status)
<script type="text/javascript">
  let getBtnSelect = function(selector){
        return document.querySelectorAll(selector);
    }

    let activeBtn = function(listBtn, btnMH = false, btnPayment = false, btnPort = false){
       listBtn.forEach(btnNode => {
        btnNode.onclick = () => {
            listBtn.forEach( btnNode =>{
                btnNode.classList.remove('btn-danger');
            });
            btnNode.classList.add('btn-danger');

            if(btnMH){
                btnMH.forEach(btnNode1 => {
                    btnNode1.classList.remove('btn-danger');
                });
           }
           if(btnPayment){
                btnPayment.forEach(btnNode2 => {
                    btnNode2.classList.remove('btn-danger');
                });
           }
           if(btnPort){
                btnPort.forEach(btnNode3 => {
                    btnNode3.classList.remove('btn-danger');
                });
           }
        }
        });
    }
    //Primer nivel menu
    const btnProduct = getBtnSelect('#content-product button');
    //Segundo nivel menu
    const btnMH = getBtnSelect('#val-content-mifi button');
    //Segundo nivel menu
    const btnPayment = getBtnSelect('#type-payment-content button');
    //tercer o cuarto nivel menu
    const btnPort = getBtnSelect('#port-content button');

    activeBtn(btnProduct, btnMH, btnPayment, btnPort);
    activeBtn(btnMH, false, false, btnPort);
    activeBtn(btnPayment, false, false, btnPort);
    activeBtn(btnPort);

   $('#alert-comp').hide();
   $(function () {
       function handleError(err) {
           $('.loading-ajax').hide();
           showMessageAjax('alert-danger', 'No se pudieron obtener automáticamente las coordenadas.');
       };

       let callPacks = function(lat, lon, address, type, isPort = false, isBandTE = false, dnport = ''){

           var url = '';
           var data = {
                   _token: "{{ csrf_token() }}",
                   isPort: isPort,
                   dnport: dnport
               };

           if(type == 'home' || type == 'mifi-h'){
               url = "{{route('seller.showPacks')}}";
               data.lat = lat;
               data.lon = lon;
               data.address = address;
           }

           if(type == 'mov'){
               url = "{{route('seller.showPackMov')}}";
               data.isBandTE = isBandTE;
               @if(hasPermit('SEL-TLP'))
                  if($('#type-payment').val() === 'telmovpay'){
                    data.isOptionTelmov = true;
                    data.ine = $('#client').val();
                  }
                  // data.brand=$('#brand-mov').val();
               @endif
           }

           if(type == 'mifi'){
               url = "{{route('seller.showPackMov')}}";
           }

           data.type = $('#type-sell').val();

           if($('#type-payment').length && $('#type-payment-content').is(':visible')){
               data.typePayment = $('#type-payment').val();
           }

          if(url != ''){
           $.ajax({
             type: 'POST',
             url: url,
             data: data,
             dataType: "json",
             success: function(paks){
               if(paks.error && paks.message == 'TOKEN_EXPIRED'){
                   showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
               }else{
                 if(paks.success){
                    $("#divPack").html(paks.html);
                    if($('#type-payment').val() === 'telmovpay'){
                      $('.refered-container').removeClass('d-none');
                    }
                 }else{
                     showMessageAjax('alert-danger','Ocurrio un error.');
                 }
               }
               $('.loading-ajax').hide();
             },
             error: function(){
                 showMessageAjax('alert-danger','Ocurrio un error.');
                 $('.loading-ajax').hide();
             }
           });
          }else{
               showMessageAjax('alert-danger','Ocurrio un error.');
          }
       }

       $('#btnGeo').on('click', function(event){
           $('.loading-ajax').show();

           var lon = $('#lon').val().trim(),
               lat = $('#lat').val().trim();
           if(lon != '', lat != ''){
               callPacks(lat,lon,"{{$client->address}}", $('#type-sell').val(), false, false);
           }else{
               if (navigator.geolocation){
                   navigator.geolocation.getCurrentPosition(function (funcExito){
                       var lon = funcExito.coords.longitude;
                       var lat = funcExito.coords.latitude;
                       $('#lat').val(lat);
                       $('#lon').val(lon);

                       @if(hasPermit('ECO-DSE'))
                       geocodeLatLng(parseFloat($('#lat').val()), parseFloat($('#lon').val()), geocoder, map, marker, true);
                       @endif

                       // cargando packs
                       $("#divPack").html('');
                       callPacks(lat,lon,"{{$client->address}}", $('#type-sell').val(), false, false);
                   },handleError, {maximumAge:0, timeout:5000});
               }else{
                   $('.loading-ajax').hide();
                   showMessageAjax('alert-danger','No se puede obtener la Geolocalización.');
               }
           }
       });

       @if(hasPermit('SEL-MOV') || hasPermit('SEL-MIF'))
       let validQtyDns = function(res){
           if(!res.error){
               if($('#type-sell').val() == 'mov'){
                   $('.loading-ajax').fadeOut();
                   $('#val-content-phone').attr('hidden', null);
               }

               if($('#type-sell').val() == 'home' || $('#type-sell').val() == 'mifi-h'){
                   $('.loading-ajax').fadeOut();
                   $('#geo-content').removeAttr('hidden');
               }

               if($('#type-sell').val() == 'mov-ph'){
                   $('.loading-ajax').fadeOut();
                   //$('#port-content').attr('hidden', null);
                   $('#type-payment-content').attr('hidden', null);
               }

               if($('#type-sell').val() == 'mifi'){
                   callPacks(0,0,"{{$client->address}}", 'mifi', false, false);
               }

           }else{
               $('.loading-ajax').fadeOut();

               if(res.message == 'TOKEN_EXPIRED'){
                 showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
               }else{
                 showMessageAjax('alert-danger', 'El cliente tiene el límite de dn´s permitidos.');
               }
           }
       }

       @if(hasPermit('SEL-MIF'))
       $('#type-mifi').on('click', function(e){
           resetFormPort();
           @if(hasPermit('SEL-TLP'))
              $("#infoPhone").html('');
           @endif
           $('#imei').val('');
           $('#form-port-content').attr('hidden', true);
           $('#port-content').attr('hidden', true);
           $('#type-payment-content').attr('hidden', true);
           $('#geo-content').attr('hidden', true);
           $('#val-content-phone').attr('hidden', true);
           $("#divPack").html('');
           $('#val-content-mifi').attr('hidden', null);
       });

       $('#type-mifi-n').on('click', function(e){
           $('#type-sell').val('mifi');
           $("#divPack").html('');
            @if(hasPermit('SEL-TLP'))
              $("#infoPhone").html('');
           @endif
            $('#geo-content').attr('hidden', true);

           $('.loading-ajax').show();
           doPostAjax(
               "{{ route('seller.validQtyDns', ['type' => 'mifi']) }}",
               validQtyDns,
               {ine: $('#client').val(), _token: '{{ csrf_token() }}'}
           );
       });

       $('#type-mifi-h').on('click', function(e){
           $('#type-sell').val('mifi-h');
           $("#divPack").html('');
           @if(hasPermit('SEL-TLP'))
              $("#infoPhone").html('');
           @endif
           $('.loading-ajax').show();
           doPostAjax(
               "{{ route('seller.validQtyDns', ['type' => 'mifi-h']) }}",
               validQtyDns,
               {ine: $('#client').val(), _token: '{{ csrf_token() }}'}
           );
       });
       @endif

       $('#type-home').on('click', function(e){
           $('.container-btn-type').data('val','home');
           resetFormPort();
           $('#type-sell').val('home');
           $("#divPack").html('');
           $('#val-content-phone').attr('hidden', true);
           @if(hasPermit('SEL-TLP'))
              $("#infoPhone").html('');
           @endif
           $('#port-content').attr('hidden', true);
           $('#form-port-content').attr('hidden', true);
           $('#type-payment-content').attr('hidden', true);
           $('#val-content-mifi').attr('hidden', true);

           $('.loading-ajax').show();
           doPostAjax(
               "{{ route('seller.validQtyDns', ['type' => 'home']) }}",
               validQtyDns,
               {ine: $('#client').val(), _token: '{{ csrf_token() }}'}
           );
       });

       @if(hasPermit('SEL-MOV'))
       $('#type-mov').on('click', function(e){
           $('.container-btn-type').data('val','mov');
           resetFormPort();
           $('#type-sell').val('mov');
           $("#divPack").html('');
            @if(hasPermit('SEL-TLP'))
              $("#infoPhone").html('');
           @endif

           $('#geo-content').attr('hidden', true);
           $('#port-content').attr('hidden', true);
           $('#form-port-content').attr('hidden', true);
           $('#type-payment-content').attr('hidden', true);
           $('#val-content-mifi').attr('hidden', true);
           $('#imei').val('');

           $('.loading-ajax').show();
           doPostAjax(
               "{{ route('seller.validQtyDns', ['type' => 'mov']) }}",
               validQtyDns,
               {ine: $('#client').val(), _token: '{{ csrf_token() }}'}
           );
       });

       $('#type-mov-ph').on('click', function(e){
           $('.container-btn-type').data('val','mov-ph');
           resetFormPort();
           $('#type-sell').val('mov-ph');
           $("#divPack").html('');
          @if(hasPermit('SEL-TLP'))
              $("#infoPhone").html('');
           @endif
           $('#geo-content').attr('hidden', true);
           $('#port-content').attr('hidden', true);
           $('#form-port-content').attr('hidden', true);
           $('#val-content-phone').attr('hidden', true);
           $('#val-content-mifi').attr('hidden', true);
           $('#imei').val('');

           $('.loading-ajax').show();
           doPostAjax(
               "{{ route('seller.validQtyDns', ['type' => 'mov']) }}",
               validQtyDns,
               {ine: $('#client').val(), _token: '{{ csrf_token() }}'}
           );
       });

      @if(hasPermit('SEL-TLP'))
      let chekingIdentiTelmov = function(res){
         $('.loading-ajax').fadeOut();
         if(!res.error){
            if(res.success){
              if(res.html.length>0){
                $('#infoPhone').html(res.html);
                if(res.viewPort){
                  $('#port-content').attr('hidden', null);
                }else{
                  $('#port-content').attr('hidden', true);
                  callPacks(0,0,"{{$client->address}}", 'mov', false, $("#is-band-te").val());
                }
              }else{
                showMessageAjax('alert-danger', res.msg);
              }
            }else{
               showMessageAjax('alert-danger', res.msg);
            }
         }else{
            if(res.message == 'TOKEN_EXPIRED'){
               showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
            }else{
               $('#alert-comp').removeClass('alert-success');
               $('#alert-comp').removeClass('alert-warning');
               $('#alert-comp').addClass('alert-danger');
               $('#alert-comp').text('No se puede continuar');
               $('#alert-comp').show();
               //showMessageAjax('alert-danger', 'Teléfono no compatible.');
             }
         }
      }
      @endif

       $('.btn-tp').on('click', function(e){
           let type = $(this).data('type');
           $('#type-payment').val(type);
           resetFormPort();
           $("#divPack").html('');
            @if(hasPermit('SEL-TLP'))
              $("#infoPhone").html('');
           @endif
            if(type==='telmovpay'){
               @if(hasPermit('SEL-TLP'))
               //Verificamos que no tenga un proceso abierto con telmovPay
               //
               doPostAjax(
               "{{ route('seller.chekingIdentiTelmov') }}",
               chekingIdentiTelmov,
               {
               ine: $('#client').val(),
               _token: '{{ csrf_token() }}'
               });
               @endif
           }else{
               $('#port-content').attr('hidden', null);
           }
       });

       let validImei = function(res){
           $('.loading-ajax').fadeOut();
           $("#is-band-te").val(false);
           if(!res.error){
               if(res.data.band28 == 'NO'){
                   $('#alert-comp').removeClass('alert-danger');
                   $('#alert-comp').removeClass('alert-success');
                   $('#alert-comp').addClass('alert-warning');

                   $('#alert-comp').text('Equipo no es compatible con Banda 28');
                   $("#is-band-te").val('N');
               }
               else{
                   $('#alert-comp').removeClass('alert-danger');
                   $('#alert-comp').removeClass('alert-warning');
                   $('#alert-comp').addClass('alert-success');

                   if(res.data.band28 == 'SI'){
                       $("#is-band-te").val('Y');
                   }
                   if(res.data.volteCapable == 'no'){
                       $('#alert-comp').text('Equipo compatible con VozApp');
                   }else{
                       $('#alert-comp').text('Equipo es compatible con la red VoLTE');
                   }
               }
               $('#alert-comp').show();
               //Quitar lo siguiente cuando se habilite portabilidad
               //$('#wto-port').trigger('click');
               //Quitar comentario de abajo cuando se habilite portabilidad
               $('#port-content').attr('hidden', null);
           }else{
             if(res.message == 'TOKEN_EXPIRED'){
                 showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
             }else{
               $('#alert-comp').removeClass('alert-success');
               $('#alert-comp').removeClass('alert-warning');
               $('#alert-comp').addClass('alert-danger');
               $('#alert-comp').text('Equipo no compatible');
               $('#alert-comp').show();
               //showMessageAjax('alert-danger', 'Teléfono no compatible.');
             }
           }
       }

       $('#valid-imei').on('click', function(e){
           let imei = $('#imei').val();

           $('#imei-error').text('');
           $("#divPack").html('');

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

       $('#wto-port').on('click', function(e){
           $('.loading-ajax').show();
           resetFormPort();
           $("#divPack").html('');
           $('#form-port-content').attr('hidden', true);
           callPacks(0,0,"{{$client->address}}", 'mov', false, $("#is-band-te").val());
       });

      $('#wt-port').on('click', function(e){
          $("#divPack").html('');
          $('#form-port-content').attr('hidden', null);
      });

         $('#btn-form-port').on('click', function(e){
            valid = fromValidPort();
            if(!valid){
               showMessageAjax('alert-danger', 'Por favor revisa los datos para la portabilidad.');
            }else{
               $('.loading-ajax').show();
               //resetFormPort();
               callPacks(0,0,"{{$client->address}}", 'mov', true, $("#is-band-te").val(), $('#dn_port').val());
            }
          });
      @endif

       let resetFormPort = function(){
           $('#nip').val('');
           $('#nip').parent().parent().removeClass('has-error');
           $('#nip').siblings('div').text('');

           $('#dn_port').val('');
           $('#dn_port').parent().parent().removeClass('has-error');
           $('#dn_port').siblings('div').text('Número de 10 dígitos que se quiere portar');

           $('#dn_port2').val('');
           $('#dn_port2').parent().parent().removeClass('has-error');
           $('#dn_port2').siblings('div').text('Ingresa nuevamente el número a portar');

           $('#port-prov').val($('#port-prov option:first').val())
           $('#port-prov').parent().parent().removeClass('has-error');
           $('#port-prov').siblings('div').text('');

           {{--db.data('dropify').clearElement();
           $('#dni-back').parent().removeClass('has-error');
           $('#dni-back').siblings('.dropify-message').text('Click o arrastre una imágen para subirla');

           df.data('dropify').clearElement();
           $('#dni-front').parent().removeClass('has-error');
           $('#dni-front').siblings('.dropify-message').text('Click o arrastre una imágen para subirla');--}}
       }

       {{--let messagesDF = {
           default: 'Click o arrastre una imágen para subirla',
           replace: 'Reemplazar imágen',
           remove: 'Borrar',
           error: 'Error al cargar imágen'
       };

       var df = $('#dni-front').dropify({
           messages: messagesDF
       });

       var db = $('#dni-back').dropify({
           messages: messagesDF
       });--}}

       @endif

       @if(hasPermit('ECO-DSE'))
       initPlaces();
       @endif
   });
</script>
@endif
@stop
