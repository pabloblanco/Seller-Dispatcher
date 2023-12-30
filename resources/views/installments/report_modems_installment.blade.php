@extends('layouts.admin')

@section('customCSS')
  <link rel="stylesheet" href="{{ asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">

  <link href="{{ asset('css/selectize.css') }}" rel="stylesheet">
  <link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet">
@stop

@section('content')
  @include('components.messages')
  @include('components.messagesAjax')

  @if($errors->any())
      <div class="alert alert-danger">
          <ul>
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
  @endif

  <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <h4 class="page-title"> Reporte de modems en abono </h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
          <ol class="breadcrumb">
              <li><a href="#">Coordinaci&oacute;n</a></li>
              <li class="active">Reporte de modems en abono.</li>
          </ol>
      </div>
  </div>
  <div class="row">
      <div class="col-md-12">
          <div class="white-box">
              <h3 class="box-title">Reporte de modems en abono</h3>

              <div class="row">
                @if(!empty($coordinadores) && $coordinadores->count())
                <div class="col-sm-12 p-b-10">
                    <select class="form-control" id="list-users" name="list-users">
                        @if(!empty($userc))
                        <option value="{{$userc}}">{{$userc}}</option>
                        @else
                        <option value="">Todos</option>
                        @endif
                    </select>
                </div>
                @endif

                <div class="col-md-3 col-sm-12">
                  <div class="white-box text-center bg-success">
                    <h1 class="text-white counter">{{ !empty($coord) ? $coord->sum->tokens_assigned : 0 }}</h1>
                    <p class="text-white">
                      M&oacute;dems asignados
                    </p>
                  </div>
                </div>

                <div class="col-md-3 col-sm-12">
                  <div class="white-box text-center bg-success">
                    <h1 class="text-white counter">{{ !empty($coord) ? $coord->sum->tokens_available : 0 }}</h1>
                    <p class="text-white">
                      M&oacute;dems disponibles
                    </p>
                  </div>
                </div>

                <div class="col-md-3 col-sm-12">
                  <div class="white-box text-center bg-info" style="padding: 14px !important;">
                    <h1 class="text-white counter">{{ $histT }}</h1>
                    <p class="text-white">
                      M&oacute;dems colocados (hist&oacute;rico)
                    </p>
                  </div>
                </div>

                <div class="col-md-3 col-sm-12">
                  <div class="white-box text-center bg-info" style="padding: 14px !important;">
                    <h1 class="text-white counter">{{ $histR }}</h1>
                    <p class="text-white">
                      M&oacute;dems recuperados (hist&oacute;rico)
                    </p>
                  </div>
                </div>
              </div>
              <div class="row">
                  <div class="col-md-12 p-b-20" id="list-modems">
                    @foreach($sales as $sale)
                      <div class="card card-outline-danger text-dark m-b-10">
                          <div class="card-block">
                              <div class="col-md-12">
                                  <ul class="list-icons">
                                      <li>
                                          <i class="ti-angle-right"></i> 
                                          <strong>Vendedor:</strong> 
                                          <span>
                                            {{ $sale->name }} {{ $sale->last_name }}
                                          </span>
                                      </li>
                                      <li>
                                          <i class="ti-angle-right"></i> 
                                          <strong>Coordinador:</strong> 
                                          <span>
                                              {{$sale->name_coord}} {{$sale->last_coord}}
                                          </span>
                                      </li>
                                      <li>
                                          <i class="ti-angle-right"></i> 
                                          <strong>MSISDN:</strong> 
                                          <span>
                                              {{ $sale->msisdn }}
                                          </span>
                                      </li>
                                      <li>
                                          <i class="ti-angle-right"></i> 
                                          <strong>Pago inicial:</strong> 
                                          <span>
                                            {{ '$'.number_format($sale->first_pay,2,'.',',') }}
                                          </span>
                                      </li>
                                      <li>
                                          <i class="ti-angle-right"></i> 
                                          <strong>
                                            M&oacute;dem vigente:
                                          </strong> 
                                          <span>
                                            {{ $sale->expired ? 'No' : 'Si' }}
                                          </span>
                                      </li>
                                      @if(!$sale->expired)
                                      <li>
                                          <i class="ti-angle-right"></i> 
                                          <strong>Saldo por cobrar:</strong> 
                                          <span>
                                            {{ '$'.number_format(($sale->pendingAmount),2,'.',',') }}
                                          </span>
                                      </li>
                                      @else
                                        <li>
                                          <i class="ti-angle-right"></i> 
                                          <strong>días vencidos:</strong> 
                                          <span>
                                            {{ $sale->expDays }}
                                          </span>
                                        </li>
                                        <li>
                                            <i class="ti-angle-right"></i> 
                                            <strong>Saldo vencido:</strong> 
                                            <span>
                                              {{ '$'.number_format(($sale->expiredAmount),2,'.',',') }}
                                            </span>
                                        </li>
                                      @endif
                                  </ul>
                              </div>
                          </div>
                      </div>
                    @endforeach
                  </div>
              </div>
          </div>
      </div>
  </div>
@stop

@section('scriptJS')
  <!-- typehead TextBox Search -->
  <script src="{{ asset('plugins/bower_components/waypoints/lib/jquery.waypoints.js') }}"></script>
  {{-- <script src="{{ asset('plugins/bower_components/counterup/jquery.counterup.min.js') }}"></script> --}}

  <script src="{{ asset('js/selectize.js')}}"></script>

  <script type="text/javascript">
    $(function(){
      // $(".counter").counterUp({
      //   delay: 100,
      //   time: 1200
      // });

      @if(!empty($coordinadores) && $coordinadores->count())
      $('#list-users').selectize({
          valueField: 'email',
          searchField: 'name',
          labelField: 'name',
          create: false,
          options: [
              
              {email: '', name: 'Todos'},
              @foreach($coordinadores as $user)
                  {email: '{{$user->email}}',name: '{{$user->name}} {{$user->last_name}}'},
              @endforeach
          ],
          render: {
              option: function(item, escape) {
                  return '<p>'+escape(item.name)+'</p>';
              }
          }
      });

      $('#list-users').on('change', function(e){
          var data = $('#list-users').val().trim();
          if(data && data != ''){
            location.href = "{{route('installments.reportsMI')}}/"+data;
          }else{
            location.href = "{{route('installments.reportsMI')}}";
          }
      });
      @endif
    });
  </script>
@stop