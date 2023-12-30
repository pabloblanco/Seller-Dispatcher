<label class="col-md-12">
  Zona de conexión(OLT)
</label>
@if(count($olts))
<select class="form-control" id="olt" name="olt" placeholder="Seleccione una OLT" required="">
  <option value="">
    Seleccione una OLT
  </option>
  @foreach($olts as $olt)
  <option value="{{ $olt->zone_id }}" data-installer="{{$olt->installer}}">
    {{ $olt->name_zone }}
  </option>
  @endforeach
</select>
<div class="help-block with-errors">
</div>
@else
<div class="alert alert-danger">
  <p>
    Falló el listado de OLT's o no hay puntos activos en la ciudad seleccionada que se puedan mostrar.
  </p>
</div>
@endif

<script type="text/javascript">

  //Funciones para manejo del mapa
    //
    var temp_lat  = 19.39068;
    var temp_lng  = -99.2836963;
    var temp_zoom = 5;
    var FiberZonePolygon = null;

    let setPoligono = function(dataPoligono, point_center){

      if(dataPoligono !== null && point_center !== null){
        poligono = dataPoligono['poligono'];
        temp_lat = point_center['lat'];
        temp_lng = point_center['lng'];
        temp_zoom = dataPoligono['zoom'];

        if(FiberZonePolygon !== null ){
          //reseteo area
          FiberZonePolygon.setMap(null);
        }
        // Construye el polígono.
         FiberZonePolygon = new google.maps.Polygon({
            paths: poligono,
            strokeColor: '#82954B',
            strokeOpacity: 0.8,
            strokeWeight: 3,
            fillColor: '#BABD42',
            fillOpacity: 0.4,
            clickable: false
        });
      }
    }

    var dataPoligono, point_center;
    let map, marker, center, geocoder;

    let eventoMapa = function(latLng, geocoder){
      //console.log(latLng);
      marker.setPosition(latLng);
      map.panTo(latLng);

      lat = marker.getPosition().lat();
      lng = marker.getPosition().lng();

      geocodeLatLng(lat, lng, geocoder, map, marker, true);
    }

    let initPlaces = function(dataPolygon, point_init){
      dataPoligono = dataPolygon;
      point_center = point_init;
      setPoligono(dataPoligono, point_center);

      geocoder = new google.maps.Geocoder;
      lat = temp_lat;
      lng = temp_lng;

      center = new google.maps.LatLng(lat, lng);

      map = new google.maps.Map(document.getElementById('map-content'), {
                    center,
                    zoom: temp_zoom
      });

      if(FiberZonePolygon !== null ){
        FiberZonePolygon.setMap(map);
      }

      /*const image = {
        url: "{asset('images/coberturaFibra.png')}}",
        // This marker is 20 pixels wide by 32 pixels high.
        size: new google.maps.Size(40, 35),
        // The origin for this image is (0, 0).
        origin: new google.maps.Point(0, 0),
        // The anchor for this image is the base of the flagpole at (0, 32).
        anchor: new google.maps.Point(0, 35),
      };*/

      marker = new google.maps.Marker({
        map: map,
        draggable: true,
       // icon: image,
        animation: google.maps.Animation.DROP,
        position: {lat: lat, lng: lng}
      });

      map.addListener('click', function(e) {
        eventoMapa(e.latLng, geocoder);
      });

      marker.addListener('dragend', function(event) {
        eventoMapa(event.latLng, geocoder);
      });

      marker.addListener('dragend', function (event){
          lat = marker.getPosition().lat();
          lng = marker.getPosition().lng();

          geocodeLatLng(lat, lng, geocoder, map, marker, true);
      });

      $('.lon-map').on('blur', function(e){
        if($('.lon-map').val().trim() != '' && $('.lat-map').val().trim() != '' && !isNaN(parseFloat($('.lat-map').val())) && !isNaN(parseFloat($('.lon-map').val()))){
          geocodeLatLng(parseFloat($('.lat-map').val()), parseFloat($('.lon-map').val()), geocoder, map, marker, true);
        }
      });

      $('.lat-map').on('blur', function(e){
        if($('.lon-map').val().trim() != '' && $('.lat-map').val().trim() != '' && !isNaN(parseFloat($('.lat-map').val())) && !isNaN(parseFloat($('.lon-map').val()))){
          geocodeLatLng(parseFloat($('.lat-map').val()), parseFloat($('.lon-map').val()), geocoder, map, marker, true);
        }
      });
    }

    function getAddressApiGoogle(lat, lng, geocoder, initial = false) {
      var latlng = {
        lat: lat,
        lng: lng
      };
      geocoder.geocode({
        'location': latlng
      }, function(results, status) {
        if (status === 'OK') {
          if (results[0]) {
            if (!initial) {
              $('#locality').val(results[0].formatted_address);
              $("#locality2").val(results[0].formatted_address);
              /*
               $("#lat").val(lat);
              $("#lng").val(lng);
              for (var i = 0; i < results[0].address_components.length; i++) {
                var addressType = results[0].address_components[i].types[0];
                if (addressType == 'locality') {
                  colony = results[0].address_components[i]['short_name'];
                }
                if (addressType == 'route') {
                  street = results[0].address_components[i]['short_name'];
                }
              }
              $("#colony").val(colony);
              $("#route").val(street);
              */
            }
          } else {
            showMessageAjax('alert-danger','No se encontro la dirección del punto marcado.');
          }
        } else {
          showMessageAjax('alert-danger','Ocurrio un error cargando la dirección, por favor intente mas tarde.');
          console.log(status);
        }
      });
    }

    function getCoverageFiber(lat, lng, idCity=false, idOlt=false){
      $('#lat_OK').val(lat);
      $('#lng_OK').val(lng);
      $('.lat-map').val(lat);
      $('.lon-map').val(lng);

      return new Promise((resolve, reject) => {

      if(!idCity){
        idCity=$('#city').val();
      }
      if(!idOlt){
        idOlt=$('#olt').val();
      }

      resp = {"success":false,"msg":'Hubo un problema para obtener la servicialidad de fibra'};

      doPostAjax(
        '{{ route('sellerFiber.chekingCoverageFiber') }}',
        function(res){
          if($('.loading-ajax').is(':visible')){
            $('.loading-ajax').fadeOut();
          }
            //console.log('2- ',res);
          if(res.success){
            resp = {"success":true,"msg":res.code};
                  return resolve(resp);
          }else{
            if(res.message == 'TOKEN_EXPIRED'){
              showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
            }else{
              resp = {"success":false,"msg":res.msg};
                    return resolve(resp);
            }
          }
        },
        {
          lat: lat,
          lng: lng,
          city_id: idCity,
          zone_id: idOlt
        },
        $('meta[name="csrf-token"]').attr('content')
      );
      });
    }

    function searchInfoAddress(lat, lng){

      doPostAjax(
        '{{ route('sellerFiber.getCompAddress') }}',
        function(res){
          if($('.loading-ajax').is(':visible')){
            $('.loading-ajax').fadeOut();
          }

          if(!res.error){
           // $('#state').val(res.data.state ? res.data.state : '');
            $('#muni').val(res.data.municipality ? res.data.municipality : '');
            $('#colony').val(res.data.colony ? res.data.colony : '');
             $('#route').val(res.data.route ? res.data.route : '');
            $('#numberhouse').val("");
            $('#reference').val("");

            $('#blockAddressExtra').removeClass('d-none');

          }else{
            if(res.message == 'TOKEN_EXPIRED'){
              showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
            }else{
              showMessageAjax('alert-danger', res.msg);
            }
          }
        },
        {lat: lat, lng: lng},
        $('meta[name="csrf-token"]').attr('content')
      );
    }

    function promesaTheCoverage(lat, lng){

      if($('#typeAddress').is(':checked')){
       // console.log('opcion de direccion');
        getAddressApiGoogle(lat, lng, geocoder, false);
      }else{
        //console.log('opcion de coordenada');
      }

      let isCoverage = getCoverageFiber(lat, lng);
        isCoverage.then(function(promesa_coverage) {

        //console.log('1- ',promesa_coverage);

        if(promesa_coverage['success']){
          showMessageAjax('alert-info', 'Cobertura de fibra OK!');
          searchInfoAddress(lat,lng);

        }else{
          if($('.loading-ajax').is(':visible')){
            $('.loading-ajax').fadeOut();
          }
          //$('#blockAddressExtra').addClass('d-none');
          showMessageAjax('alert-warning', promesa_coverage.msg);
          ResetViewFiberCite('offcoverage');
        }
        return true;
      });
    }

    function centerMap(map, marker, lat, lng){
      if(lat != '' && lng != '' && !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng))){

        setPointMap(lat, lng);
        setPoligono(dataPoligono, point_center);
        if(FiberZonePolygon !== null ){
          FiberZonePolygon.setMap(map);
        }

        if(!$('.loading-ajax').is(':visible')){
          $('.loading-ajax').show();
        }
        //Revision de cobertura
        //
        return promesaTheCoverage(lat,lng);
        //end Revision de cobertura
      }
      return false;
    }

    let geocodeLatLng = function(lat,lng, geocoder, map, marker, ban) {
      centerMap(map, marker, lat, lng);
    }

    let handleError = function(err) {
        $('.loading-ajax').hide();
        showMessageAjax('alert-danger', 'No se puede obtener la Geolocalización, intenta dar permisos al navegador.');
        console.warn('ERROR(' + err.code + '): ' + err.message);
    };

    let getGeoPosition = function(lat, lon, btn){
      $('.loading-ajax').show();

      /*if(lon != '', lat != ''){
        centerMap(map, marker, lat, lon);
      }else{*/
        // HTML5 geolocation.
        if (navigator.geolocation){
          navigator.geolocation.getCurrentPosition(function (funcExito){
              var lon = funcExito.coords.longitude;
              var lat = funcExito.coords.latitude;
               // $('.lat-'+btn).val(lat);
              //  $('.lon-'+btn).val(lon);
               // $('#lat_OK').val(lat);
               // $('#lng_OK').val(lon);
                centerMap(map, marker, lat, lon);
              }, function() {
                $('.loading-ajax').hide();
                showMessageAjax('alert-danger', 'Servicio de Geolocation fallo. Verifique que este habilitada la geolozalicion en su navegador');
              });
            }else{
              $('.loading-ajax').hide();
              showMessageAjax('alert-danger', 'Tu navegador no soporta geolocation.');
            }
        /*}*/
    }
    //Ingreso de texto para la direccion
    //

    function setPointMap(lat, lng, zoom=16){
      let latlng = {
        lat: parseFloat(lat),
        lng: parseFloat(lng)
      };

      map.setCenter(latlng);
      marker.setPosition(latlng);
      map.setZoom(zoom);
    }

    function renderMap(place) {
      var lat = place.geometry.location.lat();
      var lng = place.geometry.location.lng();
      var placeId = place.place_id;
      var ubicacion = $("#locality").val();
      var postalCode = "";
      $("#locality2").val(ubicacion);
      //$("#lat_OK").val(lat);
     // $("#lng_OK").val(lng);
      //var geocoder = new google.maps.Geocoder;


        //Revision de cobertura
        //
        promesaTheCoverage(lat,lng);
        //end Revision de cobertura
        //
      setPointMap(lat, lng);

      //const center = new google.maps.LatLng(lat, lng);
      /*const map = new google.maps.Map(document.getElementById('map'), {
        center,
        zoom: 16
      });*/
      /*marker = new google.maps.Marker({
        map: map,
        draggable: true,
        animation: google.maps.Animation.DROP,
        position: {
          lat: lat,
          lng: lng
        }
      });
      //no es necesario
      */
      map.addListener('click', function(e) {
        eventoMapa(e.latLng, geocoder);
      });
      marker.addListener('dragend', function(event) {
        lat = marker.getPosition().lat();
        lng = marker.getPosition().lng();

        geocodeLatLng(lat, lng, geocoder, map, marker);
      });

    }
    function initializeAutocomplete() {
      var input = document.getElementById('locality');
      var options = {}
      var markers = [];
      var autocomplete = new google.maps.places.Autocomplete(input);
      google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var place = autocomplete.getPlace();
        if (!place.geometry) {

         /* const center = new google.maps.LatLng(0, 0);
          const map = new google.maps.Map(document.getElementById('contenedor_mapa'), {
            center,
            zoom: 16
          });
          //no es necesario
          */
          const placeService = new google.maps.places.PlacesService(map);
          placeName = $('.pac-container').first().find('.pac-item-query').first().text() + " " + $('.pac-container').first().find('.pac-item-query').first().next().text();
          const request = {
            query: placeName,
            fields: ['place_id', 'name', 'formatted_address', 'icon', 'geometry'],
          }
          placeService.findPlaceFromQuery(request, (results, status) => {

            if (status == google.maps.places.PlacesServiceStatus.OK) {
              $("#locality").val(placeName);
              renderMap(results[0]);
            } else {
              $("#locality2").val('');
              $("#lat").val('');
              $("#lng").val('');
              $("#street").val('');
              return;
            }
          })
          return;
        }
        renderMap(place);
      });
    }

    //Fin de funciones para el manejo del mapa
    //
    //Consulta coordenadas del navegador

   // $('#btnGeo').unbind('change');
    $('#btnGeo').on('click', function(event){
          let btn = $(event.currentTarget).data('btn');
          lon = $('.lon-'+btn).val().trim();
          lat = $('.lat-'+btn).val().trim();
          getGeoPosition(lat, lon, btn);
    });

    $('#validGeo').on('click', function(event){
      if($('.lat-map').val()!=='' && $('.lon-map').val()!==''){
          centerMap(map, marker, $('.lat-map').val(), $('.lon-map').val());
      }else{
          showMessageAjax('alert-danger', 'Se debe escribir las coordenas a consultar');
      }
    });

    //End Consulta coordenadas del navegador
    //

function listPlan(type = "init"){
  if(!$('.loading-ajax').is(':visible')){
    $('.loading-ajax').show();
  }
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': CSRF_TOKEN
    },
    url: '{{ route('sellerFiber.getPlanes') }}',
    type: 'POST',
    dataType: 'json',
    data: {
        type: type,
        oltid: $('#olt').val(),
        dni: $('#dni').val(),
        packforcer: (typeof $('#typePlan').val() === 'undefined')? '': ($('#typePlan').is(':checked'))?'Y':'N',
        packSuscrip: (typeof $('#typePlan_suscrip').val() === 'undefined')? '': ($('#typePlan_suscrip').is(':checked'))?'Y':'N',
        packBundler: (typeof $('#typePlan_bundle').val() === 'undefined')? '': ($('#typePlan_bundle').is(':checked'))?'Y':'N',
      },
    error: function() {
      $('.loading-ajax').fadeOut();
      showMessageAjax('alert-danger', 'Ocurrio un error al obtener la lista de planes de fibra.');
    },
    success: function(dataplan) {
      $('.loading-ajax').fadeOut();
      if (!dataplan.error) {
        if (dataplan.success) {
          if(type==="init"){
            $('#blockPlan').html(dataplan.msg);
          }else{
            $('#item_select_packs').html(dataplan.msg);
            $('#block_number_identi').html(dataplan.htmlDocument);
          }
        }else{
          showMessageAjax('alert-danger', dataplan.message);
        }
      } else {
        if (dataplan.message == 'TOKEN_EXPIRED') {
          showMessageAjax('alert-danger', 'Su session a expirado, por favor actualice la página.');
        } else {
          showMessageAjax('alert-danger', dataplan.message);
        }
      }
    }
  });
}

$('#olt').change(function (e) {
  e.preventDefault();
  ResetViewFiberCite('olt');
  if($(this).val()!==''){

    let info = $('#olt option:selected').data('installer');
    if(info == 'Y'){
      $('.loading-ajax').show();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        url: '{{ route('sellerFiber.getMapCoverage') }}',
        type: 'POST',
        dataType: 'json',
        data: {
            oltid: $('#olt').val(),
            cityid: $('#city').val()
          },
        error: function() {
          $('.loading-ajax').fadeOut();
          showMessageAjax('alert-danger', 'Ocurrio un error al obtener el mapa de fibra.');
        },
        success: function(data) {
          if (!data.error) {
            if (data.success) {
              $('#blockTipeAddress').html(data.inputAddress);
              //poligono map
              //Point center map
              if(data.dataPoligono !== null){
                //Inicializando mapa
                initPlaces(data.dataPoligono, data.point_center);
                $('#blockMap').removeClass('d-none');
                $('#typeAddress').bootstrapToggle();
                $('#blockGPS').removeClass('d-none');
                listPlan();

              }else{
                $('.loading-ajax').fadeOut();
                showMessageAjax('alert-danger', "No se pudo obtener el area de cobertura de la Olt '"+$('#olt option:selected').text().trim()+"', verifica que este cargado e intenta nuevamente");
                $('#olt').val('');
                $('#olt').data('selectize').setValue('');
              }
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
      showMessageAjax('alert-danger', "No hay personal asignado que pueda procesar la solicitud en la zona");
    }
  }else{
      ResetViewFiberCite('olt');
  }
});
</script>
