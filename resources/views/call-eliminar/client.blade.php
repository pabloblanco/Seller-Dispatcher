@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css') }}">
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
            <h4 class="page-title"> Consulta de cliente </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Call Center</a></li>
                <li class="active">Cliente.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Buscar cliente</h3>
                <form class="form-horizontal" id="Salesclientform" method="POST" action="" data-toggle="validator">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <div class="col-md-12">
                            <div id="scrollable-dropdown-menu">
                                <input class="typeahead form-control" id="buscar"  name="buscar" type="text" placeholder="Escribe el número netwey o nombre del cliente">
                                <div class="help-block with-errors"></div>
                            </div>
                            {{--<input type="hidden" name="seller" id="seller" value="">--}}
                        </div>
                    </div>
                </form>
                <div id="client" class="hidden">
                    <div class="row hidden p-b-20">
                    	<h3 class="box-title">Datos del cliente</h3>
                    	<div class="col-md-12">
    	                	<ul class="list-icons" id="data-client"></ul>
                        </div>
                    </div>

                    <div class="row p-b-20">
                        <h3 class="box-title">Estatus la linea</h3>
                        <div class="col-md-12">
                            <ul class="list-icons" id="status-line">
                                <li>
                                    <i class="ti-angle-right"></i> 
                                    <strong>Nombre:</strong> <span class="name_seller">zxczx</span>
                                </li>
                                <li>
                                    <i class="ti-angle-right"></i> 
                                    <strong>Teléfono:</strong> <span class="phone_seller">zxcx</span>
                                </li>
                                <li>
                                    <i class="ti-angle-right"></i> 
                                    <strong>Email:</strong> <span class="email_seller">xcv</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="row p-b-20">
                        <h3 class="box-title">&Uacute;ltima recarga</h3>
                        <div class="col-md-12">
                            <ul class="list-icons" id="">
                                <li>
                                    <i class="ti-angle-right"></i> 
                                    <strong>Nombre:</strong> <span class="name_seller">zxczx</span>
                                </li>
                                <li>
                                    <i class="ti-angle-right"></i> 
                                    <strong>Teléfono:</strong> <span class="phone_seller">zxcx</span>
                                </li>
                                <li>
                                    <i class="ti-angle-right"></i> 
                                    <strong>Email:</strong> <span class="email_seller">xcv</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-danger waves-effect waves-light m-l-10">Dar de baja</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <!-- typehead TextBox Search -->
    <script src="{{ asset('plugins/bower_components/typeahead.js-master/dist/typeahead.bundle.min.js') }}"></script>

    <script type="text/javascript">
        $(function () {
        	var route = "{{route('call.clientAjax')}}";

        	var clients = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('namePhone'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                prefetch: route,
                remote: {
                    url: route+'/%QUERY',
                    wildcard: '%QUERY'
                }
            });

        	var myTypeahead = $('.typeahead').typeahead(null, {
                name: 'clients',
                limit: 10,
                minLength: 3,
                display: 'namePhone',
                source: clients
            });

            myTypeahead.on('typeahead:selected',function(evt, data){
            	var number = data.msisdn ? data.msisdn.trim() : '';
            	if(number != ''){
            		$.post("{{route('call.getClient')}}",{number: number, _token: "{{ csrf_token() }}"}, function(data){
            			if(!data.error){
                            var html = '';
                            if(data.client){
                                html = '<li><i class="ti-angle-right"></i><strong>Nombre:</strong> <span class="name_seller">'+data.client.name+'</span></li>';
                                html += '<li><i class="ti-angle-right"></i><strong>Apellido:</strong> <span class="name_seller">'+data.client.last_name+'</span></li>';
                                html += '<li><i class="ti-angle-right"></i><strong>Email:</strong> <span class="name_seller">'+data.client.email ? data.client.email : 'N/R'+'</span></li>';
                                html += '<li><i class="ti-angle-right"></i><strong>Teléfono:</strong> <span class="name_seller">'+data.client.phone_home+'</span></li>';
                                html += '<li><i class="ti-angle-right"></i><strong>Dirección:</strong> <span class="name_seller">'+data.client.address ? data.client.address : 'N/R'+'</span></li>';
                                $('#data-client').html(html);
                            }

                            if(data.showLastRecharge){

                            }
            				console.log(data);
            			}else{
            				if(data.message == 'TOKEN_EXPIRED'){
	                            showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
	                        }else{
                                showMessageAjax('alert-danger',data.message);
	                        }
            			}
            		},'json');
            	}else{
            		showMessageAjax('alert-danger','Debe seleccionar un cliente');
            	}
            });
        });
    </script>
@stop