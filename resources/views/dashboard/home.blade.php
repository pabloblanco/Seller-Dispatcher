{{-- DEPRECATED --}}
@extends('layouts.admin')

@section('content')
    @include('components.messages')
    
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"> Dashboard </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active">Home</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    <h3 class="box-title">Serviciabilidad por dirección.</h3>
                    
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm" id="address" name="address" placeholder="Escribe la dirección donde estara el Netwey*">

                        <div id="map" class="col-md-12 col-sm-12 map-container" style="height: 50vh;min-height: 300px; padding-top: 20px;">

                            <div id="map-content" style="width: 100%;height: 100%;"></div>

                        </div>
                    </div>

                    <div class="col-md-12 p-t-20">
                        <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
                            <label>Latitud:</label>
                            <input class="form-control lat-map" name="lat" type="text">
                            <div class="help-block with-errors"></div>
                        </div>
                        <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
                            <label>longitud:</label>
                            <input class="form-control lon-map" name="lon" type="text">
                            <div class="help-block with-errors"></div>
                        </div>
                        <div class="col-lg-4 col-xs-12 col-md-4 col-sm-12">
                            <button type="button" name="btnMap" class="btn btn-success waves-effect waves-light m-r-10 btnGeo" style="margin-top: 26px;" data-btn="map">Consultar</button>
                        </div>
                    </div>

                    <div class="col-md-12 p-t-20 hidden" id="serv-c-map">
                        <div class="card card-outline-secondary text-center text-dark">
                            <div class="card-block">
                                <p class="m-0 font-18 serviciability-map"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

<script type="text/javascript">
    function initPlaces(){
            var input = document.getElementById('address'),
                autocomplete = new google.maps.places.Autocomplete(input),
                geocoder = new google.maps.Geocoder,
                lat = 19.39068, 
                lng = -99.2836963;

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
                            $('.lon-map').val(lng),
                            $('.lat-map').val(lat);
                        }
                        centerMap(map, marker, lat, lng);
                        //$('#serv-c').addClass('hidden');
                    }else{
                        showMessageAjax('alert-danger','No se encontro la dirección del punto marcado.');
                    }
                }else{
                    showMessageAjax('alert-danger','Ocurrio un error cargando la dirección, por favor intente mas tarde.');
                    console.log(status);
                }
            });
        }
</script>

@section('scriptJS')
<script src="{{ asset('https://maps.googleapis.com/maps/api/js?key='.env('GOOGLE_KEY').'&libraries=places&callback=initPlaces') }}"></script>

<script type="text/javascript">
    callServ = function(lat, lon, btn){
        if(btn == 'map')
            if(map && marker)
                centerMap(map, marker, lat, lon);

        $.ajax({
            type: 'POST',
            url: "{{route('dashboard.serviciability')}}",
            data: { _token: "{{ csrf_token() }}",lat:lat, lon:lon},
            success: function(serv){
                serv = JSON.parse(serv);
                if(serv.error && serv.message == 'TOKEN_EXPIRED'){
                    showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                }else{
                    $('.serviciability-'+btn).text(serv.message);
                    $('#serv-c-'+btn).removeClass('hidden');
                }
                $('.loading-ajax').hide();
            }
        });
    }

    $(function () {
        function handleError(err) {
            console.warn('ERROR(' + err.code + '): ' + err.message);
        };
            
        function getServicability(lat, lon, btn){
            $('.loading-ajax').show();
            
            if(lon != '', lat != ''){
                callServ(lat, lon, btn);
            }else{
                if (navigator.geolocation){
                    navigator.geolocation.getCurrentPosition(function (funcExito){
                        var lon = funcExito.coords.longitude;
                        var lat = funcExito.coords.latitude;
                        $('.lat-'+btn).val(lat);
                        $('.lon-'+btn).val(lon);

                        callServ(lat, lon, btn);
                    },handleError, {maximumAge:0});
                }else{
                    $('.loading-ajax').hide();
                    $('#msgAjax').addClass('alert-danger').show();
                    $('#txtMsg').text('No se puede obtener la Geolocalización.');
                    setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                }
            }
        }

        $('.btnGeo').on('click', function(event){
            var btn = $(event.currentTarget).data('btn'),
                lon = $('.lon-'+btn).val().trim(),
                lat = $('.lat-'+btn).val().trim();

            getServicability(lat, lon, btn)
        });
    });
</script>
@stop