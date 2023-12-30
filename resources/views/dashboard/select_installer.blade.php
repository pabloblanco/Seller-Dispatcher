@if($permit_boss)
<div class="col-12 mt-4">
  <button class="btn btn-success waves-effect waves-light m-r-10" data-cita="{{$cita_id}}" data-pack="{{$pack_id}}" id="auto_asigne_installer" title="Auto asignarme la cita" type="button">
    Auto asignarme la cita
  </button>
  <div hidden="" id="block_autoAsigne">
    <label>
      Auto-asignación:
    </label>
    <span id="date-installer-label">
      {{ session('name')." ".session('last_name')." - ".session('user') }}
    </span>
    <input hidden="" id="installer_auto" name="installer_auto" type="hidden" value="{{ session('user') }}"/>
    <button class="btn" data-cita="{{$cita_id}}" data-pack="{{$pack_id}}" data-bundle="{{$isBundle}}" id="trash_installer" title="Quitar auto-asignacion" type="button">
      <i class="fa fa-trash">
      </i>
    </button>
  </div>
</div>
@endif
<div class="col-12 px-3 my-4" id="block_select_installer">
  @if(count($installerDispose))
  <div class="row alert alert-danger">
    <p>
      <b>
        Precaución
      </b>
      al asignar el instalador a esta cita, removerá la cita de instalación pendientes y será asignada al nuevo instalador que selecciones.
    </p>
  </div>
  <label class="col-12">
    Buscar instalador
  </label>
  <div class="col-12">
    <select class="form-control" id="installer_list" name="installer_list" placeholder="Escribe el Nombre o correo del instalador" required="">
    </select>
    <div class="help-block with-errors" id="error_install">
    </div>
  </div>
  {{--
  <div class="col-12">
    <button class="btn btn-success waves-effect waves-light m-r-10" type="submit">
      Asignar instalador
    </button>
  </div>
  --}}
  @else
  <div class="alert alert-danger my-4 text-center">
    <p>
      No hay instaladores de la cuadrilla disponibles en este momento al cual asignar esta cita.  @if($isBundle=='Y') <strong> Nota: </strong><i> Se debe contar con el inventario para instalar el Bundle </i>@endif
    </p>
  </div>
  @endif
</div>
@if($permit_boss || count($installerDispose))
<input hidden="" id="installer" name="installer" type="hidden" value=""/>
<input id="pack_id" type="hidden" value="{{$pack_id}}"/>
<input id="install_id" type="hidden" value="{{$cita_id}}"/>
@endif
<script type="text/javascript">
  $(function () {

    @if(count($installerDispose))
      //console.log('lista instaladores');
      let myArray = new Array();
      @foreach($installerDispose as $nodo)
        var obj = {email: '{{$nodo->email}}', name: '{{$nodo->name.' '.$nodo->last_name}}', info_full: '{{$nodo->info}}'};
        myArray.push(obj);
        //console.log('obj> '+obj.email+' '+obj.name+' '+obj.info_full);
      @endforeach

    $('#installer_list').selectize({
      maxItems: 1,
      valueField: 'email',
      labelField: 'info_full',
      searchField: ['email','name'],
      options: myArray,
      create: false,
      render: {
        item: function (item, escape) {

          opt = "<div>";
          opt += '<span>' + escape(item.info_full) + '</span></br>';
          opt += "</div>";
          return opt;
        },
        option: function(item, escape) {

          opt = "<div>";
          opt += '<span style="color:#666; opacity:0.75; font-weight:600;">Instalador:</span><span> ' + escape(item.name) + "</span></br>";
          opt += '<span class="aai_description mb-0" style="color:#666; opacity:0.75; font-weight:600;"> Email: </span><span>' + escape(item.email) +"</span></br>";
          opt += "</div>";
          return opt;
        }
      },
    });

    $('#installer_list').change(function (e) {
      e.preventDefault();
      if($('#installer_list').val()!==''){
        $('#installer').val($('#installer_list').val());
        $('#installer').text($('#installer_list').text());
        $('#error_install').text('');
        $('#go-to-install').attr('disabled', null);
      }else{
        $('#error_install').text('Se debe seleccionar un instalador');
      }
    });

  @endif
  @if($permit_boss)
    //console.log('jefe permitido');

    $('#auto_asigne_installer').on('click', function(e){
      $('.loading-ajax').show();
      $('#auto_asigne_installer').attr('hidden', true);
      $('#block_autoAsigne').attr('hidden', false);
      $('.selectize-control').attr('hidden', true);
      $('#installer').val($('#installer_auto').val());
      $('#installer').text($('#date-installer-label').text());
      $('#block_select_installer').html('');
      $('.loading-ajax').fadeOut();
      $('#go-to-install').attr('disabled', null);
    });

    $('#trash_installer').on('click', function(e){
      $('#go-to-install').attr('disabled', true);
      viewInstallerList($(this).data('pack'), $(this).data('cita'), $(this).data('bundle'));
    });

  @endif

});
</script>
