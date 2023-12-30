<h3 class="box-title col-md-12">
  Datos del Cliente
</h3>
<div class="row">
  <div class="col-md-6 col-12 form-group mb-2">
    <label class="col-sm-12">
      ID del cliente:
    </label>
    <div class="col-sm-12 pb-md-3 client-dni">
      {{ (isset($client->dni) && !empty($client->dni))? $client->dni : "S/N"}}
    </div>
  </div>
  <div class="col-md-6 col-12 form-group mb-2">
    <label class="col-sm-12">
      Nombre / Apellido:
    </label>
    <div class="col-sm-12 pb-md-3 client-name-full">
      {{ (isset($client->name) && !empty($client->name))? $client->name : "S/N" }} {{ (isset($client->last_name) && !empty($client->last_name))? $client->last_name : "S/N"}}
    </div>
  </div>
  <div class="col-md-6 col-12 form-group mb-2">
    <label class="col-sm-12">
      Teléfono:
    </label>
    <div class="col-sm-12 pb-md-3 client-phone">
      {{ (isset($client->phone_home) && !empty($client->phone_home))? $client->phone_home : "S/N" }}
      @if($client->isVerifyPhone == "VERIFIED")
        <i class="ti-check-box" title="Celular verificado"></i>
      @endif
    </div>
  </div>
  <div class="col-md-6 col-12 form-group mb-2">
    <label class="col-sm-12">
      Email:
    </label>
    <div class="col-sm-12 pb-md-3 client-email">
      {{ (isset($client->email) && !empty($client->email))? $client->email : "S/N"}}
    </div>
  </div>
  <div class="col-sm-12 form-group mb-2">
    <label class="col-sm-12">
      Dirección:
    </label>
    <div class="col-sm-12 pb-md-3 client-address">
      {{ (isset($client->address) && !empty($client->address))? $client->address : "S/N"}}
    </div>
  </div>
</div>

<div class="pb-4"></div>
