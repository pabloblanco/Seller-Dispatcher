<h3 class="box-title col-md-12">
  Datos verificados del Cliente
</h3>
<div class="form-group mb-2">
  <label class="col-md-12">
    Nombres y Apellidos:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-name-full">
    {{ (isset($clientTelmov->names))? $clientTelmov->names->firstName.' '.$clientTelmov->names->maternalLastName.' '.$clientTelmov->names->paternalLastName : "S/N" }}
  </div>
</div>
<div class="form-group mb-2">
  <label class="col-md-12">
    CURP:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-dni">
    {{ (isset($clientTelmov->curp) && !empty($clientTelmov->curp))? $clientTelmov->curp : "S/N"}}
  </div>
</div>
<div class="form-group mb-2">
  <label class="col-md-12">
    Fecha de nacimiento:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-phone">
    {{ (isset($clientTelmov->birthDate) && !empty($clientTelmov->birthDate))? $clientTelmov->birthDate : "S/N" }}
  </div>
</div>
<div class="form-group mb-2">
  <label class="col-md-12">
    Email:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-email">
    {{ (isset($clientTelmov->emailAddress) && !empty($clientTelmov->emailAddress))? $clientTelmov->emailAddress : "S/N"}}
  </div>
</div>
<div class="form-group mb-2">
  <label class="col-md-12">
    Telefono:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-phone">
    {{ (isset($clientTelmov->phoneNumber) && !empty($clientTelmov->phoneNumber))? $clientTelmov->phoneNumber : "S/N"}}
  </div>
</div>
<div class="form-group mb-2">
  <h4 class="box-title col-md-12">
    Direccion:
  </h4>
  @if(isset($clientTelmov->address))
  <label class="col-md-12">
    Ciudad:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-city">
    {{ (isset($clientTelmov->address->city) && !empty($clientTelmov->address->city))? $clientTelmov->address->city : "S/N"}}
  </div>
  <label class="col-md-12">
    Vecindario:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-email">
    {{ (isset($clientTelmov->address->neighborhood) && !empty($clientTelmov->address->neighborhood))? $clientTelmov->address->neighborhood : "S/N"}}
  </div>
  <label class="col-md-12">
    Codigo postal:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-email">
    {{ (isset($clientTelmov->address->postalCode) && !empty($clientTelmov->address->postalCode))? $clientTelmov->address->postalCode : "S/N"}}
  </div>
  <label class="col-md-12">
    Estado:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-email">
    {{ (isset($clientTelmov->address->state) && !empty($clientTelmov->address->state))? $clientTelmov->address->state : "S/N"}}
  </div>
  <label class="col-md-12">
    Numero de calle:
  </label>
  <div class="col-md-12 pb-md-3 clientTelmov-email">
    {{ (isset($clientTelmov->address->streetNameAndNumber) && !empty($clientTelmov->address->streetNameAndNumber))? $clientTelmov->address->streetNameAndNumber : "S/N"}}
  </div>
  @else
  S/N
  @endif
</div>