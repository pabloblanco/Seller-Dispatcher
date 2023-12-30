@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.css') }}">
@stop

@section('content')
    @include('components.messages')
    @include('components.messagesAjax')

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
            <h4 class="page-title"> Lista de Clientes </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <!--<a href="https://themeforest.net/item/elite-admin-responsive-dashboard-web-app-kit-/16750820" target="_blank" class="btn btn-danger pull-right m-l-20 btn-rounded btn-outline hidden-xs hidden-sm waves-effect waves-light">Buy Now</a>-->
            <ol class="breadcrumb">
                <li><a href="#">Clientes</a></li>
                <li class="active">Listado de Clientes.</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                <h3 class="box-title">Clientes</h3>

                <div class="col-md-12">
                    <!--<form>-->
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
                    <!--</form>-->
                </div>

                <div id="client-list" class="col-sm-12 col-md-12">
                    @forelse ($data['clients'] as $client)
                        <div class="col-md-12">
                            <blockquote>
                                <label>Nombre:</label>
                                <p>{{$client->name}} {{$client->last_name}}</p>
                                <label>Tel&eacute;fono:</label>
                                <p>{{$client->phone_home}}</p>
                                <label>DN:</label>
                                <p>{{$client->msisdn}}</p>

                                @if(hasPermit('ECD-DSE'))
                                <a href="{{$client->urledit}}" class="edit btn btn-info btn-circle btn-lg waves-effect font-23 m-r-10">
                                    <i class="zmdi zmdi-edit"></i>
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
                    </div>
                @endif
            </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>

    <script type="text/javascript">
        $(function () {
            var urlList = '';
            @if(hasPermit('LCN-DSE'))
                urlList = '{{route('client.listClient')}}';
            @endif

            $('#search').on('click', function(event){
                var text = $('#searchText').val();
                    
                if(text != '' && urlList != ''){
                    window.location = urlList+'/1/'+text;
                }else{
                    window.location = urlList;
                }
            });
            $('#searchText').keyup(function(e){
                if(e.keyCode == 13)
                {
                    var text = $('#searchText').val();
                    if(text != '' && urlList != ''){
                        window.location = urlList+'/1/'+text;
                    }else{
                        window.location = urlList;
                    }
                }
            });
            bindEvents();
        });

        function bindEvents(){
            $('.page').on('click', function(event){
                event.preventDefault();
                var href = $(event.currentTarget).prop('href');
                $.get(href, function(data){
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
                            htmlList += '<label>Netwey:</label> <p>'+ele.msisdn+'</p>';
                            if(data.canEdit){
                                htmlList += '<a href="'+ele.urledit+'" class="edit btn btn-info btn-circle btn-lg waves-effect font-23 m-r-10">';
                                htmlList += '<i class="zmdi zmdi-edit"></i>';
                                htmlList += '</a>';
                            }
                            htmlList += '</blockquote> </div>';
                        });

                        $('#client-list').html(htmlList);
                        $('#client-pag').html(htmlpg);
                        bindEvents();
                    }
                    $(".preloader").fadeOut();
                }, 'json');
            });

            $('.activate-loader').on('click', function(event){
                $(".preloader").fadeIn();
            });
        }
    </script>
@stop