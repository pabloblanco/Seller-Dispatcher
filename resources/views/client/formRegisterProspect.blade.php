<form action="" class="form-horizontal" data-toggle="validator" id="registerclientform" method="POST" name="registerclientform">
  {{ csrf_field() }}
  <div class="form-group">
    <label class="col-md-12">
      Nombre
    </label>
    <div class="col-md-12">
      <input class="form-control" id="name" name="name" placeholder="Nombre del prospecto" required="" type="text"/>
      <div class="help-block with-errors">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Apellido
    </label>
    <div class="col-md-12">
      <input class="form-control" id="last_name" name="last_name" placeholder="Apellido del prospecto" required="" type="text"/>
      <div class="help-block with-errors">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      INE
    </label>
    <div class="col-md-12">
      <input class="form-control" id="dni" maxlength="10" minlength="10" name="dni" placeholder="Identificación del prospecto" type="text"/>
      <div class="help-block with-errors">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Dirección de domicilio
    </label>
    <div class="col-md-12">
      <input class="form-control" id="direction" name="direction" type="text"/>
      <div class="help-block with-errors">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Fecha de nacimiento
    </label>
    <div class="col-md-12">
      <input class="form-control" id="birthday" name="birthday" placeholder="dd-mm-yyyy" type="text"/>
      <div class="help-block with-errors">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Email
    </label>
    <div class="col-md-12">
      <input class="form-control" data-error="Dirección de email no válida" id="email" name="email" pattern="[a-zA-Z0-9_\-\.~]{2,}@[a-zA-Z0-9_\-\.~]{2,}\.[a-zA-Z]{2,4}" placeholder="correo@servidor.com" type="email"/>
      <div class="help-block with-errors">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Geolocalización (No aplica para telefonía)
    </label>
    <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
      <input class="form-control" id="lat" name="lat" placeholder="Latitud" step="any" type="number"/>
      <div class="help-block with-errors">
      </div>
    </div>
    <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
      <input class="form-control" id="lon" name="lon" placeholder="longitud" step="any" type="number"/>
      <div class="help-block with-errors">
      </div>
    </div>
    <div class="col-lg-4 col-xs-12 col-md-4 col-sm-12">
      <button class="btn btn-success waves-effect waves-light m-r-10" id="btnGeo" name="btnGeo" type="button">
        Ubicar
      </button>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Teléfono principal
    </label>
    <div class="col-md-12 p-t-0 help-block">
      10 dígitos numéricos
    </div>
    <div class="col-md-12">
      <input class="form-control" id="phone" maxlength="10" minlength="10" name="phone" pattern="^[0-9]{10}$" required="" type="text"/>
      <div class="help-block with-errors">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Teléfono secundario
    </label>
    <div class="col-md-12 p-t-0 help-block">
      10 dígitos numéricos
    </div>
    <div class="col-md-12">
      <input class="form-control" id="phone2" maxlength="10" minlength="10" name="phone2" pattern="^[0-9]{10}$" type="text"/>
      <div class="help-block with-errors">
      </div>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Prospecto campaña
    </label>
    <div class="col-md-12">
      <label class="custom-control custom-radio">
        <input checked="" class="custom-control-input social" name="social" type="radio" value="N"/>
        <span class="custom-control-indicator">
        </span>
        <span class="custom-control-description">
          No
        </span>
      </label>
      <label class="custom-control custom-radio">
        <input class="custom-control-input social" name="social" type="radio" value="S"/>
        <span class="custom-control-indicator">
        </span>
        <span class="custom-control-description">
          Si
        </span>
      </label>
    </div>
  </div>
  <div class="form-group" id="rs-content">
    <label class="col-md-12">
      Seleccione la campaña
    </label>
    <div class="col-md-12">
      <select class="form-control" id="campaign" name="campaign">
        <option value="Campaña al Whatsapp">
          Campaña al Whatsapp
        </option>
        <option value="Campaña a la Web">
          Campaña a la Web
        </option>
        <option value="Campaña de POP Up">
          Campaña de POP Up
        </option>
        <option value="Campaña de Registro en Web">
          Campaña de Registro en Web
        </option>
        <option value="Campaña de Instagram">
          Campaña de Instagram
        </option>
        <option value="Campaña de Google Ads">
          Campaña de Google Ads
        </option>
        <option value="Campaña de messenger">
          Campaña de messenger
        </option>
        <option value="Otra">
          Otra
        </option>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Próximo contacto
    </label>
    <div class="col-md-12">
      <input class="form-control" id="nextC" name="nextC" placeholder="dd-mm-yyyy" type="text"/>
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-12">
      Notas
    </label>
    <div class="col-md-12">
      <textarea class="form-control" id="note" name="note" rows="4">
      </textarea>
    </div>
  </div>
  <button class="btn btn-success waves-effect waves-light m-r-10 activate-loader" type="submit">
    Guardar
  </button>
  <button class="btn btn-inverse waves-effect waves-light" type="reset">
    Cancelar
  </button>
</form>
