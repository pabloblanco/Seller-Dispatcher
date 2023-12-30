<div class="row p-b-20 pt-3" hidden="" id="form-port-content">
  <div class="col-md-12">
    <h3 class="box-title">
      Formulario de portabilidad
    </h3>
    <form id="formPort" name="formPort">
      <div class="row">
        <div class="col-md-6 col-12 pb-3">
          <div class="">
            <label class="control-label">
              MSISDN a portar:
            </label>
            <input class="form-control" id="dn_port" maxlength="10" minlength="10" name="dn_port" oncopy="return false" oncut="return false" onpaste="return false" placeholder="Número a portar" required="" type="number"/>
          </div>
        </div>
        <div class="col-md-6 col-12 pb-3">
          <div class="">
            <label class="control-label">
              Confirmar MSISDN a portar:
            </label>
            <input class="form-control" data-match="#dn_port" data-match-error="Los números no coinciden" id="dn_port2" maxlength="10" minlength="10" name="dn_port2" oncopy="return false" oncut="return false" onpaste="return false" placeholder="Confirmar número a portar" required="" type="number"/>
          </div>
        </div>
        <div class="col-md-6 col-12 pb-3">
          <div class="">
            <label class="control-label">
              Codigo NIP:
            </label>
            <input class="form-control" id="nip" maxlength="4" minlength="4" name="nip" pattern="^[0-9]{4}$" placeholder="Codigo NIP" required="" type="number"/>
          </div>
        </div>
        <div class="col-md-6 col-12 pb-3">
          <div class="">
            <label class="control-label">
              Confirmar codigo NIP:
            </label>
            <input class="form-control" id="nip2" maxlength="4" minlength="4" name="nip2" pattern="^[0-9]{4}$" placeholder="Codigo NIP" required="" type="number"/>
          </div>
        </div>
        <div class="col-md-12 pt-3" id="notify_nip">
          <div class="alert alert-primary alert-dismissable">
            <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
              ×
            </button>
            <span>
              <h4>
                <strong>
                  Atención:
                </strong>
              </h4>
                &#128179; Consideraciones al momento de solicitar el codigo NIP:
            </span>
            <br>
            <li>
              <strong>1 -</strong> El codigo NIP tiene una validez de máximo 5 días continuos.
            </li>
            <li>
              <strong>2 -</strong> Puede solicitarse desde el equipo del cliente a portar via SMS con la palabra NIP al 051 o llamando al 051.
            </li>
            <li>
              <strong>3 -</strong> Tenga en cuenta que el proceso de portación suele tardar aproximandamente 3 días hábiles. Temporalmente durante ese tiempo su numero sera el msisdn de netwey.
            </li>
          </div>
        </div>
        {{--
        <div class="col-md-6 col-12">
          <div class="">
            <label class="control-label">
              Identificación (Frente)
            </label>
            <input accept="image/png, image/jpeg, image/jpg" class="dropify" data-max-file-size="10M" id="dni-front" name="dni-front" type="file"/>
          </div>
        </div>
        <div class="col-md-6 col-12">
          <div class="">
            <label class="control-label">
              Identificación (Reverso)
            </label>
            <input accept="image/png, image/jpeg, image/jpg" class="dropify" data-max-file-size="10M" id="dni-back" name="dni-back" type="file"/>
          </div>
        </div>
        --}}
        @if(!empty($companys))
          <div class="col-md-6 col-12">
            <div class="">
              <label class="control-label">
                Operador origen:
              </label>
              <select class="form-control" id="port-prov" name="port-prov" required="" placeholder="Seleccione un operador" >
                <option value="">
                  Seleccione un operador
                </option>
                @foreach($companys as $company)
                  <option value="{{ $company->id }}">
                    {{ $company->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        @endif

        <div class="col-md-12 text-right">
          <button class="btn btn-success waves-effect waves-light text-right" id="btn-form-port" name="btn-form-port" type="button">
            Verificar portabilidad
          </button>
          <div class="help-block with-errors" id="error-verifyPort">
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

@if($banJs=="fiber")
@section("fromPortJS")
@endif

<script src="{{ asset('plugins/bower_components/jquery-validation/dist/jquery.validate.min.js') }}"></script>
<script src="{{ asset('js/fromPort.js') }}"></script>


@if($banJs=="fiber")
@endsection
@endif
