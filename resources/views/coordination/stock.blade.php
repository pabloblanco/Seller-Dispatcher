@extends('layouts.admin')

@section('customCSS')
<link href="{{ asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css') }}" rel="stylesheet"/>
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
  <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
    <h4 class="page-title">
      Asginación de inventario
    </h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          Coordinación
        </a>
      </li>
      <li class="active">
        Asignar inventario.
      </li>
    </ol>
  </div>
</div>
<div class="row">
  @if($lock->is_locked == 'Y')
  <div class="col-md-12">
    <div class="white-box">
      <div class="alert alert-danger">
        <p>
          <b>
            Has sido bloqueado
          </b>
          , por favor comunicate con tu supervisor.
        </p>
      </div>
    </div>
  </div>
  @else
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Buscar vendedor
      </h3>
      <form action="" class="form-horizontal" data-toggle="validator" id="Salesclientform" method="POST">
        {{ csrf_field() }}
        <div class="form-group">
          <div class="col-md-12">
            <div id="scrollable-dropdown-menu">
              <select class="form-control" id="list-users" name="list-users">
              </select>
            </div>
          </div>
        </div>
      </form>
      <div class="row hidden p-b-20" id="data-seller">
        <h3 class="box-title">
          Datos del vendedor
        </h3>
        <div class="col-md-12">
          <ul class="list-icons">
            <li>
              <i class="ti-angle-right">
              </i>
              <strong>
                Nombre:
              </strong>
              <span class="name_seller">
              </span>
            </li>
            <li>
              <i class="ti-angle-right">
              </i>
              <strong>
                Teléfono:
              </strong>
              <span class="phone_seller">
              </span>
            </li>
            <li>
              <i class="ti-angle-right">
              </i>
              <strong>
                Email:
              </strong>
              <span class="email_seller">
              </span>
            </li>
          </ul>
        </div>
      </div>
      <div class="row hidden p-b-20" id="add-inv">
        <h3 class="box-title">
          Tipo de inventario
        </h3>
        <div class="col-md-12 m-b-10">
          <button class="btn btn-success waves-effect waves-light m-r-10" id="asig-hbb" name="asig-hbb" type="button">
            Módem
          </button>
          <button class="btn btn-success waves-effect waves-light m-r-10" id="asig-mifi" name="asig-mifi" type="button">
            MIFI
          </button>
          <button class="btn btn-success waves-effect waves-light" id="asig-mbb" name="asig-mbb" type="button">
            Sim card
          </button>
        </div>
        <div class="row hidden" id="asig-mifi-content">
          <h3 class="box-title col-md-12">
            Buscar módem MIFI
          </h3>
          <div class="col-md-8">
            <input class="typeahead form-control" id="list-art-mifi" placeholder="MSISDN" type="text">
            </input>
          </div>
          <div class="col-md-4 text-left">
            <button class="btn btn-success waves-effect waves-light add-to-seller" type="button">
              Asignar inventario
            </button>
          </div>
        </div>
        <div class="row hidden" id="asig-hbb-content">
          <h3 class="box-title col-md-12">
            Buscar módem
          </h3>
          <div class="col-md-8">
            <input class="typeahead form-control" id="list-art" placeholder="MSISDN" type="text">
          </div>
          <div class="col-md-4 text-left">
            <button class="btn btn-success waves-effect waves-light add-to-seller" type="button">
              Asignar inventario
            </button>
          </div>
        </div>
        <div class="row hidden" id="asig-mbb-content">
          <h3 class="box-title col-md-12">
            Seleccionar sim card
          </h3>
          <div class="col-md-12">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>
                  </th>
                  <th>
                    msisdn
                  </th>
                  <th>
                    iccid
                  </th>
                </tr>
              </thead>
              <tbody id="body-table-mbb">
                @foreach($invMbb as $item)
                <tr class="item" id="dnc-{{ $item->msisdn }}">
                  <td>
                    <input class="dn-t" name="item" type="checkbox" value="{{ $item->msisdn }}">
                  </td>
                  <td>
                    {{ $item->msisdn }}
                  </td>
                  <td>
                    {{ $item->iccid }}
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            <button class="btn btn-success waves-effect waves-light add-to-seller" type="button">
              Asignar inventario
            </button>
          </div>
        </div>
      </div>
      <div class="row hidden" id="error-message-inv">
        <div class="alert alert-danger">
          <p>
          </p>
        </div>
      </div>
      <div class="row hidden" id="info-seller">
        <h3 class="box-title">
          Inventario del vendedor
        </h3>
        <div class="col-md-12 hidden" id="without-article">
          <div class="alert alert-danger">
            <p>
              Vendor sin inventario.
            </p>
          </div>
        </div>
        <div class="col-md-12 hidden" id="device-content">
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@stop

@section('scriptJS')
@if($lock->is_locked == 'N')
<!-- typehead TextBox Search -->
<script src="{{ asset('plugins/bower_components/typeahead.js-master/dist/typeahead.bundle.min.js') }}">
</script>
<script src="{{ asset('js/selectize.js')}}">
</script>
<script type="text/javascript">
  $(function () {
            let invCoo = [],
                invMIFI = [],
                invMbb = 0,
                showAddInv = false, {{-- Muestro el boton de asignar inventario --}}
                limitInv = 0, {{-- Limite de inventario del vendedor hbb --}}
                limitInvMbb = 0, {{-- Limite de inventario del vendedor mbb --}}
                limitInvMIFI = 0; {{-- Limite de inventario del vendedor mifi --}}

            @foreach($invHbb as $arti)
                invCoo.push("{{$arti->msisdn}}");
            @endforeach

            @foreach($invMIFI as $arti)
                invMIFI.push("{{$arti->msisdn}}");
            @endforeach

            @if(!empty($invMbb) && $invMbb->count())
                invMbb = {{ $invMbb->count() }};
            @endif

            let configList = {
                hint: true,
                highlight: true,
                minLength: 1,
                dynamic: true
            };

            $('#list-art').typeahead(configList,{
                                name: 'articles',
                                source: substringMatcher(invCoo)
                            });

            $('#list-art-mifi').typeahead(configList,{
                                name: 'articles',
                                source: substringMatcher(invMIFI)
                              });

            $('#list-users').selectize({
                valueField: 'email',
                searchField: 'name',
                labelField: 'name',
                render: {
                    option: function(item, escape) {
                        return '<p>'+escape(item.name_profile)+': '+escape(item.name)+' '+escape(item.last_name)+'</p>';
                    }
                },
                load: function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('findRelationUsers') }}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            q: query,
                            dismissal: true
                        },
                        error: function() {
                            callback();
                        },
                        success: function(res){
                            if(!res.error){
                                callback(res.users);
                            }else{
                                callback();
                            }
                        }
                    });
                }
            });

            $('#list-users').on('change', function(e){
                let email = $(this).val();

                if(email && email != ''){
                    $(".preloader").fadeIn();
                    $('#list-art').val('');
                    $('#list-art-mifi').val('');
                    $('#device-content').html('');
                    $('#info-seller').removeClass('hidden');
                    $('#without-article').addClass('hidden');
                    $('#error-message-inv').addClass('hidden');
                    $('#device-content').addClass('hidden');
                    $('#add-inv').addClass('hidden');
                    $('#data-seller').addClass('hidden');

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'POST',
                        dataType: 'json',
                        url: "{{route('coordination.findInveSeller')}}",
                        data: { seller: email },
                        success: function(data){
                            if(!data.error){
                                //Mostrando datos del vendedor
                                //console.log(data);

                                if(data.seller){
                                    $('.name_seller').text(data.seller.name + ' ' + data.seller.last_name);
                                    $('.phone_seller').text(data.seller.phone);
                                    $('.email_seller').text(data.seller.email);
                                    $('#data-seller').removeClass('hidden');
                                }

                                //Muestra div dependiendo si el usuario autenticado tiene asignado inventario o no
                                if(invCoo.length < 1 && invMbb < 1 && invMIFI.length < 1){
                                    if(data.isDismissal){
                                        $('#error-message-inv p').text('El usuario esta en proceso de baja.');
                                        $('#error-message-inv').removeClass('hidden');
                                    }else{
                                      $('#error-message-inv p').text('No tienes disponible inventario para asignar.');
                                      $('#error-message-inv').removeClass('hidden');
                                    }
                                }else{
                                    let isBtnAsigActive = false;
                                    $('#asig-hbb').hide();
                                    $('#asig-mbb').hide();
                                    $('#asig-mifi').hide();
                                    //Valida si el vendedor tiene ventas sin consiliar con el coordinador
                                    if(!data.isSalesActive && !data.isDismissal && data.stockHbb < data.limitInvHbb){
                                        $('#asig-hbb').show();
                                        
                                        if(isBtnAsigActive === false){
                                            isBtnAsigActive = 'asig-hbb';
                                        }
                                    }

                                    if(!data.isSalesActive && !data.isDismissal && data.stockMbb < data.limitInvMbb){
                                        $('#asig-mbb').show();

                                        if(isBtnAsigActive === false){
                                            isBtnAsigActive = 'asig-mbb';
                                        }
                                    }

                                    if(!data.isSalesActive && !data.isDismissal && data.stockMIFI < data.limitInvMIFI){
                                        $('#asig-mifi').show();

                                        if(isBtnAsigActive === false){
                                            isBtnAsigActive = 'asig-mifi';
                                        }
                                    }

                                    if(isBtnAsigActive !== false){
                                        $('#add-inv').removeClass('hidden');
                                        $('#'+isBtnAsigActive).trigger('click');
                                    }

                                    if(data.isDismissal){
                                        $('#error-message-inv p').text('El usuario esta en proceso de baja.');
                                        $('#error-message-inv').removeClass('hidden');
                                    }else if(data.isSalesActive){
                                        $('#error-message-inv p').text('El vendedor tiene ventas activas.');
                                        $('#error-message-inv').removeClass('hidden');
                                    }else if(data.stockHbb >= data.limitInvHbb && data.stockMbb >= data.limitInvMbb){
                                        $('#asig-hbb-content').addClass('hidden');
                                        $('#asig-mbb-content').addClass('hidden');

                                        $('#error-message-inv p').text('El vendedor alcanzó el limite de inventario en módems y simcards.');
                                        $('#error-message-inv').removeClass('hidden');
                                    } 
                                }

                                //Esta bandera valida si se muestra el boton de asignar inventario en caso de que retorne inventario al coordinador
                                if(!data.isSalesActive && !data.isDismissal){
                                    showAddInv = true;
                                }

                                limitInv = data.limitInvHbb;
                                limitInvMbb = data.limitInvMbb;
                                limitInvMIFI = data.limitInvMIFI;

                                if(data.stock.length > 0){
                                    $('#device-content').html(data.htmlStock);
                                    $('#device-content').removeClass('hidden');

                                    bindEvents();
                                }else{
                                    $('#without-article').removeClass('hidden');
                                }
                            }else{
                                if(data.message == 'TOKEN_EXPIRED'){
                                    showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                                }else{
                                    showMessageAjax('alert-danger',data.message);
                                }
                            }
                            $(".preloader").fadeOut();
                        },
                        error: function(){
                            showMessageAjax('alert-danger', 'Ocurrio un error.');
                            $(".preloader").fadeOut();
                        }
                    });
                }
            });

            $('.add-to-seller').on('click', function(e){
                var num = $('#list-art').val().trim(),
                    type = 'HBB';

                if(num == ''){
                    num = $('#list-art-mifi').val().trim();
                    type = 'MIFI';
                }

                if($('.dn-t').is(':checked')){
                    var num = [];
                    type = 'MBB';
                    $('.dn-t').each(function(){
                        if($(this).is(':checked') && $(this).val().trim() != ''){
                            num.push($(this).val());
                        }
                    });
                }

                if(num.length){
                    $(".preloader").fadeIn();

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('coordination.addStock') }}",
                        type: 'POST',
                        data:{
                            msisdn: num,
                            seller: $('#list-users').val().trim(),
                            type: type
                        },
                        dataType: 'json',
                        success: function(data){
                            if(!data.error){
                                if(data.articles){
                                    $('#without-article').addClass('hidden');

                                    data.articles.forEach(function(ele){
                                        if(ele.artic_type == 'H'){
                                            //Quitando msisdn de la lista
                                            let index = invCoo.indexOf(ele.msisdn);
                                            if (index > -1) {
                                               invCoo.splice(index, 1);
                                            }
                                        }else if(ele.artic_type == 'T'){
                                            $('#dnc-'+ele.msisdn).remove();
                                            invMbb = invMbb - 1;
                                        }else if(ele.artic_type == 'M'){
                                            //Quitando msisdn de la lista
                                            let index = invMIFI.indexOf(ele.msisdn);
                                            if (index > -1) {
                                               invMIFI.splice(index, 1);
                                            }
                                        }
                                    });

                                    $('#device-content').append(data.htmlStock);
                                    $('#device-content').removeClass('hidden');

                                    if(limitInvMbb <= $('#device-content blockquote.T').length && limitInv <= $('#device-content blockquote.H').length && limitInvMIFI <= $('#device-content blockquote.M').length){

                                        $('#asig-mbb').hide();
                                        $('#asig-hbb').hide();
                                        $('#asig-mifi').hide();
                                        $('#asig-mbb-content').addClass('hidden');
                                        $('#asig-hbb-content').addClass('hidden');
                                        $('#asig-mifi-content').addClass('hidden');

                                        $('#error-message-inv p').text('El vendedor alcanzó el limite de inventario en simcardsy módems.');
                                        $('#error-message-inv').removeClass('hidden');
                                    }else{
                                        if(limitInvMbb <= $('#device-content blockquote.T').length){
                                            $('#asig-mbb').hide();
                                            $('#asig-mbb-content').addClass('hidden');

                                            $('#error-message-inv p').text('El vendedor alcanzó el limite de inventario en simcards o teléfonos.');
                                            $('#error-message-inv').removeClass('hidden');
                                        }
                                        
                                        if(limitInv <= $('#device-content blockquote.H').length){
                                            $('#asig-hbb').hide();
                                            $('#asig-hbb-content').addClass('hidden');

                                            $('#error-message-inv p').text('El vendedor alcanzó el limite de inventario en módems.');
                                            $('#error-message-inv').removeClass('hidden');
                                        }

                                        if(limitInvMIFI <= $('#device-content blockquote.M').length){
                                            $('#asig-mifi').hide();
                                            $('#asig-mifi-content').addClass('hidden');

                                            $('#error-message-inv p').text('El vendedor alcanzó el limite de inventario en módems mifi.');
                                            $('#error-message-inv').removeClass('hidden');
                                        }
                                    }
                                    
                                    bindEvents();
                                    showMessageAjax('alert-success', data.message);
                                    
                                    //Muestra alerta en caso de que le coordinador se quede sin inventario
                                    if(invCoo.length < 1 && type == 'HBB'){
                                        $('#asig-hbb-content').addClass('hidden');
                                        $('#error-message-inv p').text('No tienes disponible inventario de módems para asignar.');
                                        $('#error-message-inv').removeClass('hidden');
                                    }

                                    if(invMIFI.length < 1 && type == 'MIFI'){
                                        $('#asig-mifi-content').addClass('hidden');
                                        $('#error-message-inv p').text('No tienes disponible inventario de módems mifi para asignar.');
                                        $('#error-message-inv').removeClass('hidden');
                                    }

                                    if(type == 'MBB' && invMbb < 1){
                                        $('#asig-mbb-content').addClass('hidden');
                                        $('#error-message-inv p').text('No tienes disponible inventario de sim card para asignar.');
                                        $('#error-message-inv').removeClass('hidden');
                                    }

                                    if($('.dn-t').length && type == 'MBB'){
                                        $('.dn-t').each(function(){
                                            $(this).prop("checked", false);
                                        });
                                    }
                                }else{
                                    showMessageAjax('alert-danger','Ocurrio un error.');
                                }
                            }else{
                                if(data.message == 'TOKEN_EXPIRED'){
                                    showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                                }else{
                                    showMessageAjax('alert-danger',data.message);
                                }
                            }
                            $('#list-art').val('');
                            $(".preloader").fadeOut();
                        },
                        error: function(){
                            showMessageAjax('alert-danger', 'Ocurrio un error.');
                            $(".preloader").fadeOut();
                        }
                    });
                }else{
                    showMessageAjax('alert-danger', 'Debe seleccionar un MSISDN');
                }
            });

            bindEvents = function(){
                $('.return-inv').unbind('click');

                $('.return-inv').on('click',function(e){
                    let num = $(e.currentTarget).data('num'),
                        preasg = $(e.currentTarget).data('pasg'),
                        seller = $('#list-users').val().trim();

                    if(num && seller){
                        $(".preloader").fadeIn();

                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: "{{ route('coordination.removeStock') }}",
                            type: 'POST',
                            data:{
                                msisdn: num,
                                seller: seller,
                                preasignado: preasg
                            },
                            dataType: 'json',
                            success: function(data){
                                if(!data.error){
                                    if(data.msisdn){
                                        showMessageAjax('alert-success', data.message);

                                        if($('#device-content blockquote').length > 1){
                                            $('.'+data.msisdn).replaceWith('');
                                            showMessageAjax('alert-success', data.message);
                                        }else{
                                            $('#device-content').html('');
                                            $('#without-article').removeClass('hidden');
                                            $('#device-content').addClass('hidden');
                                        }
                                        
                                        //Agrega al listado de msisdn el numero que retornaron al coordinador
                                        if(data.type == 'H'){
                                            invCoo.push(data.msisdn);
                                        }

                                        if(data.type == 'M'){
                                            invMIFI.push(data.msisdn);
                                        }

                                        if(data.type == 'T'){
                                            var row = '<tr class="item" id="dnc-'+data.msisdn+'">';
                                            row += '<td>';
                                            row += '<input class="dn-t" type="checkbox" name="item" value="'+data.msisdn+'">';
                                            row += '</td>';
                                            row += '<td>';
                                            row +=  data.msisdn;
                                            row += '</td>';
                                            row += '<td>';
                                            row +=  data.iccid;
                                            row += '</td>';
                                            row += '</tr>';

                                            $('#body-table-mbb').append(row);
                                            invMbb = invMbb + 1;
                                        }

                                        $('#asig-hbb').hide();
                                        $('#asig-mbb').hide();
                                        $('#asig-mifi').hide();
                                            
                                        if(showAddInv && $('#device-content blockquote.H').length < limitInv){
                                            $('#asig-hbb').show();
                                            $('#add-inv').removeClass('hidden');

                                            if($('#asig-hbb').hasClass('disabled') && $('#asig-hbb').is(':visible')){
                                                $('#asig-hbb-content').removeClass('hidden');
                                            }
                                        }

                                        if(showAddInv && $('#device-content blockquote.M').length < limitInvMIFI){
                                            $('#asig-mifi').show();
                                            $('#add-inv').removeClass('hidden');

                                            if($('#asig-mifi').hasClass('disabled') && $('#asig-mifi').is(':visible')){
                                                $('#asig-mifi-content').removeClass('hidden');
                                            }
                                        }

                                        if(showAddInv && $('#device-content blockquote.T').length < limitInvMbb){
                                            $('#add-inv').removeClass('hidden');
                                            $('#asig-mbb').show();

                                            if($('#asig-mbb').hasClass('disabled') && $('#asig-mbb').is(':visible')){
                                                $('#asig-mbb-content').removeClass('hidden');
                                            }
                                        }

                                        if($('#error-message-inv').is(':visible')){
                                           $('#error-message-inv').addClass('hidden');
                                        }
                                    }else{
                                        showMessageAjax('alert-danger', 'Ocurrio un error.');
                                    }
                                }else{
                                    if(data.message == 'TOKEN_EXPIRED'){
                                        showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                                    }else{
                                        showMessageAjax('alert-danger',data.message);
                                    }
                                }
                                $(".preloader").fadeOut();
                            },
                            error: function(){
                                showMessageAjax('alert-danger', 'Ocurrio un error.');
                                $(".preloader").fadeOut();
                            }
                        });
                    }else{
                        showMessageAjax('alert-danger', 'Ocurrio un error.');
                    }
                });
            }

            $('#asig-hbb').on('click', function(e){
                $('.dn-t').each(function(){
                    $(this).prop("checked", false);
                });

                $('#asig-mbb-content').addClass('hidden');
                $('#asig-mifi-content').addClass('hidden');
                $('#asig-hbb-content').removeClass('hidden');

                $('#asig-hbb').addClass('disabled');
                $('#asig-hbb').addClass('btn-default');
                $('#asig-hbb').removeClass('btn-success');

                $('#asig-mbb').removeClass('disabled');
                $('#asig-mbb').removeClass('btn-default');
                $('#asig-mbb').addClass('btn-success');

                $('#asig-mifi').removeClass('disabled');
                $('#asig-mifi').removeClass('btn-default');
                $('#asig-mifi').addClass('btn-success');

                if(invCoo.length < 1){
                    $('#asig-hbb-content').addClass('hidden');
                    $('#error-message-inv p').text('No tienes disponible inventario de módems para asignar.');
                    $('#error-message-inv').removeClass('hidden');
                }
            });

            $('#asig-mifi').on('click', function(e){
                $('.dn-t').each(function(){
                    $(this).prop("checked", false);
                });

                $('#asig-mbb-content').addClass('hidden');
                $('#asig-hbb-content').addClass('hidden');
                $('#asig-mifi-content').removeClass('hidden');

                $('#asig-mifi').addClass('disabled');
                $('#asig-mifi').addClass('btn-default');
                $('#asig-mifi').removeClass('btn-success');

                $('#asig-hbb').removeClass('disabled');
                $('#asig-hbb').removeClass('btn-default');
                $('#asig-hbb').addClass('btn-success');

                $('#asig-mbb').removeClass('disabled');
                $('#asig-mbb').removeClass('btn-default');
                $('#asig-mbb').addClass('btn-success');

                if(invMIFI.length < 1){
                    $('#asig-mifi-content').addClass('hidden');
                    $('#error-message-inv p').text('No tienes disponible inventario de módems para asignar.');
                    $('#error-message-inv').removeClass('hidden');
                }
            });

            $('#asig-mbb').on('click', function(e){
                $('#asig-hbb-content').addClass('hidden');
                $('#asig-mifi-content').addClass('hidden');
                $('#asig-mbb-content').removeClass('hidden');

                $('#asig-mbb').addClass('disabled');
                $('#asig-mbb').addClass('btn-default');
                $('#asig-mbb').removeClass('btn-success');

                $('#asig-hbb').removeClass('disabled');
                $('#asig-hbb').removeClass('btn-default');
                $('#asig-hbb').addClass('btn-success');

                $('#asig-mifi').removeClass('disabled');
                $('#asig-mifi').removeClass('btn-default');
                $('#asig-mifi').addClass('btn-success');

                if(invMbb < 1){
                    $('#asig-mbb-content').addClass('hidden');
                    $('#error-message-inv p').text('No tienes disponible inventario de sim card para asignar.');
                    $('#error-message-inv').removeClass('hidden');
                }
            });
        });
</script>
@endif
@stop
