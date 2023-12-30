<div class="col-12">
  @if($detail)
	@if(!empty($title))
  <label class="d-flex justify-content-center">
    <strong>
      {{$title}}
    </strong>
  </label>
  <br/>
  @endif
  @if(!empty($msg))
  <label class="text-center">
    <strong>
      Info:
    </strong>
    {{$msg}}
  </label>
  <br/>
  @endif
  @if(!empty($responsable))
  <label class="text-center">
    <strong>
      Responsable:
    </strong>
    {{$responsable}}
  </label>
  <br/>
  @endif
	@else
  <div class="alert alert-danger">
    <p>
      Lo sentimos, no hay detalles de cambios de estatus.
    </p>
  </div>
  @endif
</div>