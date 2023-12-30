@extends('layouts.ajax')

@section('ajax')
    @if($service->status)
    <script type="text/javascript">
        $(function () {
            $("#btnpro").click(function(){
                var divnameMod = "#modal-"+$("#sltService").val();
                var divnMod = "#m-"+$("#sltService").val();
                $(".hideMod").hide();
                $(".serModDiv").hide()
                $(divnameMod).show();
                $(divnMod).show();
                $("#myModal").modal({
                backdrop: 'static'
                });
            });
            
            // Attach initialized event to it
            {{-- urlPay = "{{route('charger.process')}}"; --}}
            $("#sltService").on('change',function(evt, data){
                //$('.loading-ajax').show();
                if($("#sltService").val()!=""){
                    $(".serviDiv").hide();
                    var divname = "#"+$("#sltService").val();
                    $('#btnpro').prop('disabled', false);
                    $(divname).show();
                    $('#priceserv').val($("#sltService option[value = '"+$("#sltService").val()+"']").data('price'));

                }else{
                    $('.loading-ajax').hide();
                    $(".serviDiv").hide();
                    $('#btnpro').prop('disabled', true);
                    showMessageAjax('alert-danger', 'Debe Seleccionar un servicio.');
                    /*$('#msgAjax').addClass('alert-danger').show();
                    $('#txtMsg').text('Debe Seleccionar un servicio.');
                    setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);*/
                    $('#priceserv').val("0");
                }
            });


        });
    </script>
    @endif
    @if($service->status == false)
        <div class="alert alert-danger">
            <ul>
                <li>{{ $service->message }}</li>
            </ul>
        </div>  
    @else
        <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog" style="margin-top: 180px !important;">
    
      @foreach($service->elements as $serv)
          <!-- Modal content-->
          <div class="modal-content hideMod" id="modal-{{$serv->id}}" style="display: none;">
            <div class="modal-header" style="padding:35px 50px;">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4><span class="glyphicon glyphicon-lock"></span> Confirmación de la recarga</h4>
            </div>
            <div class="modal-body" style="padding:40px 50px;">
                <div class="row" style="margin-left: 10px;">
                    <div class="serModDiv m-t-20" id="m-{{$serv->id}}" style="display: none;">
                        <div class="row">
                            <div class="col-lg-12">
                                <h5>MSISDN</h5> <b>{{$service->msisdn}}</b>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <h5>Servicio</h5> <b>{{$serv->title}}</b>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <h5>Descripción</h5> <b>{{$serv->description}}</b>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <h5>Precio:</h5> <b>$<?php echo number_format($serv->price,2,',','.'); ?></b>
                            </div>
                        </div>
                    </div>
                </div><br><br>
                  <button type="submit" class="btn btn-success btn-block" id="btncharger"><span class="glyphicon glyphicon-off"></span> Procesar</button>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-danger btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
            </div>
          </div>
          <!-- Modal content-->
      @endforeach

    </div>
  </div>
  <!-- Modal -->
        <div class="col-lg-12">
            <div class="input-group">
                <select class="form-control" name="sltService" id="sltService" data-style="form-control">
                    <option value="" selected>[Selecciona un servicio]</option>
                @foreach($service->elements as $serv)
                    <option data-price="{{$serv->price}}" value="{{$serv->id}}">{{$serv->title}} - $<?php echo number_format($serv->price,2,'.',','); ?></option>
                 @endforeach
                </select>
                <input type="hidden" name="hidmsisdn" value="{{$service->msisdn}}">
                <input type="hidden" name="hitransaction" value="{{$service->transaction}}">
                <input type="hidden" name="hilat" value="{{!empty($service->lat) ? $service->lat : ''}}">
                <input type="hidden" name="hilng" value="{{!empty($service->lng) ? $service->lng : ''}}">
                <input type="hidden" name="hitok" value="{{$service->token}}">
                <input type="hidden" id='priceserv' name="priceserv" value="0">
            </div>
        </div>
        <div class="row" style="margin-left: 20px;"> 
            @foreach($service->elements as $serv)
            <div class="serviDiv m-t-20" id="{{$serv->id}}" style="display: none;">
                <div class="row">
                    <div class="col-lg-12">
                        <h5>Descripción</h5> {{$serv->description}}
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <h5>Precio:</h5> $<?php echo number_format($serv->price,2,'.',','); ?>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="col-lg-12 m-t-20">
            <div class="input-group">
                <button type="button" id="btnpro" disabled="true" class="btn btn-success waves-effect waves-light show-confirmation">
                            Procesar Recarga
                </button>
            </div>
        </div>
        
    @endif
@stop

    