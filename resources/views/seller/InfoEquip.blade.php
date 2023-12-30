  <label class="box-title col-md-12">
    Datos del equipo
  </label> 
  <div class="form-group mb-2">
    <label class="col-md-12">
      Marca:
    </label>
    <div class="col-md-12 pb-md-3 smartphone-brand">
      {{ (isset($equip->brand) && !empty($equip->brand))? $equip->brand : "S/N" }}
    </div>
  </div>
  <div class="form-group mb-2">
    <label class="col-md-12">
      Modelo:
    </label>
    <div class="col-md-12 pb-md-3 smartphone-model">
      {{ (isset($equip->model) && !empty($equip->model))? $equip->model : "S/N" }}
    </div>
  </div>
  <div class="form-group mb-2">
    <label class="col-md-12">
      IMEI:
    </label>
    <div class="col-md-12 pb-md-3 smartphone-imei">
      {{ (isset($equip->imei) && !empty($equip->imei))? $equip->imei : "S/N" }}
    </div>
  </div>
  <div class="form-group mb-2">
    <label class="col-md-12">
      Serial:
    </label>
    <div class="col-md-12 pb-md-3 smartphone-imei">
      {{ (isset($equip->serial) && !empty($equip->serial))? $equip->serial : "S/N" }}
    </div>
  </div>  
  <div class="form-group mb-2">
    <label class="col-md-12">
      ICCID:
    </label>
    <div class="col-md-12 pb-md-3 smartphone-iccid">
      {{ (isset($equip->iccid) && !empty($equip->iccid))? $equip->iccid : "S/N" }}
    </div>
  </div> 
  <div class="form-group mb-2">
    <label class="col-md-12">
      MSISDN:
    </label>
    <div class="col-md-12 pb-md-3 smartphone-msisdn">
      <strong>
      {{ (isset($equip->msisdn) && !empty($equip->msisdn))? $equip->msisdn : "S/N" }}
      </strong>
    </div>
  </div>