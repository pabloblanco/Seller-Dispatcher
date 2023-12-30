@extends('layouts.admin')

@section('customCSS')
  <link href="{{ asset('css/selectize.css') }}" rel="stylesheet">
  <link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet">
@stop

@section('content')
    @include('components.messages')
    @include('components.messagesAjax')

    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"> Migraciones </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Migraciones</a></li>
                <li class="active">Home</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Datos iniciales para el proceso de migraci&oacute;n</h3>

                <form class="form-horizontal" id="migrationform" name="migrationform" method="POST" data-toggle="validator">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="col-md-12">MSISDN</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control dns" id="dn1" name="dn1" placeholder="Ingresar el MSISDN Netwey" minlength="10" maxlength="10" pattern="^[0-9]{10}$" required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-12">Repetir MSISDN</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control dns" id="dn2" name="dn2" placeholder="Ingresar nuevamente el MSISDN Netwey" minlength="10" maxlength="10" pattern="^[0-9]{10}$" data-match="#dn1" data-match-error="Los MSISDNs deben coincidir" required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success waves-effect waves-light m-r-10">Consultar</button>
                </form>

                <div id="client-data"></div>
                
                @if($hasinv)
                <div id="newdn-data" hidden="true">
                    <h3 class="box-title pt-4">Nuevo MSISDN</h3>
                    <div class="row">
                        <form class="form-horizontal" id="migrationdnform" name="migrationdnform" method="POST" data-toggle="validator">
                            <div class="form-group">
                                <label class="col-md-12">Ingrese el nuevo MSISDN</label>
                                <div class="col-md-12">
                                    <select class="form-control" id="newdn" name="newdn" placeholder="Ingrese el nuevo MSISDN">
                                        {{--@foreach($packs[0]->articles as $item)
                                        <option 
                                            value="{{ $item->msisdn }}"
                                            data-desc="{{ $packs[0]->description }}">
                                                {{ $item->msisdn }}
                                        </option>
                                        @endforeach--}}
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success waves-effect waves-light">Procesar migraci&oacute;n</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de edición --}}
    <div id="confirm-m-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="confirm-m-modallabel" aria-hidden="true">
        <div class="modal-dialog" style="top: 10%;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Confirmar Migiración.</h4>
                </div>
                <div class="modal-body">
                    <div class="col-sm-12 col-md-6">
                        <label>INE:</label>
                        <p id="ine-client"></p>
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <label>Nombre:</label>
                        <p id="name-client"></p>
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <label>Plan:</label>
                        <p id="plan-client"></p>
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <label>MSISDN:</label>
                        <p id="msisdn-client"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default waves-effect" id="closecm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success waves-effect waves-light m-r-10" id="doMigration">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
@stop
@section('scriptJS')
    <script src="{{ asset('js/validator.js') }}"></script>
    <script src="{{ asset('js/selectize.js')}}"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>

    <script type="text/javascript"> 
        $(function () {
            let opts = [];
            let selectize;

            @if($hasinv)
                @foreach($packs[0]->articles as $item)
                    opts.push({
                        value: "{{ $item->msisdn }}",
                        desc: "{{ $packs[0]->description }}",
                        pack: "{{ $packs[0]->id }}",
                        service: "{{ $packs[0]->service->id }}",
                        text: "{{ $item->msisdn }}"
                    });
                @endforeach
            @endif

            if($('#newdn').length){
                let $select = $('#newdn').selectize();
                selectize = $select[0].selectize;
                
                opts.forEach(function(e){
                    selectize.addOption(e);
                });

                selectize.refreshOptions();
            }

            $('.dns').on('paste', function(e){
                e.preventDefault();
            });

            $('.dns').on('change', function(e){
                if($('#newdn-data').is(':visible') && $('#dn1').val() != $('#dn2').val()){
                    resetMigration();
                }
            });

            $('#migrationform').on('submit', function(e){
                e.preventDefault();

                getClient();
            });

            let resetMigration = function(){
                $('#client-data').html('');
                $('#newdn-data').attr('hidden', true);
                $('#dn2').val('');
            }

            let setEditModal = function(){
                $('#editclientformodal').validator();
                $('#editclientformodal').validator().unbind('submit');

                $('#editclientformodal').validator().on('submit', function(e){
                    if(!e.isDefaultPrevented()){
                      e.preventDefault();

                      $('.loading-ajax').fadeIn();

                      doPostAjax(
                        '{{ route('seller.updateClientM') }}',
                        function(res){
                            if(!res.error){
                                getClient();
                            }else{
                                $('.loading-ajax').fadeOut();
                                showMessageAjax('alert-danger',res.msg);
                            }
                            $('#closeEditM').trigger('click');
                        },
                        $(this).serialize()
                      );
                    }
                });
            }

            let getClient = function(){
                $('.loading-ajax').fadeIn();

                doPostAjax(
                  '{{ route('seller.findClientMigration') }}',
                  function(res){
                      $('.loading-ajax').fadeOut();
                      
                      if(!res.error){
                        $('#client-data').html(res.html);
                        $('#newdn-data').attr('hidden', null);
                        setEditModal();
                      }else{
                        showMessageAjax('alert-danger','No se pudo encontrar el cliente.');
                      }
                  },
                  {dn: $('#dn2').val()},
                  $('meta[name="csrf-token"]').attr('content')
                );
            }

            $('#migrationdnform').validator().on('submit', function(e){
                if(!e.isDefaultPrevented()){
                    e.preventDefault();

                    let dn = $('#newdn').val().trim(),
                        desc = selectize.options[dn].desc;

                    if(dn != ''){
                        $('#ine-client').text($('#dni-c').text());
                        $('#name-client').text($('#name-c').text());
                        $('#msisdn-client').text(dn);
                        $('#plan-client').text(desc);
                        
                        $('#confirm-m-modal').modal('show');
                    }
                }
            });

            $('#doMigration').on('click', function(e){
                let dn = $('#newdn').val().trim(),
                    pack = selectize.options[dn].pack,
                    service = selectize.options[dn].service,
                    dn_mig = $('#dn2').val().trim();

                if(dn && pack && service){
                    $('.loading-ajax').fadeIn();

                    doPostAjax(
                        '{{ route('seller.doMigration') }}',
                        function(res){
                            $('.loading-ajax').fadeOut();
                            $('#confirm-m-modal').modal('hide');

                            if(!res.error){
                                resetMigration();

                                swal({
                                    title: "Exito",
                                    text: "Migración procesada exitosamente.",
                                    icon: "success",
                                    button: {text: "OK"}
                                });
                            }else{
                                showMessageAjax('alert-danger', res.msg);
                            }
                        },
                        {msisdn: dn, msisdn_old: dn_mig, pack: pack, service: service},
                        $('meta[name="csrf-token"]').attr('content')
                    );
                }
            });
        });
    </script>
@stop