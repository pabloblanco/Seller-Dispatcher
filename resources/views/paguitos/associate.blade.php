@extends('layouts.admin')

@section('content')
    @include('components.messages')
    @include('components.messagesAjax')

    @if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>
      {{ $error }}
    </li>
    @endforeach
  </ul>
</div>
@endif
<div class="row bg-title">
  <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
    <h4 class="page-title">
      Asociar MSISDN a financiamiento.
    </h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          Ventas
        </a>
      </li>
      <li class="active">
        Paguitos
      </li>
    </ol>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="white-box">
      <div class="row">
        <h3 class="box-title">
          MSISDN del cliente
        </h3>
        <div class="col-md-12">
          <div class="alert alert-info alert-dismissable">
            <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
              ×
            </button>
            Para asociar un financiamiento a un msisdn se debe hacer primero el registro en la plataforma de Paguitos y luego la
            <i>
              "venta + activación en la plataforma de ventas Netwey"
            </i>
            .
          </div>
          <div class="col-sm-12 col-md-6 col-lg-4 m-b-20">
            {{--
            <form action="" class="form-horizontal" id="searchMsisdnForm" method="POST">
              --}}
              <div class="input-group">
                <input class="form-control" id="dn" name="dn" placeholder="MSISDN" type="text">
                  <span class="input-group-btn">
                    <button class="btn btn-info" id="searchDN" type="button">
                      <i class="zmdi zmdi-search zmdi-hc-fw">
                      </i>
                    </button>
                  </span>
                </input>
              </div>
              {{--
            </form>
            --}}
          </div>
        </div>
      </div>
      <div class="row" id="result-q">
      </div>
    </div>
  </div>
</div>
@stop

@section('scriptJS')
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
<script type="text/javascript">
  $(function () {
            isFinancing = function (res){
                $('.loading-ajax').fadeOut();
                
                $('#searchDN').attr('disabled',false);

                if(!res.error){
                    if(res.data.html){
                        $('#result-q').html(res.data.html);
                    }
                }else{
                    if(res.message == 'TOKEN_EXPIRED'){
                        showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                    }else if(res.message){
                        swal({
                            title: "Advertencia",
                            text: res.message,
                            icon: "warning",
                            button: {text: "OK"},
                        });
                    }
                }
            }

            $('#searchDN').on('click', function(e){
                let msisdn = $('#dn').val().trim();

                $('#result-q').html('');

                if(msisdn != '' && msisdn.length == 10){
                    $(e.currentTarget).attr('disabled',true);
                    $('.loading-ajax').show();

                    doPostAjax(
                        "{{ route('paguitos.verifyPaguitos') }}", 
                        isFinancing, 
                        {msisdn: msisdn},
                        '{{ csrf_token() }}'
                    );
                }else{
                    showMessageAjax('alert-danger', 'Debe escribir un MSISDN válido.');
                }
            });

            $('#dn').focusin(function(e){
                $('#searchDN').attr('disabled', false);
                $('#result-q').html('');
            });
        });
</script>
@stop
