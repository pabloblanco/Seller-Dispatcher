<section >
	<h3 class="box-title col-md-12 p-t-10">
    Estatus de activación de {{ $isCombo==true ? "combo":"fibra"}}
  </h3>

  <div class="row align-items-center justify-content-center" id="carsBundle">
  	@php
  		$ActiveVerify=false;
  	@endphp
		@if(count($childrenBundle)>0)
			{{-- @php
			$iconBundle = [
				'H'=> 'fa-home',
				'T'=> 'fa-mobile',
				'M'=> 'fa-globe',
				'MH'=> 'fa-map-o',
				'F'=> 'fa-wifi'];
			$labelBundle = [
				'H'=> 'Hogar',
				'T'=> 'Telefonia',
				'M'=> 'Mifi',
				'MH'=> 'Mifi Huella',
				'F'=> 'Fibra'];
			/////
			$statusLabelBundle = [
				'EC'=> 'Por activar...',
				'P'=> 'Activado!',
				'E'=> 'Fallo!',
				'A'=> 'Pendiente'];
			$statusIconBundle = [
				'EC'=> 'fa-clock-o',
				'P'=> 'fa-thumbs-up',
				'E'=> 'fa-exclamation-triangle',
				'A'=> 'fa-sitemap'];
			@endphp --}}

			@foreach ($childrenBundle as $value)

				@php
				if($value->status != 'P'){
					$ActiveVerify=true;
				}


				// if($value->status=='E'){
				// 	$borderStile = "4px solid red";
				// 	$backgroundStile = "#e3d5d5";
				// }else{
				// 	$borderStile = "1px solid black";
				// 	$backgroundStile = "##ffffff";
				// }

				$idContaint = str_replace("=","",base64_encode($value->config."-".$value->id));
				@endphp

				<div class="col-lg-4 col-md-6 col-sm-12" id="{{$idContaint}}">
					@include('fiber.resultBundleComponent',['component' => $value])
				</div>

				{{-- <div class="col-lg-4 col-md-6 col-sm-12 py-3 mx-2 my-2" id="{{$idContaint}}" style="border: {{$borderStile}}; border-style: dotted; border-radius: 8px; background-color: {{$backgroundStile}};">
					<div class="row text-center">
				  	<div class="col-6">
					  	<label><strong>Producto:</strong></label>
					  </div>
					  <div class="col-6">
					  	<i aria-hidden="true" class="fa {{$iconBundle[$value->dn_type]}}"></i>
				  		<label>{{$labelBundle[$value->dn_type]}}</label>
				  	</div>
			  	</div>
			  	<div class="row text-center">
					  <div class="col-6" >
					  	<label><strong>Msisdn:</strong></label>
					  </div>
					  <div class="col-6">
				  		<label>{{$value->msisdn}}</label>
				  	</div>
				  </div>
				  <div class="row text-center">
					  <div class="col-6">
					  	<label><strong>Status:</strong></label>
					  </div>
					  <div class="col-6">
				  		<label>{{$statusLabelBundle[$value->status]}}</label>
				  		<i class="fa {{$statusIconBundle[$value->status]}}" aria-hidden="true"></i>
				  	</div>
				  </div>
				  <div class="row text-center align-items-center">
					  <div class="col-6">
					  	<label><strong>Acciones:</strong></label>
					  </div>
					  <div class="col-6">
					  	@if($value->status=='E')
					  		<button class="btn btn-danger" onclick="ViewFail('{{$value->id}}','{{$value->dn_type}}')">Ver detalles</button>
					  	@else
					  		<label>S/N</label>
					  	@endif
				  	</div>
			  	</div>
				</div> --}}
			@endforeach
		@else
		  <div class="alert alert-danger">
		    No se pudo cargar {{ $isCombo==true ? "los estatus de los productos del combo.":"el estatus del producto de fibra."}}
		  </div>
	   @endif
  </div>

	{{-- @if($ActiveVerify)
	  <div class="row justify-content-center py-3" id="block_refres">
	  	<button class="btn btn-primary" onclick="RefresFail()">
				<i class="fa fa-refresh" aria-hidden="true"></i>
	  		Refrescar status
	  	</button>
	  </div>
  @endif --}}

  <div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="fail_active_product" role="dialog" style="display: none;" tabindex="-1">
	  <div class="modal-dialog" style="top: 140px;">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
	          ×
	        </button>
	        <h4 class="modal-title">
	          Inconveniente de activación de producto
	        </h4>
	      </div>
	      <div class="modal-body">
	      	<div class="px-2">
	      		<label>Motivo de la falla:</label>
	      		<div class="pb-3" id="motive"></div>
	      	</div>
	      	<div class="row changer_fail_C" id="changer_fail"></div>
	      </div>
	      <div class="modal-footer">
	        <button class="btn btn-default waves-effect" data-dismiss="modal" id="close-mod-conf_C" type="button">
	          Cancelar
	        </button>
	        <button class="btn btn-danger waves-effect waves-light"  onclick="ProcessFail()" id="process_fail" type="button">
	          Procesar
	        </button>
	      </div>
	    </div>
	  </div>
	</div>

	<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="fail_active_parent" role="dialog" style="display: none;" tabindex="-1">
	  <div class="modal-dialog" style="top: 140px;">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
	          ×
	        </button>
	        <h4 class="modal-title">
	          Inconveniente de activación de producto
	        </h4>
	      </div>
	      <div class="modal-body">
	      	<div class="px-2">
	      		<label>Motivo de la falla:</label>
	      		<div class="pb-3" id="motive"></div>
	      	</div>
	      	<div class="row changer_fail_P" id="changer_fail">
	      		<input autocomplete="off" class="form-control" id="mac_input" maxlength="17" minlength="17" name="mac_input" pattern="^([0-9A-Fa-f]{2}[:]){5}([0-9A-Fa-f]{2})$" placeholder="Escribe Mac de reemplazo" required="">
	      		<label class="px-3 mt-2" id="error-mac"></label>
	      		{{-- <div class="help-block with-errors" id="error-mac">
            </div> --}}
	      	</div>
	      </div>
	      <div class="modal-footer">
	        <button class="btn btn-default waves-effect" data-dismiss="modal" id="close-mod-conf_P" type="button">
	          Cancelar
	        </button>
	        <button class="btn btn-danger waves-effect waves-light"  onclick="ProcessFailParent()" id="process_fail_parent" type="button">
	          Procesar
	        </button>
	      </div>
	    </div>
	  </div>
	</div>

</section>

@if($init)
<script src="{{ asset('plugins/bower_components/jquery/dist/jquery.min.js') }}">
</script>
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
@endif

<script type="text/javascript">

	function valid_Mac( valor, IDcontenedor = 'mac_input') {
      let regex = /^([0-9A-Fa-f]{2}[:]){5}([0-9A-Fa-f]{2})$/;
      let tag   = document.getElementById(IDcontenedor);
      if( regex.test( valor ) ) {
        return true;
      }
      return false;
    }

    function process_change_mac(deviceMac){
      doPostAjax(
        "{{ route('sellerFiber.chekingMac') }}",
        function(res){
          if(!res.success){
            swal({
              title: 'Problemas con la dirección MAC',
              text: res.msg,
              icon: "warning",
              button: {
                text: "OK"
              }
            });
            $('.loading-ajax').fadeOut();
          }else{

          		doPostAjax(
				        "{{ route('sellerFiber.changeMac') }}",
				        function(res){
				          if(!res.success){
				            swal({
				              title: 'Problemas con la dirección MAC',
				              text: res.msg,
				              icon: "warning",
				              button: {
				                text: "OK"
				              }
				            });
				            $('.loading-ajax').fadeOut();
				          }else{

				            swal({
				              title: 'Dirección MAC actualizada',
				              text: res.msg,
				              icon: "success",
				              button: {
				                text: "OK"
				              }
				            }).then(() => {
				            	$('#fail_active_parent').modal('hide');
				            	RefresFail();
				            });

				            $('.loading-ajax').fadeOut();
				          }
				        },
				        {
				          mac: deviceMac,
				          installation_id:$('#process_fail_parent').data('id')
				        },
				        $('meta[name="csrf-token"]').attr('content')
				      );
          }
        },
        {
          mac: deviceMac
        },
        $('meta[name="csrf-token"]').attr('content')
      );
    }

	function formatMacAddress(userInput) {
      var macAddress = userInput || null;

      if (macAddress !== null) {
        var deviceMac = macAddress.value;
        deviceMac = deviceMac.toUpperCase();

        if (deviceMac.length >= 3 && deviceMac.length <= 16) {
          deviceMac = deviceMac.replace(/\W/ig, '');
          deviceMac = deviceMac.replace(/(.{2})/g, "$1:");

        }else{
          if(deviceMac.length == 17){
            if(!valid_Mac(deviceMac)){
              $('#error-mac').html('Mac invalida');
              $('#error-mac').addClass('error').addClass('text-danger');
            }
          }
        }
        document.getElementById(macAddress.id).value = deviceMac;
      }
    }

	var macAddressField = document.getElementById('mac_input');
        // Make sure field object exists
      if (typeof macAddressField !== 'undefined') {

        // Attache event listner
        macAddressField.addEventListener('keyup', function() {
            MAC = this.value;
            ItemMAC = MAC.substr(-1);
            let regex = /^([a-f]|[0-9]|[A-F])$/;

            if(MAC.length < 17){
              //hidden the imputs y clear alert error
              $('#error-mac').html('');
              $('#error-mac').removeClass('alert').removeClass('alert-danger');
              //resetFormMac();
            }
            // Allow user to use the backspace key
            if (event.keyCode !== 8 && regex.test( ItemMAC )) {
                // Format field value
                formatMacAddress(this);
            }else{
              //input invalid removed last
             MAC = MAC.substr(0, MAC.length - 1);
            document.getElementById('mac_input').value = MAC;
            }
        }, false);
      }
      //////// END process MAC

  function ViewFail(childrenBundle, childrenType, codeErr, msgErr = null){
  	//StopInterval();
  	if(childrenType!='F' && codeErr != 'FAIL_MAC' && codeErr != 'EMPTY_IP'){
  		$('.loading-ajax').fadeIn();
      doPostAjax(
        "{{ route('sellerFiber.viewProcessFail') }}",
        function(res){
          $('.loading-ajax').fadeOut();
          if(res.success){

          	$('#process_fail').data('children', childrenBundle);
          	$('#process_fail').data('type', childrenType);
            //console.log($('#process_fail').data('children'));
            //console.log($('#process_fail').data('type'));
            $('#fail_active_product .modal-body #motive').html(res.obs);
            $('#fail_active_product .modal-body #changer_fail').html(res.inventory);
            if(res.msg!='OK'){
            	$('#process_fail').attr('disabled', true);
            	$('#process_fail').attr('title', "No se cuenta con el inventario para continuar");
            }else{
            	$('#process_fail').attr('disabled', false);
            	$('#process_fail').attr('title', "Se cambiara el producto");
            }
          	$('#fail_active_product').modal('show');
          }else{
            if(res.message == 'TOKEN_EXPIRED'){
              showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
            }else{
              @handheld
	          		swal({
	                title: res.title,
	                text: res.msg,
	                icon: res.icon
	              });
	          	@elsehandheld
	            	showMessageAjax(res.icon, res.msg);
	          	@endhandheld
            }
          }
        },
        {
          id: childrenBundle,
          dn_type: childrenType,
        },
        $('meta[name="csrf-token"]').attr('content')
      );
    }
    else{
    	//Elemento de fibra
    	$('#fail_active_parent .modal-body #motive').html(msgErr);
    	$('#process_fail_parent').data('id', childrenBundle);
      $('#process_fail_parent').data('type', childrenType);
      if(codeErr != 'FAIL_MAC'){
      	$('.changer_fail_P').hide();
      	$('#process_fail_parent').hide();
      	$('#close-mod-conf_P').text("Cerrar");
      }else{
      	$('.changer_fail_P').show();
      	$('#process_fail_parent').show();
      	$('#close-mod-conf_P').text("Cancelar");
      }
			$('#fail_active_parent').modal('show');
    }
  }
  function ProcessFail(){
  	$('.loading-ajax').fadeIn();
  	doPostAjax(
      "{{ route('sellerFiber.processFail') }}",
      function(res){
        $('.loading-ajax').fadeOut();
        if(res.success){
        	$('#fail_active_product').modal('hide');
        	showMessageAjax(res.icon, res.msg);
        	RefresFail();
        //	initInterval();
        }else{
          if(res.message == 'TOKEN_EXPIRED'){
            showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
          }else{
            @handheld
          		swal({
                title: res.title,
                text: res.msg,
                icon: res.icon
              });
          	@elsehandheld
            	showMessageAjax(res.icon, res.msg);
          	@endhandheld
          }
         }
      },
      {
        id: $('#process_fail').data('children'),
        dn_type: $('#process_fail').data('type'),
        inv_detail_id_new: $('#dn_new').val(),
      },
      $('meta[name="csrf-token"]').attr('content')
    );
  }
  function ProcessFailParent(){
  	$('.loading-ajax').fadeIn();
  	deviceMac = $('#mac_input').val();
  	if(valid_Mac(deviceMac)){
      process_change_mac(deviceMac);
    }else{
    	$('.loading-ajax').fadeOut();
      swal({
              title: 'Problemas con la dirección MAC',
              text: 'Mac Invalida',
              icon: "warning",
              button: {
                text: "OK"
              }
      });
    }
  }

  function RefresFail(){
  	$('.loading-ajax').fadeIn();

  	doPostAjax(
      "{{ route('sellerFiber.refresFail') }}",
      function(res){
        $('.loading-ajax').fadeOut();
        if(res.success){
					$('#blockBundleResult').html(res.msg);
        }else{
          if(res.message == 'TOKEN_EXPIRED'){
            showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
          }else{
          	@handheld
          		swal({
                title: res.title,
                text: res.msg,
                icon: res.icon
              });
          	@elsehandheld
            	showMessageAjax(res.icon, res.msg);
          	@endhandheld
          }
         }
      },
      {
        id: '{{$id}}',
      },
      $('meta[name="csrf-token"]').attr('content')
    );
	}

	function RefresMsg(type,showMsgProv = 'N'){
		if(type=='OK'){
			$('#process_fail').data('continue', "OK");

			text = "{{ $isCombo==true ? "Todos los productos que conforman el combo fueron activados exitosamente!":"Servicio de fibra fue activado exitosamente!"}}";

			if(showMsgProv=='Y'){
				text = text+="\n\n Atención: No se logró realizar el aprovisionamiento de forma automatica, debes comucarte con 815 y solicitar el aprovisionamiento de esta instalación"
			}

			swal({
	      title: "{{ $isCombo==true ? "Productos activados!":"Servicio de fibra activado!"}}",
	      text: text,
	      html: true,
	      icon: 'success',
	      button: {
	        text: "OK"
	      }
      });
		}else{
			swal({
	      title: "{{ $isCombo==true ? "Las activaciones deben ser revisadas!":"La activacion debe ser revisada!"}}",
	      text: "{{ $isCombo==true ? "Uno o varios productos que conforman el combo aun no se han":"El servicio de fibra aun no se ha"}} activado exitosamente, dirijase a 'Ver detalles' en el producto que fallo para mayor información",
	      icon: 'warning',
	      button: {
	        text: "OK"
	      }
      });
		}
	}
/*
	var actual = 30;
	function viewRefresCount(tiempo){

		if(actual<=1){
			StopInterval();
			RefresFail();
			actual = 30;
		}else{
			actual -= 1;
		}
		$('#block_refres').html('Proxima verificacion en '+actual+' seg >>> '+tiempo);
	}

	function initInterval(){
		//if (!intervalTime) {
		// solo establece un nuevo si el reloj está detenido
		var Tiempo = "{{time()}}";
		intervalTime = setInterval(viewRefresCount, 1000, Tiempo);
		console.log("intervalTime "+intervalTime);
		//}
	}

	function StopInterval(){
		clearInterval(intervalTime);
		intervalTime = null;
	}
*/
	$(function () {
		@if($ActiveVerify)
			$('#process_fail').data('continue', "FAIL");
			/*initInterval();
			$("#fail_active_product").on('hide.bs.modal', function(){
				initInterval();
  		});*/
		@else
			//console.log("APAGADO");
			$('#process_fail').data('continue', "OK");
			//$('#block_refres').text('');
		@endif
	});

	@if(count($childrenBundle)>0)
			@foreach ($childrenBundle as $value)
				// console.log("{{$value->status }}");
				@php
				$idContaint = str_replace("=","",base64_encode($value->config."-".$value->id));
				@endphp

				@if($value->status=='EC' || $value->status=='PA')
					$('#'+"{{$idContaint}}"+'> .loading-bundle-component').fadeIn();

					if(typeof {{"intv_".$idContaint}} === 'undefined'){
						eval('var ' + "{{"intv_".$idContaint}}" + '= null;');
					}

					if({{"intv_".$idContaint}} == null){
						{{"intv_".$idContaint}} = setInterval(function(){
							// console.log("{{"intv-".base64_decode($idContaint)}}");

							$.ajax({
				        headers: {
				          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				        },
				        url: '{{ route('sellerFiber.refresComponent') }}',
				        type: 'POST',
				        dataType: 'json',
				        data: {
				          id: '{{$value->id}}',
					        config: '{{$value->config}}'
				        },
				        error: function(res) {
				        	//alert('ocurrio un error');
				          console.log(res);

				          // if(typeof {{"intv_".$idContaint}} !== 'undefined'){
									// 			clearInterval({{"intv_".$idContaint}});
									// 			{{"intv_".$idContaint}} = null;
									// 		}
				        },
				        success: function(res) {
				          if (res.success) {
				            $('#'+'{{$idContaint}}').html(res.msg);
				            // console.log(res.status);
				            if(res.status=='EC' || res.status=='PA'){
				            	$('#'+"{{$idContaint}}"+'> div > .loading-bundle-component').fadeIn();
										}
										else{
											//closeRefreshComponent("{{$idContaint}}");
											$('#'+"{{$idContaint}}"+'> div > .loading-bundle-component').fadeOut();

											if(typeof {{"intv_".$idContaint}} !== 'undefined'){
												clearInterval({{"intv_".$idContaint}});
												{{"intv_".$idContaint}} = null;
											}

											if(res.status == 'E'){
												RefresMsg('FAIL');
											}
											else{
												if(res.totalProcess == 'Y'){
													RefresMsg('OK',res.showMsgProvisioning);
												}
											}
										}
				          } else {
				          	swal({
			                title: res.title,
			                text: res.msg,
			                icon: res.icon
	              		});
				          }
				        }
				      });
						}, 9950);
					}
				@else
					$('#'+"{{$idContaint}}"+'> div > .loading-bundle-component').fadeOut();

					if(typeof {{"intv_".$idContaint}} !== 'undefined'){
						clearInterval({{"intv_".$idContaint}});
						{{"intv_".$idContaint}} = null;
					}
				@endif
			@endforeach
		@endif
</script>
