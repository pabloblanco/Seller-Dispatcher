<div class="container" hidden id="block_{{$type}}">
  <div class="row">
    <div class="col-md-12">
      <label >
        > Equipo de {{$title}}
      </label>
    </div>
    <div class="col-sm-12 col-md-6">
      <label>
        Plan:
      </label>
      <p id="plan-conf">
      </p>
    </div>
    <div class="col-sm-12 col-md-6">
      <label>
        Msisdn de {{$title}}:
      </label>
      <strong>
        <p id="msisdn-conf">
        </p>
      </strong>
      <input type="hidden" name="dn_{{$type}}">
    </div>
    @if($type=='F')
      <div class="col-sm-12 col-md-6">
        <label>
          Nodo de red:
        </label>
        <p id="nodo-conf">
        </p>
      </div>
    @endif
  </div>
  @if($line)
    <hr class="my-2">
  @endif
</div>
