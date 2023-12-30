<div class="block-plan p-l-0 col-sm-12 col-md-12">
    <input type="hidden" name="serviciability" id="serviciability" value="NO_OK">
    <input type="hidden" name="type-sell" id="type-sell" value="{{ $data['artic']->artic_type }}">
    <div class="col-sm-12 col-md-6">
        <label>Descripción del plan:</label>
        <p>{{$data['packs'][0]->description}}</p>
    </div>
    <div class="col-sm-12 col-md-6">
        <label>Servicio:</label>
        <p>{{$data['services'][0]->title}}</p>
    </div>
    <div class="col-sm-12 col-md-6">
        <label>Descripción del servicio:</label>
        <p>{{$data['services'][0]->description}}</p>
    </div>
    <div class="col-sm-12 col-md-6">
        <label>Precio del plan:</label>
        <p>$ {{$data['services'][0]->price_pack + $data['services'][0]->price_serv}}</p>
    </div>

    <div class="col-sm-12 col-md-6">
        <label>MSISDN:</label>
        <p>{{$data['artic']->msisdn}}</p>
    </div>

    <div class="col-sm-12 col-md-6">
        <label>Articulo:</label>
        <p>{{$data['artic']->title}}</p>
    </div>
    <div class="col-sm-12 col-md-6">
        <label>ICCID:</label>
        <p>{{$data['artic']->iccid}}</p>
    </div>
    <div class="col-sm-12 col-md-6">
        <label>Imei:</label>
        <p>{{$data['artic']->imei}}</p>
    </div>
    
    @if(hasPermit('MAP-DSE') && $data['artic']->artic_type == 'H')
    <div class="col-md-12 col-sm-12" @if($address === false) hidden @endif>
        <label>Dirección:</label>
        
        <input type="text" class="form-control form-control-sm" id="address" name="address" placeholder="Introduce la ubicación donde estara el Netwey*" @if($address !== false &&  $address !== true) value="{{$address}}" hidden @endif>
        
        @if($address !== false &&  $address !== true)
            <p>{{$address}}</p>
        @endif
    </div>

    <div class="col-md-12 p-t-20" @if($address !== true) hidden @endif>
        <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
            <label>Latitud:</label>
            <input class="form-control" id="lat" name="lat" type="text" @if(!empty($lat)) value="{{$lat}}" @endif>
        </div>
        <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
            <label>longitud:</label>
            <input class="form-control" id="lon" name="lon" type="text" @if(!empty($lng)) value="{{$lng}}" @endif>
        </div>
    </div>

    <div id="map" class="col-md-12 col-sm-12 map-container" style="height: 50vh;min-height: 300px; padding-top: 20px;" @if($address !== true) hidden @endif>
        <div id="map-content" style="width: 100%;height: 100%;"></div>
    </div>
    @endif
</div>

@if($address === true && hasPermit('MAP-DSE') && $data['artic']->artic_type == 'H')
{{-- <script src="{{ asset('https://maps.googleapis.com/maps/api/js?key='.env('GOOGLE_KEY').'&libraries=places&callback=initPlaces') }}"></script> --}}

<script type="text/javascript">
    function initPlaces(){
        var input = document.getElementById('address'),
            autocomplete = new google.maps.places.Autocomplete(input),
            geocoder = new google.maps.Geocoder,
            lat = 19.39068, 
            lng = -99.2836963;

        var center = new google.maps.LatLng(lat, lng),
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

    function geocodeLatLng(lat,lng, geocoder, map, marker, ban) {
        var latlng = {lat:lat, lng: lng};

        geocoder.geocode({'location': latlng}, function(results, status) {
            $('#preloader').hide();
            if (status === 'OK') {
                if (results[0]){
                    if(ban){
                        $('#address').val(results[0].formatted_address);
                        map.setZoom(16);
                        $('#lon').val(lng),
                        $('#lat').val(lat);
                    }

                    map.setCenter(latlng);
                    marker.setPosition(latlng);

                    $('#map').attr('hidden', null);
                }else{
                    showMessageAjax('alert-danger','No se encontro la dirección del punto marcado.');
                }
            }else{
                showMessageAjax('alert-danger','Ocurrio un error cargando la dirección, por favor intente mas tarde.');
                console.log(status);
            }
        });
    }

    initPlaces();
</script>
@endif