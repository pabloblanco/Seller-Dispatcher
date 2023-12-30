@php
	$iconBundle = [
		'H'=> 'fa-home',
		'T'=> 'fa-mobile',
		'M'=> 'fa-globe',
		'MH'=> 'fa-map-o',
		'F'=> 'fa-wifi'
	];
	$labelBundle = [
		'H'=> 'Hogar',
		'T'=> 'Telefonia',
		'M'=> 'Mifi',
		'MH'=> 'Mifi Huella',
		'F'=> 'Fibra'
	];
	/////-------------------
	$statusLabelBundle = [
		'EC'=> 'Por activar...',
		'PA'=> 'Por aprovisionar...',
		'P'=> 'Activado!',
		'E'=> 'Fallo!',
		'A'=> 'Pendiente'
	];
	$statusIconBundle = [
		'EC'=> 'fa-clock-o',
		'PA'=> 'fa-clock-o',
		'P'=> 'fa-thumbs-up',
		'E'=> 'fa-exclamation-triangle',
		'A'=> 'fa-sitemap'
	];

	switch ($component->status) {
		case 'EC':
			$borderStile = "2px solid #f0ad4e";
			$backgroundStile = "#fcf8e3";
		break;
		case 'PA':
			$borderStile = "2px solid #f0ad4e";
			$backgroundStile = "#fcf8e3";
		break;
		case 'E':
			$borderStile = "2px solid #d9534f";
			$backgroundStile = "#f2dede";
		break;
		case 'P':
			$borderStile = "2px solid #5cb85c";
			$backgroundStile = "#dff0d8";
		break;
	}

	$idContaint = str_replace("=","",base64_encode($component->config."-".$component->id));
@endphp
<div class="bundle-component-container col-12 py-3 mx-2 my-2" id="" style="border: {{$borderStile}}; background-color: {{$backgroundStile}};">

	<div class="loading-bundle-component">
    <div >
    	{{--<i class="fa fa-spin fa-circle-o-notch"></i>--}}
    	<i class="fa fa-spin fa-spinner"></i>
    </div>
  </div>

	<div class="row text-center">
		<div class="col-6">
			<label><strong>Producto:</strong></label>
		</div>
		<div class="col-6">
			<i aria-hidden="true" class="fa {{$iconBundle[$component->dn_type]}}"></i>
			<label>{{$labelBundle[$component->dn_type]}}</label>
		</div>
	</div>
	<div class="row text-center">
		<div class="col-6" >
			<label><strong>Msisdn:</strong></label>
		</div>
		<div class="col-6">
			<label>{{$component->msisdn}}</label>
		</div>
	</div>
	<div class="row text-center">
		<div class="col-6">
			<label><strong>Status:</strong></label>
		</div>
		<div class="col-6">
			<label>{{$statusLabelBundle[$component->status]}}</label>
			<i class="fa {{$statusIconBundle[$component->status]}}" aria-hidden="true"></i>
		</div>
	</div>
	<div class="row text-center align-items-center">

		@php
			if(!empty($component->obs_activate)){
				$obsact = json_decode(json_encode($component->obs_activate));
				$codeErr = $obsact->code;
				$msgErr = $obsact->message;
			}
			else{
				$codeErr = null;
				$msgErr = "";
			}
		@endphp
		@if(
			$component->config == 'children'
			||
			(
				$component->config == 'master'
				&&
				$component->status!='E'
				||
				($component->status=='E' && ($codeErr == 'FAIL_MAC' || $codeErr == 'EMPTY_IP'))
			)
		)
		<div class="col-6">
			<label><strong>Acciones:</strong></label>
		</div>
		<div class="col-6">
			@if($component->status=='E' && ($component->config == 'children' || ($component->config == 'master') && ($codeErr == 'FAIL_MAC' || $codeErr == 'EMPTY_IP')))
			<button class="btn btn-danger py-1" style="background: #d9534f;border: #d9534f;" onclick="ViewFail('{{$component->id}}','{{$component->dn_type}}','{{$codeErr}}','{{$msgErr}}')">Ver detalles</button>
			@else
			<label>---</label>
			@endif
		</div>
		@else
		<div class="col-12">
			<label><strong>{!!$msgErr!!}</strong></label>
		</div>
		@endif
	</div>
</div>
