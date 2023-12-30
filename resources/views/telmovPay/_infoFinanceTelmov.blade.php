<div class="container">
  <div class="row">
    <div class="col-md-6 col-12">
      @include('seller.InfoClient', ['client' => $client ])
    </div>
    <hr class="d-md-none d-block" style="width: 80%; height: 1px; color: black;"/>
    <div class="col-md-6 col-12">
      @include('seller.InfoEquip', ['equip' => $equip ])
      <div class="form-group mb-2">
        <label class="col-md-12">
          Costo
        </label>
        <div class="col-12 equip-price">
          {{ (isset($equip->price) && !empty($equip->price))? $equip->price : "S/N"}}
        </div>
      </div>
      <div class="form-group mb-2">
        <label class="col-md-12">
          Fecha de venta
        </label>
        <div class="col-12 equip-sale-date">
          {{ (isset($equip->dateSale) && !empty($equip->dateSale))? $equip->dateSale : "S/N"}}
        </div>
      </div>
    </div>
    <hr style="width: 80%; height: 1px; color: black;"/>
    <div class="col-md-12">
      <h3 class="box-title col-12">
        Financiación de TelmovPay
      </h3>
      <div class="form-group">
        <label class="col-md-6 col-12">
          Monto del enganche
        </label>
        <div class="col-12 equip-initial_amount">
          <strong>
          {{ (isset($saleTelmov->initial_amount) && !empty($saleTelmov->initial_amount))? $saleTelmov->initial_amount." $" : "S/N"}}
          </strong>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-6 col-12">
          Numero de semanas
        </label>
        <div class="col-12 equip-cant_cuotes">
          {{ (isset($saleTelmov->cant_cuotes) && !empty($saleTelmov->cant_cuotes))? $saleTelmov->cant_cuotes : "S/N"}}
        </div>
      </div>
    </div>
    <div class="col-md-12">
    <h3 class="box-title col-12">
        Recibiste el dinero del enganche?
    </h3>
    <div class="form-group px-3">
        <label class="custom-control custom-radio">
          {{--checked=""--}}
            <input  class="custom-control-input" name="recibmoney" type="radio" value="N"/>
            <span class="custom-control-indicator">
            </span>
            <span class="custom-control-description">
                No
            </span>
        </label>
        <label class="custom-control custom-radio">
            <input class="custom-control-input" name="recibmoney" type="radio" value="Y"/>
              <span class="custom-control-indicator">
              </span>
            <span class="custom-control-description">
                Si
            </span>
        </label>
    </div>
    </div>
    <div class="col-md-12 text-right">
      <button class="btn btn-default waves-effect" data-dismiss="modal" id="btncancelAsocciate"  type="button">
          Cancelar
      </button>
      <button class="btn btn-danger waves-effect waves-light" name="btnAssociateTelmov" id="btnAssociateTelmov" type="button">
          Asociar financimiento
      </button>
    </div>
  </div>
</div>

<script type="text/javascript">

  associateEnganche = function (res){
    $('.loading-ajax').fadeOut();
    if(res.success){
      swal({
          title: "Exito!",
          text: res.message,
          icon: "success",
          button: {text: "OK"},
      }).then(() => {
        setTimeout(() => {  location.reload(); }, 3000);
      });
    }else{
        showMessageAjax(res.icon, res.message);
    }
  }

  SendEnganche = function (recib){
    $('.loading-ajax').show();
      doPostAjax(
          "{{ route('telmovpay.associateCashTelmov') }}",
          associateEnganche,
          {
            msisdn: '{{$equip->msisdn}}',
            money: recib
          },
          '{{ csrf_token() }}'
      );
  }


  $('#btnAssociateTelmov').on('click', function(e){
    var recib = $("input:radio[name=recibmoney]:checked").val()

      if(recib != '' && recib !== undefined){
        if(recib == 'N'){
          swal({
            title: "Seguro no recibiste el enganche?",
            text: "La acción no se puede revertir",
            icon: "warning",
            buttons: {
              cancel: {
                text: "Cancelar",
                value: 'cancel',
                visible: true,
                className: "",
                closeModal: true,
              },
              confirm: {
                text: "Continuar",
                value: 'ok',
                visible: true,
                className: "",
                closeModal: true
              },
            },
            dangerMode: true,
          }).then((option) => {

            if (option == 'ok') {
              SendEnganche(recib);
            }
          });
        }else{
           SendEnganche(recib);
        }
      }else{
          showMessageAjax('alert-danger', 'Debes indicar si recibiste el dinero del enganche');
      }
  });

</script>
