@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}"/>
    <link href="{{ asset('css/selectize.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet"/>
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
            <h4 class="page-title"> Instalaciones de Fibra por cobrar </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Ventas</a></li>
                <li class="active">Instalaciones de Fibra por cobrar.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        @if($lock->is_locked == 'Y')
          <div class="col-md-12">
            <div class="white-box">
              <div class="alert alert-danger">
                <p><b>Has sido bloqueado</b>, por favor comunicate con tu supervisor.</p>
              </div>
            </div>
          </div>
        @else
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                <h3 class="box-title">&nbsp;</h3>

                <div class="col-md-12 p-b-20">
                    <div class="col-sm-12 col-md-6 col-lg-4 m-b-20">
                        <div class="form-group">
                            <select class="form-control" id="status" name="status">
                                <option value="">Todos los Estatus</option>
                                <option value="A">Por Instalar</option>
                                <option value="P">Instalado</option>
                                <option value="R">Reprogramado</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 text-center">
                        <button type="button" name="search" id="search" class="btn btn-success waves-effect waves-light m-r-10">Consultar</button>
                    </div>
                </div>

               <div id="client-list" class="col-sm-12 col-md-12">
                    @forelse ($data['registers'] as $install)
                        <div class="col-md-12">
                            <blockquote>
                                <label>Cliente:</label>
                                <p>{{$install->name}} {{$install->last_name ?? ''}}</p>
                                <label>Tel&eacute;fono:</label>
                                <p>{{$install->phone_home}}</p>
                                <label>Direccion:</label>
                                <p>{{$install->address_instalation}}</p>
                                @if(!empty($install->date_install))
                                <label>Fecha de Instalación:</label>
                                <p>{{$install->date_install}}</p>
                                @endif
                                <label>Estatus:</label>
                                <p>{{$install->status}}</p>

                                <button type="button" data-toggle="modal" data-id="{{$install->id}}" data-target="#detail-pending-pay-modal" class="btn btn-success waves-effect waves-light m-r-10 mt-3 mb-3">Ver detalle</button>

                                {{--  @if(hasPermit('EPD-DSE') && request()->route()->getName() == 'client.list')
                                <a href="{{$client->urledit}}" class="edit btn btn-info btn-circle btn-lg waves-effect font-23 m-r-10">
                                    <i class="zmdi zmdi-edit"></i>
                                </a>
                                @endif --}}
                                {{--
                                @if(hasPermit('CDV-DSE'))
                                <a href="{{$client->schedule}}" class="edit btn btn-success btn-circle btn-lg waves-effect font-23">
                                    <i class="zmdi zmdi-calendar"></i>
                                </a>
                                @endif --}}
                            </blockquote>
                        </div>
                    @empty
                        <div class="col-md-12">
                            <blockquote>
                                <p>No hay Instalaciones de Fibra por cobrar.</p>
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
        @endif
    </div>

    <div id="detail-pending-pay-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Detalle de la instalaci&oacute;n</h4>
                    </div>
                    <div class="modal-body" id="detail-pending-paid--content"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default waves-effect" id="close-modal-paid-detail-inst" data-dismiss="modal">Cerrar</button>
                        {{-- <button type="button" class="btn btn-success waves-effect" id="mark-as-paid">Marcar como pagada</button> --}}
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

            urlList = '{{route('sellerFiber.payPending')}}';

            doSearch = function(){
                var status = $('#status').val().trim();

                if(urlList != '' && (status != '')){
                    var search = '';
                    if(status != '')
                        search += 'status='+status;
                    window.location = urlList+'/1/'+search;
                }else{
                    window.location = urlList;
                }
            }

            $('#search').on('click', function(event){
                doSearch();
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
                    success: function(data){
                        if(data && data.registers.length > 0){
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
                            data.registers.forEach(function(ele){
                                htmlList += '<div class="col-md-12"> <blockquote>';
                                htmlList += '<label>Cliente:</label> <p>'+ele.name+' '+ele.last_name+'</p>'
                                htmlList += '<label>Tel&eacute;fono:</label> <p>'+ele.phone_home+'</p>'
                                htmlList += '<label>Direcci&oacute;n:</label> <p>'+ele.address_instalation+'</p>'
                                htmlList += '<label>Fecha de Instalación:</label> <p>'+ele.date_install+'</p>'
                                htmlList += '<label>Estatus:</label> <p>'+ele.status+'</p>'


                                htmlList += '<button type="button" data-toggle="modal" data-id="'+ele.id+'" data-target="#detail-pending-pay-modal" class="btn btn-success waves-effect waves-light m-r-10 mt-3 mb-3">Ver detalle</button>';
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
        }


        @if(hasPermit('SEL-FIB'))
            $('#detail-pending-pay-modal').on('hide.bs.modal', function(event) {
                $('#detail-pending-paid--content').html('');
            });

            $('#detail-pending-pay-modal').on('shown.bs.modal', function(event) {
                let idInstall = $(event.relatedTarget).attr('data-id');

                $('.loading-ajax').show();

                doPostAjax(
                    '{{ route('sellerFiber.detailPendingPaidInsModal') }}',
                    function(res){
                        $('.loading-ajax').fadeOut();

                        if(!res.error){
                            $('#detail-pending-paid--content').html(res.html);
                        }else{
                            if(res.message == 'TOKEN_EXPIRED'){
                                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                            }else{
                                showMessageAjax('alert-danger', res.message);
                                $('#close-modal-paid-detail-inst').trigger('click');
                            }
                        }
                    },
                    {id: idInstall},
                    $('meta[name="csrf-token"]').attr('content')
                );
            });

            $('#mark-as-paid').on('click', function(){
                swal({
                    title: "¿Seguro que desea marcar esta instalación como pagada?",
                    text: "Esta acción no tiene reverso.",
                    icon: "warning",
                    dangerMode: true,
                    buttons: {
                        cancel: {
                            text: 'Cancelar',
                            visible: true,
                            value: 'cancelar'
                        },
                        confirm: {
                            text: 'Aceptar',
                            visible: true,
                            value: 'ok'
                        }
                    }
                })
                .then((value) => {
                    if(value == 'ok'){
                        $('.loading-ajax').fadeIn();

                        let idInstall = $('#form-paid-install').attr('data-id');

                        doPostAjax(
                            '{{ route('sellerFiber.markAsPaidInstall') }}',
                            function(res){
                                $('#close-modal-paid-detail-inst').trigger('click');
                                $('.loading-ajax').fadeOut();

                                if(!res.error){
                                    $('#row-date-paid-'+res.id).remove();
                                    showMessageAjax('alert-success', res.message);
                                }else{
                                    $('#close-modal-paid-detail-inst').trigger('click');
                                    if(res.message == 'TOKEN_EXPIRED'){
                                        showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                                    }else{
                                        showMessageAjax('alert-danger', res.message);
                                    }
                                }
                            },
                            {
                                id: idInstall
                            },
                            $('meta[name="csrf-token"]').attr('content')
                        );
                    }
                });
            });
        @endif
    </script>
@stop
