<label>
  Escribir dirección:
</label>
<input class="form-control input-coverage" id="locality" name="locality" onfocus="initializeAutocomplete();" placeholder="*Ingrese dirección de agendamiento" type="text"/>
<input class="form-control input-coverage" id="locality2" name="locality2" type="hidden"/>
<div class="col-lg-4 col-xs-12 col-md-4 col-sm-12">
  <button class="btn btn-success waves-effect waves-light m-r-10" data-btn="map" id="btnAdress" name="btnAdress" style="margin-top: 26px;" type="button">
    Consultar servicialidad
  </button>
</div>
<script type="text/javascript">
  $(function () {
    $('#btnAdress').on('click', function(e){
      e.preventDefault();
      if($("#locality").val()!==''){

        if ($('#locality').val() != $('#locality2').val()) {

          showMessageAjax('alert-danger', 'La direccion ingresada no coincide con el marcador del mapa');
        }else{
          if(!$('.loading-ajax').is(':visible')){
            $('.loading-ajax').show();
          }
          
        doPostAjax(
          '{{ route('sellerFiber.getCoordFromAddress') }}',
          function(res){
            if($('.loading-ajax').is(':visible')){
              $('.loading-ajax').fadeOut();
            }
              //console.log('2- ',res);
            if(res.success){
              var lat = res.data.lat;
              var lng = res.data.lng;
              setPointMap(lat, lng);
              promesaTheCoverage(lat, lng);
            }else{
              if(res.message == 'TOKEN_EXPIRED'){
                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
              }else{
                resp = {"success":false,"msg":res.msg};
              }
            }
          },
          {
            locality: $('#locality').val()
          },
          $('meta[name="csrf-token"]').attr('content')
        );        
      }
      }else{
          showMessageAjax('alert-danger', 'Se debe ingresar un direccion para consultar servicialidad');
      }
    });
  });
</script>