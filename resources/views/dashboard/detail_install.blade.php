<form class="form-horizontal" data-id="{{$data->id}}" data-toggle="validator" id="form-edit-install">
  @include('fiber.infoClientFiber', ['data' => $data ])
  <div class="row">
    <div class="col-12 px-3 pb-3">
      <hr class="mb-4 mt-4"/>
      <label>
        Dirección de Instalación
      </label>
    </div>
    <div class="col-12 col-md-6 px-3">
      <label>
        Estado:
      </label>
      <p id="date-state-label">
        {{$data->state ?? 'S/I'}}
      </p>
    </div>
    <div class="col-12 col-md-6 px-3">
      <label>
        Ciudad:
      </label>
      <p id="date-city-label">
        {{$data->city ?? 'S/I'}}
      </p>
    </div>
    <div class="col-12 col-md-6 px-3">
      <label>
        Alcaldia/Municipio:
      </label>
      <p id="date-municipality-label">
        {{$data->municipality ?? 'S/I'}}
      </p>
    </div>
    <div class="col-12 col-md-6 px-3">
      <label>
        Colonia:
      </label>
      <p id="date-colony-label">
        {{$data->colony ?? 'S/I'}}
      </p>
    </div>
    <div class="col-12 col-md-6 px-3">
      <label>
        Calle:
      </label>
      <p id="date-route-label">
        {{$data->route ?? 'S/I'}}
      </p>
    </div>
    <div class="col-12 col-md-6 px-3">
      <label>
        Número de casa:
      </label>
      <p id="date-house_number-label">
        {{$data->house_number ?? 'S/I'}}
      </p>
    </div>
    <div class="col-12 px-3">
      <label>
        Referencia:
      </label>
      <p id="date-reference-label">
        {{$data->reference ?? 'S/I'}}
      </p>
    </div>
  </div>
  @include('fiber.infoOltConex', ['data' => $data, 'view' => true ])
  <div class="row">
    <div class="col-12 px-3 p-t-10 align-items-center">
      <hr class="mb-4 mt-4"/>
      <label class="mr-3">
        Foto referencia:
      </label>
      <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#ref_collapse" aria-expanded="false" aria-controls="ref_collapse" title="Ver Documento"><i class="fa fa-eye"></i></button>
    </div>
    <div id="ref_collapse" class="collapse">
      @if(!empty($data->photo))
        <img alt="image" class="img-responsive img-rounded" src="{{$data->photo}}" width="100%">
        @else
        <p>
          S/I
        </p>
        @endif
      </img>
      </div>
  </div>
  @if(!empty($data->lat) && !empty($data->lng))
  <div class="row">
    <div class="col-12 px-3">
      <label>
        Ubicación:
      </label>
      <div class="map-container" id="map" style="height: 50vh;min-height: 250px; padding-top: 20px;">
        <div id="map-content-inst" style="width: 100%;height: 100%;">
        </div>
      </div>
    </div>
  </div>
  @endif

  <div class="row">
    <div class="col-12 px-3">
      <hr class="mb-4 mt-4"/>
      <label>
        Paquete de Instalación
      </label>
    </div>
  </div>
  @if(isset($bundlePack))
    @include('fiber.plan', ['plan' => $bundlePack, 'bundle' => true])
  @else
    @include('fiber.plan', ['plan' => $infoPack])
  @endif

  <div class="row">
    <div class="col-12 p-t-10 px-3">
      <label>
        Fecha de instalación:
      </label>
      <p id="date-inst-label">
        {{date('d-m-Y', strtotime($data->date_instalation)).' / '.$data->schedule}}
        {{--
        esto solo lo hara la mesa de control
        <a href="#" id="edit-date">
          <i class="fa fa-edit">
          </i>
        </a>
        --}}
      </p>
    </div>
  </div>
  <div class="row">
    <div class="col-12 px-3 p-t-10">
      <label>
        Instalador:
      </label>
      <div id="date-inst-label">
        @if(!empty($data->name_inst) || !empty($data->last_name_inst))
        <div id="block_list_installer">
          {{$data->name_inst}} {{$data->last_name_inst ?? ''}}

        @if(hasPermit('FIB-AIC'))
          <span>
            {{--solo el jefe puede editar el instalador--}}
          {{--
            <a href="#" id="change-install">
              <i class="fa fa-edit">
              </i>
            </a>
            --}}
            <button class="btn" data-cita="{{$data->id}}" data-pack="{{$data->pack_id}}" data-type="{{$type}}" id="list_installer" title="Cambiar instalador" type="button">
              <i class="fa fa-edit">
              </i>
            </button>
          </span>
        </div>
        @endif
      @else
        <div class="row align-items-center" id="block_list_installer">
          <div class="col-auto px-2">
            Instalador por establecer...
          </div>
          @if(hasPermit('FIB-AIC'))
          <div class="col-auto px-2">
            <button class="btn btn-success waves-effect waves-light m-r-10" data-cita="{{$data->id}}" data-pack="{{$data->pack_id}}" data-bundle="{{!empty($data->bundle_id)?'Y':'N'}}" data-type="{{$type}}" id="list_installer" title="Buscar instaladores disponibles" type="button">
              Listar instaladores
            </button>
          </div>
          @endif
        </div>
        @endif
        <div class="row" id="insta-content">
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    {{--
   @if($data->paid == 'N')
    <div class="col-12 px-3 p-t-10">
      <button class="btn btn-danger waves-effect waves-light m-r-10" data-target="#modalDeleteCita" data-toggle="modal" type="button">
        Eliminar cita
      </button>
    </div>
    @endif
    --}}
    @if(hasPermit('FIB-SMC'))
    <div class="col-6 px-3 p-t-10">
      <button class="btn btn-danger waves-effect waves-light m-r-10" data-cita="{{$data->id}}" data-target="#modalCancelCita" data-toggle="modal" data-type="{{$type}}" id="btnCancelCita" title="Indica la razon de cancelacion para que mesa de control gestione una solucion" type="button">
        Cancelar cita
      </button>
    </div>
    @endif
  </div>
</form>
<script type="text/javascript">

  function initViewInstaller(option){
    $('#go-to-install').attr('disabled',true);
    switch(option){
      case "installer":
        //Instalaciones por realizar.
        $('#go-to-install').html('Realizar instalación');
        $('#go-to-install').data('vtype','installer');
        $('#go-to-install').attr('disabled', false);
        break;
      case "installerAgenda":
        //Citas por asignar.
        $('#go-to-install').html('Asignar cita');
        $('#go-to-install').data('vtype','installerAgenda');
        $('#go-to-install').attr('disabled',true);
        break;
      case "installerAsigne":
        //Citas asignadas a instaladores.
        $('#go-to-install').html('Guardar cambios');
        $('#go-to-install').data('vtype','installerAsigne');
        break;
      default:
        console.log('Opcion '+option+' no definida');
      }
  }

  @if(hasPermit('FIB-AIC'))

  function viewInstallerList(pack_id, cita_id, DataBundle='N'){
    $('.loading-ajax').fadeIn();
    doPostAjax(
      '{{ route('sellerFiber.getListInstaller') }}',
      function(res){
        $('.loading-ajax').fadeOut();

        if(res.sucess){
          $('#insta-content').html(res.html);
          $('#block_list_installer').attr('hidden', true);
        }else{
          showMessageAjax('alert-danger',res.msg);
        }
      },
      {
        pack: pack_id,
        cita: cita_id,
        isBundle: DataBundle
      },
      $('meta[name="csrf-token"]').attr('content')
    );
  }

  @endif

  $(function () {
      $('#block_list_installer').attr('hidden', null);
      initViewInstaller('{{$type}}');
      @if(!empty($data->lat) && !empty($data->lng))
        let lat = {{$data->lat}},
            lng = {{$data->lng}};

        center = new google.maps.LatLng(lat, lng),
        map = new google.maps.Map(document.getElementById('map-content-inst'), {
                        center,
                        zoom: 15
                    });

        marker = new google.maps.Marker({
                  map: map,
                  draggable: true,
                  animation: google.maps.Animation.DROP,
                  position: {lat: lat, lng: lng}
                });
      @endif
      @if(hasPermit('FIB-AIC'))

      $('#list_installer').on('click', function(e){
        if($(this).data('type')=='installer'){
          //Instalaciones asignadas al jefe que decidio editar quien lo instalara
          initViewInstaller('installerAsigne');
        }
        viewInstallerList($(this).data('pack'), $(this).data('cita'), $(this).data('bundle'));
      });
      @endif
  });

</script>
