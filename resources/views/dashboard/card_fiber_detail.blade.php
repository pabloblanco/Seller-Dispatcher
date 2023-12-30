@extends('layouts.admin')
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
  <div class="col-lg-3 col-md-4 col-sm-4 col-12 text-sm-left text-center">
    <h4 class="page-title">
      Listado de citas. </br> Area: {{$operation}}
    </h4>
  </div>
  <div class="col-lg-9 col-md-8 col-sm-8 col-12 d-flex justify-content-sm-end justify-content-center">
    <ol class="breadcrumb">
      <li>
        <a href="{{route('dashboard')}}">
    			Dashboard
        </a>
      </li>
      <li class="active">
        Listado de citas.
      </li>
    </ol>
  </div>
</div>
@if(!empty($datacard))

<div class="row">
	<div class="col-sm-12">
		<div class="white-box px-4 text-center">
			AREA EN CONSTRUCCION
		</div>
	</div>
</div>
<div class="row justify-content-start">

	@php
    $hoy=0;
	@endphp
	@foreach($datacard as $date)
	  @php
	  	if(strtotime($date->date_instalation) < strtotime(date('d-m-Y')))
	  	{
        $stilo ="border: 2px solid #ad7d7d; background-color: #fdbdbd;";
      }elseif(strtotime($date->date_instalation) == strtotime(date('d-m-Y'))){
      	$hoy++;
	    	$stilo = "border: 1px red solid; background: rgba(251, 150, 120, 0.2);";
	    }else{
	    	$stilo = "";
	    }

		@endphp
		<div class="col-lg-3 col-md-4 col-sm-6">
	    <div class="white-box px-4 text-center" style="{{$stilo}}">
	      <h3 class="box-title" style="line-height: normal;">
	        Registro #{{$date->id}}
	      </h3>
	      <div class="col-sm-12 text-left">
	        <label>Cliente:</label>
	        <p id="txt-name_{{$date->id}}">{{$date->name}} {{$date->last_name ?? ''}}</p>
	      </div>
	      <div class="col-sm-12 text-left">
	        <label>Dirección:</label>
	        <p id="txt-address_{{$date->id}}">{{$date->address_instalation}}</p>
	      </div>
	      <div class="col-sm-12 text-left">
	        <label>Fecha de instalación:</label>
	        <p id="txt-date_{{$date->id}}">{{$date->date_instalation}}</p>
	      </div>
	      <div class="col-sm-12 text-left">
	        <label>Horario de instalación:</label>
	        <p id="txt-time_{{$date->id}}">{{$date->schedule}}</p>
	      </div>
	      <div class="col-sm-12 text-left">
	        <label>Instalador:</label>
	        <p id="txt-email_{{$date->id}}">{{(!empty($date->installer))? $date->installer : 'S/N'}}</p>
	      </div>
	      <div class="col-sm-12 text-left">
	        <label>Operación:</label>
	        <p id="txt-type_{{$date->id}}">{{$operation}}</p>
	      </div>
	      <button class="btn btn-success waves-effect waves-light m-r-10" data-id="{{$date->id}}" data-type="{{$type}}" data-target="#detail-install-modal" data-toggle="modal" type="button">
            Ver detalle
         </button>
	    </div>
	  </div>
	@endforeach
</div>
<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="detail-install-modal" role="dialog" style="display: none;" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
            ×
          </button>
          <h4 class="modal-title">
            Detalle de la cita de instalación
          </h4>
        </div>
        <div class="modal-body" id="detail-install-content">
        </div>
        <div class="modal-footer">
          <button class="btn btn-default waves-effect" data-dismiss="modal" id="close-modal-detail-inst" type="button">
            Cerrar
          </button>
          <button class="btn btn-success waves-effect" data-vtype="" id="go-to-install" type="button">
            ***
          </button>
        </div>
      </div>
    </div>
  </div>

<script type="text/javascript">
	$(function () {
		$('#detail-install-modal').on('hide.bs.modal', function(event) {
          $('#detail-install-content').html('');
    });

    $('#detail-install-modal').on('shown.bs.modal', function(event) {
      let idInstall = $(event.relatedTarget).attr('data-id');
      let typeInstall = $(event.relatedTarget).attr('data-type');

      $('.loading-ajax').show();

        doPostAjax(
          '{{ route('sellerFiber.detailInsModal') }}',
          function(res){
            $('.loading-ajax').fadeOut();

            if(!res.error){
                $('#detail-install-content').html(res.html);
                addEventsToModal();
                $('#calendar').datepicker('setDate', new Date(res.date_ins));
            }else{
                if(res.message == 'TOKEN_EXPIRED'){
                    showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                }else{
                    showMessageAjax('alert-danger', res.message);
                    $('#close-modal-detail-inst').trigger('click');
                }
            }
          },
          {
            id: idInstall,
            type: typeInstall
          },
          $('meta[name="csrf-token"]').attr('content')
        );
    });

	});
</script>
@else
	<div class="alert alert-danger">
    No hay datos para ser mostrados.
  </div>
@endif
@stop
