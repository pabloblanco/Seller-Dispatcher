@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
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
            <h4 class="page-title"> Estado de deudas y conciliaciones </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">Estado de deudas y conciliaciones</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Filtros</h3>
                <form id="debtstatus-form" method="POST" data-toggle="validator">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Fecha desde</label>
                                <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Fecha hasta</label>
                                <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy">
                            </div>
                        </div>

                        <div class="col-md-12 text-center p-b-20">
                            <button type="button" id="do-search" class="btn btn-success waves-effect">
                                Filtrar
                            </button>
                        </div>
                    </div>
                </form>

                 <div class="row" id="debtstatus-content" hidden="">
                    {{-- <div>
                        <button class="btn btn-success m-b-20" id="exportCsv" type="button">
                            Exportar CSV
                        </button>
                        <a href="#" style="display: none;" id="downloadfile"></a>
                    </div> --}}
                    <div class="col-md-12 p-b-20" id="list-debtstatus">

                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal modalAnimate" id="detailModalDebts" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="modal_close_btn close" data-dismiss="modal" id="modal_close_x" data-modal="#detailModalDebts">&times;</button>
                    <h4 class="modal-title" id='detailModalDebtsTitle'></h4>
                </div>
                <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

    <script type="text/javascript">

        function dateFormat(data){
            var date = new Date(data);
            var dateStr =  (date.getDate()<10?('0'+(date.getDate()).toString()):(date.getDate()).toString()) + "-" + (date.getMonth()+1<10?('0'+(date.getMonth() + 1).toString()):(date.getMonth() + 1).toString()) +"-" + date.getFullYear().toString();
            return dateStr;
        }

        function datebChange(limHasta){
            newDateB = $('#dateb').datepicker('getDate');

            newDateE = $('#dateb').datepicker('getDate');
            newDateE = newDateE.setDate(newDateE.getDate() + 4);

            if(newDateE > limHasta){
                newDateE = limHasta;
            }

            newDateBStr = dateFormat(newDateB);
            newDateEStr = dateFormat(newDateE);

            $('#dateb').datepicker('update',newDateBStr);
            $('#datee').datepicker('update',newDateEStr);
        }

        function dateeChange(limDesde){
            newDateE = $('#datee').datepicker('getDate');

            newDateB = $('#datee').datepicker('getDate');
            newDateB = newDateB.setDate(newDateB.getDate() - 4);

            if(newDateB < limDesde){
                newDateB = limDesde;
            }

            newDateBStr = dateFormat(newDateB);
            newDateEStr = dateFormat(newDateE);

            $('#dateb').datepicker('update',newDateBStr);
            $('#datee').datepicker('update',newDateEStr);
        }

        seeRegisters = (e) => {
          e.preventDefault();
          var register = $(e.currentTarget).data('register');
          var type = $(e.currentTarget).data('type');
          switch(type){
            case 'UPS': title='ventas'; break;
            case 'REC': title='registros'; break;
            case 'DEP': title='depositos'; break;
            default: title='registros';
          }

          if(register && register != ''){
              if(!$('.'+register).is(':visible')){
                  $('.'+register).attr('hidden', null);
                  $(e.currentTarget).text('Ocultar '+title);
              }
              else{
                  $('.'+register).attr('hidden', true);
                  $(e.currentTarget).text('Ver '+title);
              }
          }
        }


        seeDetails = (e) => {
          e.preventDefault();
          var detail = $(e.currentTarget).data('detail');
          if(detail && detail != ''){
              console.log($('.'+detail));
              if(!$('.'+detail).is(':visible')){
                  $('.'+detail).attr('hidden', null);
                  $(e.currentTarget).text('Ocultar detalles');
              }
              else{
                  $('.'+detail).attr('hidden', true);
                  $(e.currentTarget).text('Ver detalles');
              }
          }
        }


        detailModalDebts = (type,id,date,user_type) => {

            if(type && type!='' && id && id != ''){
                $('.preloader').fadeIn();
                switch(type){
                    case 'UPS':
                        url = '{{route('coordination.reportgetDebtStatusUps')}}'
                        title = 'Altas del dia '+date;
                    break;
                    case 'REC':
                        url = '{{route('coordination.reportgetDebtStatusRec')}}'
                        title = 'Efectivo recibido el dia '+date;
                    break;
                    case 'DEL':
                        url = '{{route('coordination.reportgetDebtStatusDel')}}'
                        title = 'Efectivo entregado el dia '+date;
                    break;
                    case 'DEP':
                        url = '{{route('coordination.reportgetDebtStatusDep')}}'
                        title = 'Depositos Conciliados del dia '+date;
                    break;
                    default:
                        url = '';
                }

                if(url !== ''){
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        async: true,
                        url: url,
                        method: 'POST',
                        data: {id: id, user_type:user_type},
                        dataType: 'json',
                        success: function (res) {
                            $(".preloader").fadeOut();
                            if(res.success){
                                $('#detailModalDebts .modal-body').html(res.html);
                                $('#detailModalDebts #detailModalDebtsTitle').html(title);
                                $('.seeRegisters').on('click', seeRegisters);
                                $('.seeDetails').on('click', seeDetails);
                                $('#detailModalDebts').modal({backdrop: 'static', keyboard: false});
                            }else{
                                $(".preloader").fadeOut();
                                showMessageAjax('alert-danger',res.msg);
                            }
                        },
                        error: function (res) {
                            $(".preloader").fadeOut();
                            showMessageAjax('alert-danger','No se pudo consultar el detalle del Historico.');
                        }
                    });
                }
                else{
                    $(".preloader").fadeOut();
                    showMessageAjax('alert-danger','No se pudo consultar el detalle del Historico.');
                }
            }
        }

        function loadList(form = '', action = ''){
                $(".preloader").fadeIn();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    async: true,
                    url: "{{route('coordination.reportgetDebtStatus')}}",
                    method: 'POST',
                    data: $('#'+form).serialize(),
                    dataType: 'json',
                    success: function (res) {
                        $(".preloader").fadeOut();

                        if(res.success){
                            //console.log(res.html);
                            $('#list-debtstatus').html(res.html);
                            $('#debtstatus-content').attr('hidden', null);
                        }else{
                            showMessageAjax('alert-danger', res.msg);
                        }
                    },
                    error: function (res) {
                        $(".preloader").fadeOut();
                        showMessageAjax('alert-danger', 'No se pudo cargar el historico.');
                    }
                });
            }

        $(function () {

            var limDesde = new Date(new Date().setHours(0, 0, 0, 0));
            limDesde = limDesde.setDate(limDesde.getDate() - 30);
            var limDesdeStr =  dateFormat(limDesde);

            var initDesde = new Date();
            initDesde = initDesde.setDate(initDesde.getDate() - 5);
            var initDesdeStr =  dateFormat(initDesde);

            var limHasta = new Date(new Date().setHours(23, 59, 59, 0));
            limHasta = limHasta.setDate(limHasta.getDate() - 1)
            var limHastaStr =  dateFormat(limHasta);

            var initHasta = new Date();
            initHasta = initHasta.setDate(initHasta.getDate() - 1);
            var initHastaStr =  dateFormat(initHasta);

            var config = {
                autoclose: true,
                format: 'dd-mm-yyyy',
                language: 'es',
                todayHighlight: true,
                orientation: 'bottom',
                startDate: limDesdeStr,
                endDate: limHastaStr
            }

            $('#dateb').datepicker(config).on('changeDate', function(e) {
                datebChange(limHasta);
            });

            $('#dateb').datepicker('update',initDesdeStr);

             $('#dateb').on('change',function(){
                if(!$('#dateb').datepicker('getDate')){
                    $('#dateb').datepicker('update',limDesdeStr);
                    datebChange(limHasta)
                }
            })


            $('#datee').datepicker(config).on('changeDate', function(e) {
                dateeChange(limDesde);
            });

            $('#datee').on('change',function(){
                if(! $('#datee').datepicker('getDate')){
                    $('#datee').datepicker('update',limHastaStr);
                    dateeChange(limDesde)
                }
            })

            $('#datee').datepicker('update',initHastaStr);

            $('#detailModalDebts .close').on('click', function (event){
                $('#detailModalDebts .modal-body').html('');
                $('#detailModalDebts #detailModalDebtsTitle').html('');
            });

            $('#do-search').on('click', function(e){
                loadList('debtstatus-form');
            });

            loadList('debtstatus-form');
        });
    </script>
@stop