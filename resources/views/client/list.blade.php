@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
    <link href="{{ asset('css/selectize.css') }}" rel="stylesheet">
    <link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet">
@stop

@section('content')
    @include('components.messages')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"> Lista de Prospectos </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Propectos</a></li>
                <li class="active">Listado de Prospectos.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                <h3 class="box-title">Prospectos</h3>

                <div class="col-md-12 p-b-20">
                    @if(session('user_type') != 'vendor')
                        <div class="col-sm-12 col-md-6 col-lg-4 m-b-20">
                            <div class="form-group">
                                <input type="text" class="form-control" id="searchText" placeholder="Buscar por nombre o teléfono">
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-6 col-lg-4 m-b-20">
                            <div class="form-group">
                                <input class="form-control input-daterange-datepicker" type="text" name="daterange" id="daterange" placeholder="Seleccione un rango de fechas" value="" readonly="true" />
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-6 col-lg-4 m-b-20">
                            <div class="form-group">
                                <select class="form-control" id="seller" name="seller">
                                    <option value="">Seleccione un usuario</option>
                                    <option value="{{session('user')}}">Yo</option>
                                </select>

                                {{-- <select class="form-control" id="seller" name="seller">
                                    <option value="">Vendedores</option>
                                    @if(session('user_type') == 'admin' || session('user_type') == 'coordinador')
                                        <option value="{{session('user')}}">YO</option>
                                    @endif
                                    @foreach($sellers as $seller)
                                        <option value="{{$seller->email}}"> {{$seller->name}} {{$seller->last_name}} </option>
                                    @endforeach
                                </select> --}}
                            </div>
                        </div>

                        <div class="col-md-12 text-center">
                            <button type="button" name="search" id="search" class="btn btn-success waves-effect waves-light m-r-10">Consultar</button>
                        </div>
                    @else
                        <div class="col-sm-12 col-md-6 col-lg-4 m-b-20">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchText" placeholder="Buscar por nombre o teléfono">
                                <span class="input-group-btn">
                                    <button class="btn btn-info" type="button" id="search">
                                        <i class="zmdi zmdi-search zmdi-hc-fw"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                <div id="client-list" class="col-sm-12 col-md-12">
                    @forelse ($data['clients'] as $client)
                        <div class="col-md-12">
                            <blockquote>
                                <label>Nombre:</label>
                                <p>{{$client->name}} {{$client->last_name}}</p>
                                <label>Tel&eacute;fono:</label>
                                <p>{{$client->phone_home}}</p>
                                <label>Fecha de registro:</label>
                                <p>{{$client->date_reg}}</p>
                                @if(session('user_type') != 'vendor')
                                    <label>Registrado por:</label>
                                    <p>{{$client->seller}}</p>
                                @endif
                                @if(!empty($client->lat) && !empty($client->lng))
                                    <label>Cordenadas:</label>
                                    <p> <b>Latitud</b> {{$client->lat}} <b>Longitud</b> {{$client->lng}} </p>
                                @endif
                                @if(!empty($client->contact_date))
                                    <label>Fecha de pr&oacute;ximo contacto:</label>
                                    <p>{{$client->contact_date}}</p>
                                @endif
                                @if(!empty($client->note))
                                    <label>Nota:</label>
                                    <p>{{$client->note}}</p>
                                @endif

                                @if(hasPermit('EPD-DSE') && request()->route()->getName() == 'client.list')
                                <a href="{{$client->urledit}}" class="edit btn btn-info btn-circle btn-lg waves-effect font-23 m-r-10">
                                    <i class="zmdi zmdi-edit"></i>
                                </a>
                                @endif

                                @if(hasPermit('CDV-DSE'))
                                <a href="{{$client->schedule}}" class="edit btn btn-success btn-circle btn-lg waves-effect font-23">
                                    <i class="zmdi zmdi-calendar"></i>
                                </a>
                                @endif
                            </blockquote>
                        </div>
                    @empty
                        <div class="col-md-12">
                            <blockquote>
                                <p>No hay prospectos registrados.</p>
                            </blockquote>
                        </div>
                    @endforelse
                </div>

                @if($data['recordsTotal'] > $data['limit'])
                    <div class="col-md-12 text-center">
                        <ul class="pagination pagination-split" id="client-pag">
                            @if($data['actualPage'] == 1)
                                <li class="disabled"> <a><i class="fa fa-angle-left"></i></a> </li>
                            @else
                                <li> <a class="page activate-loader" href="{{$data['first']}}"><i class="fa fa-angle-left"></i></a> </li>
                            @endif
                            @for ($i = 0; $i < count($data['pages']); $i++)
                                <li class="{{$data['pages'][$i]['active'] ? 'active' : ''}}">
                                    <a class="page activate-loader" href="{{$data['pages'][$i]['url']}}">{{$data['pages'][$i]['number']}}</a>
                                </li>
                            @endfor
                            @if($data['actualPage'] == $data['totalPAges'])
                                <li> <a><i class="fa fa-angle-right"></i></a> </li>
                            @else
                                <li> <a class="page activate-loader" href="{{$data['last']}}"><i class="fa fa-angle-right"></i></a> </li>
                            @endif
                        </ul>
                        <h4>Total de la consulta: <b>{{$data["recordsTotal"]}}</b> </h4>
                    </div>
                @else
                    <div class="col-md-12 text-center">
                        <h4>Total de la consulta: <b>{{$data["recordsTotal"]}}</b> </h4>
                    </div>
                @endif
            </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/moment/moment.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('js/selectize.js')}}"></script>

    <script type="text/javascript">
        $(function () {
            var urlList = '';
            @if(hasPermit('LCL-DSE'))
                urlList = '{{route('client.list')}}';
            @endif

            doSearch = function(){
                var text = $('#searchText').val().trim();

                if($('#daterange').length){
                    var dates = $('#daterange').val().trim(),
                        seller = $('#seller').val().trim();
                }
                    
                if(urlList != '' && (text != '' || dates != '' || seller != '')){
                    var search = '';
                    if(text != '')
                        search += 'np='+text+',';
                    if($('#daterange').length){
                        if(dates != '')
                            search += 'date='+dates+',';
                        if(seller != '')
                            search += 'seller='+seller;
                    }
                    window.location = urlList+'/1/'+search;
                }else{
                    window.location = urlList;
                }
            }

            $('#search').on('click', function(event){
                doSearch();
            });

            $('#searchText').keyup(function(e){
                if(e.keyCode == 13)
                {
                    doSearch();
                }
            });

            $('.input-daterange-datepicker').daterangepicker({
                autoUpdateInput: false,
                buttonClasses: ['btn', 'btn-sm'],
                applyClass: 'btn-danger',
                cancelClass: 'btn-inverse',
                maxDate: new Date(),
                locale: {
                    format: "YYYY/MM/DD",
                    separator: "-",
                    applyLabel: "Aceptar",
                    cancelLabel: 'Cancelar',
                    toLabel: "A",
                }
            });

            $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            bindEvents();
        });

        function bindEvents(){
            $('.page').on('click', function(event){
                event.preventDefault();
                var href = $(event.currentTarget).prop('href');

                $.ajax({
                    type: 'GET',
                    url: href,
                    //data: { _token: "{{ csrf_token() }}",lat:lat, lon:lon},
                    success: function(data){
                        if(data && data.clients.length > 0){
                            var htmlpg = '';
                            if(data.actualPage == 1)
                                htmlpg = '<li class="disabled"> <a><i class="fa fa-angle-left"></i></a> </li>';
                            else
                                htmlpg = '<li> <a class="page activate-loader" href="'+data.first+'"><i class="fa fa-angle-left"></i></a> </li>';
                            data.pages.forEach(function(ele){
                                var active = ele.active ? 'active' : '';
                                htmlpg += '<li class="'+active+'">';
                                htmlpg += '<a class="page activate-loader" href="'+ele.url+'">'+ele.number+'</a>';
                                htmlpg += '</li>';
                            });
                            if(data.actualPage == data.totalPAges)
                                htmlpg += '<li class="disabled"> <a><i class="fa fa-angle-right"></i></a> </li>';
                            else
                                htmlpg += '<li> <a class="page activate-loader" href="'+data.last+'"><i class="fa fa-angle-right"></i></a> </li>';
                            
                            var htmlList = '';
                            data.clients.forEach(function(ele){
                                htmlList += '<div class="col-md-12"> <blockquote>';
                                htmlList += '<label>Nombre:</label> <p>'+ele.name+' '+ele.last_name+'</p>';
                                htmlList += '<label>Teléfono:</label> <p>'+ele.phone_home+'</p>';
                                htmlList += '<label>Fecha de registro:</label> <p>'+ele.date_reg+'</p>';

                                @if(session('user_type') != 'vendor')
                                    htmlList += '<label>Registrado por:</label> <p>'+ele.seller+'</p>';
                                @endif

                                if(ele.lat && ele.lng){
                                    htmlList += '<label>Cordenadas:</label><p><b>Latitud</b>'+ele.lat+'<b>Longitud</b>'+ele.lng+'</p>';
                                }

                                if(ele.contact_date){
                                     htmlList += '<label>Fecha de pr&oacute;ximo contacto:</label> <p>'+ele.contact_date+'</p>';
                                }

                                if(ele.note){
                                     htmlList += '<label>Nota:</label> <p>'+ele.note+'</p>';
                                }
                                    
                                @if(request()->route()->getName() == 'client.list')
                                if(data.canEdit){
                                    htmlList += '<a href="'+ele.urledit+'" class="edit btn btn-info btn-circle btn-lg waves-effect font-23 m-r-10">';
                                    htmlList += '<i class="zmdi zmdi-edit"></i>';
                                    htmlList += '</a>';
                                }
                                @endif
                                {{--@if(request()->route()->getName() == 'date.new')--}}
                                if(data.canSchedule){
                                    htmlList += '<a href="'+ele.schedule+'" class="edit btn btn-success btn-circle btn-lg waves-effect font-23">';
                                    htmlList += '<i class="zmdi zmdi-calendar"></i>';
                                    htmlList += '</a>';
                                }
                                {{--@endif--}}
                                htmlList += '</blockquote> </div>';
                            });

                            $('#client-list').html(htmlList);
                            $('#client-pag').html(htmlpg);
                            bindEvents();
                        }
                        $(".preloader").fadeOut();
                    },
                    error: function(){
                        $(".preloader").fadeOut();
                    }
                });
            });

            $('.activate-loader').on('click', function(event){
                $(".preloader").fadeIn();
            });

            $('#seller').selectize({
                valueField: 'email',
                searchField: 'name',
                labelField: 'name',
                options: [
                    {email: '{{ session('user') }}', name: 'Yo', type: '{{ session('profile') }}'},
                    @if(!empty($sellers) && count($sellers))
                    @foreach($sellers as $seller)
                        {email: '{{$seller->email}}', name: '{{$seller->name_profile.': '.$seller->name}} {{$seller->last_name}}', type: '{{ ($seller->name_profile == 'Sub Coordinador' || $seller->name_profile == 'Coordinador')? 'list-group-item-success' : 'list-group-item-info'}}'},
                    @endforeach
                    @endif
                ],
                render: {
                    option: function(item, escape) {
                        {{-- class="item-seller '+escape(item.type)+'" --}}
                        return '<p>'+escape(item.name)+'</p>';
                    }
                }
            });
        }
    </script>
@stop