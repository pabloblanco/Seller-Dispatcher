  <div class="form-group text-center" id="block_videocam">
    <div class="position-relative" id="block_flash-{{$current}}">
      {{--<div id="netweyload" style="border: none;background: transparent;color: #eeeeee;height: 100%;align-items: center;display: flex;justify-content: center; position: absolute; z-index: -1; left: 0; right: 0;">
        <img alt="loading" height="100px" src="{{ asset('images/Netwey-animado-min.svg') }}" width="100px"/>
      </div>--}}
      <video autoplay muted playsinline id="video-{{$current}}" style="object-fit: scale-down; width: 100%; height: 80%;">
      </video>
      <canvas hidden="true" id="canvas-{{$current}}">
      </canvas>
      <img alt="" height="100%" hidden="true" id="img-{{$current}}" style="width: 100%; height: 100%;"/>
    </div>
    <button class="btn btn-success waves-effect waves-light m-t-10" id="btnTakePic-{{$current}}" onclick="btnTakePic('{{$current}}','{{$next}}')" type="button">
      &#128247; Tomar foto
    </button>
    <button class="btn btn-danger waves-effect waves-light m-t-10" hidden="true" id="btnDesPic-{{$current}}" onclick="btnDesPic('{{$current}}','{{$next}}')" type="button">
      &#128257; Tomar otra fotografia
    </button>
  </div>
  <div class="help-block" id="error-photo-{{$current}}"></div>
