<div class="row">
  <div class="col-md-3 col-12 px-3 pb-3">
    <label>
      Cliente:
    </label>
  </div>
  <div class="col-md-9 col-12 px-3">
    <p>
      {{$dataInstall->name}} {{$dataInstall->last_name ?? ''}}
    </p>
  </div>
  <div class="col-md-3 col-12 px-3 pb-3">
    <label>
      Instalación:
    </label>
  </div>
  <div class="col-md-9 col-12 px-3">
    <p>
      {{$dataInstall->address_instalation}}
    </p>
    <p>
      {{$dataInstall->date_instalation}}
    </p>
    <p>
      {{$dataInstall->schedule}}
    </p>
  </div>
</div>
