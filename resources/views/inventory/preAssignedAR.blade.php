@extends('layouts.admin')

@section('customCSS')
  <style>
    .swal-text{
      text-align: center;
    }
  </style>
@stop

@section('content')
@include('components.messages')
@include('components.messagesAjax')

@php
$types = [
  'H' => 'Internet Hogar',
  'T' => 'Telefonía',
  'M' => 'Mifi',
  'F' => 'Fibra'
]
@endphp

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
    <h4 class="page-title"> Aceptar o Rechazar Inventario</h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
    <ol class="breadcrumb">
      <li><a href="#">Dashboard</a></li>
      <li class="active">Aceptar o Rechazar Inventario</li>
    </ol>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">Lista Articulos Pre-asignados Pendientes.</h3>
      @if($articles->count())
      <div id="sales-list" class="row">
        <div class="table-responsive" id="list-article">
          <table class="table table-bordered table-dn-noty">
            <thead>
              <tr>
                <th>MSISDN</th>
                <th>Equipo</th>
                <th>EMEI</th>
                <th>Tipo</th>
                <th>Fecha de PreAsignación</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($articles as $article)
              <tr class="item">
                <td>
                  {{ $article->msisdn }}
                </td>

                <td>
                  {{ $article->title }}
                </td>

                <td>
                  {{ $article->imei }}
                </td>

                <td>

                  {{ !empty($types[$article->type]) ? $types[$article->type] : 'Otro' }}
                </td>

                <td>
                  {{ date('d-m-Y', strtotime($article->date_reg)) }}
                </td>

                <td>
                    <button class="btn btn-success waves-effect waves-light btn-action" id="aceptPreAssign" data-id="{{$article->id}}" type="button">
                      Aceptar
                    </button>
                    <button class="btn btn-danger waves-effect waves-light btn-action" id="rejectPreAssign" data-id="{{$article->id}}" type="button">
                      Rechazar
                    </button>
                </td>

              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @else
      <div class="row">
        <div class="alert alert-danger">
          <p>No se consiguio inventario pre-asignado pendiente</p>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@stop

@section('scriptJS')
<script src="{{ asset('js/sweetalert.min.js') }}"></script>
<script type="text/javascript">
  function confirmationAction(btn){
    if(btn.prop('id') == 'aceptPreAssign'){
        act = 'A';
        title = "Acepta que el articulo sea asignado a su inventario disponible";
        text = "esta acción no tiene reverso.";
        content = null;
      }
      if(btn.prop('id') == 'rejectPreAssign'){
        act = 'R';
        title = "¿Esta seguro que desea rechazar el articulo y que no sea asignado a su inventario?";
        text = "Si es asi, escriba el motivo del rechazo \n esta acción no tiene reverso.";
        content = {
                element: "textarea",
                attributes: {
                    id: "reason",
                    maxLength: "255"
                }
        };
      }

      if(act=='A' || act=='R'){
        swal({
            title: title,
            text: text,
            icon: "warning",
            dangerMode: true,
            content : content,
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
            },
            closeOnClickOutside: false,
            closeOnEsc: false
        })
        .then((value) => {

          if(value == 'cancelar'){
            return;
          }
          else{
            if(act=='R'){
              sw=0;
              if($('textarea#reason').val().trim().length < 5){
                swal({
                    text: "Debes indicar un motivo de rechazo valido de al menos 5 caracteres.",
                    icon: "error",
                    buttons: {
                        confirm: {
                            text: 'Aceptar',
                            visible: true,
                            value: 'ok'
                        }
                    }
                }).then(() => {
                    confirmationAction(btn);
                });
              }
              else{
                sw = 1
              }
            }
            else{
              sw= 1
            }

            if(sw==1){
                id = btn.data('id');
                if(act == 'A'){
                  url = "{{ route('inventory.acceptPreassignedInv') }}";
                  data = {
                      _token: "{{ csrf_token() }}",
                      id: id
                    }
                }
                else{
                  console.log('asd');
                  url = "{{ route('inventory.rejectPreassignedInv') }}";
                  reason = $('textarea#reason').val().trim();
                  data = {
                      _token: "{{ csrf_token() }}",
                      id: id,
                      reason:reason
                  }
                }
                $(".preloader").fadeIn();
                $.ajax({
                    url: url,
                    type: 'post',
                    data: data,
                    dataType: "json",
                    cache: false,
                    success: function (res) {
                        if(res.success){
                          //console.log(res);
                          $('.btn-action[data-id='+res.id+']').prop('disabled',true)
                          swal({ text: res.msg, icon: "success", button: "OK" });
                        }
                        else{
                          swal({ title: "Ocurrio un error", text: res.msg, icon: "error", button: "Aceptar", dangerMode:true });
                        }
                        $(".preloader").fadeOut();
                    },
                    error: function (res) {
                        console.log(res);
                        alert('Hubo un error');
                        $(".preloader").fadeOut();
                    }
                });
            }
          }
        });
      }
  }
  $(function() {
    $('.btn-action').on('click',function(){
      confirmationAction($(this));
    });


  });

</script>
@stop
