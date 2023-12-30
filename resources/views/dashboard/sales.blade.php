<div class="col-md-12">
    <div class="white-box">
        <h3 class="box-title">{{ $title }}</h3>

        <div class="row">
            <div class="col-lg-2 col-md-3 col-xs-12 col-sm-6 text-center">
                <div class="icon-dashboard">
                    <i class="fa fa-shopping-cart icon"></i>
                </div>
            </div>

            <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12">
                <div class="col-md-12">
                    <div class="form-group hidden" id="calendar-content-{{ $type }}">
                        <input class="form-control datepicker-input input-daterange-datepicker-{{ $type }}" type="text" name="daterange-{{ $type }}" id="daterange{{ $type }}" placeholder="Seleccione un rango de fechas" value="" readonly="true" data-type="{{ $type }}" />
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-xs-12 col-sm-12">
                    <div class="text-center m-b-10">
                        <button class="btn btn-success waves-effect waves-light show-calendar" id="show-calendar-{{ $type }}" data-type="{{ $type }}">
                            Ventas por rango de fecha
                        </button>
                    </div>

                    <div class="white-box text-center bg-theme-dark">
                        <h1 class="text-white">
                            <span class="counter" id="totalSales-{{ $type }}">
                                {{$totalSales}}
                            <span>
                        </h1>
                        <p class="text-white font-16" id="totalSalesDates-{{ $type }}">
                            {{ $dateRange }}
                        </p>
                    </div>
                    @if(showMenu(['LCV-DSE']) && $type == 'h')
                        <div class="text-center m-t-10">
                            <a href="{{route('client.scheduleList')}}" class="btn btn-success waves-effect waves-light">
                                <span>Ver Agendas</span> <i class="fa fa-calendar m-l-5"></i>
                            </a>
                        </div>
                    @endif
                </div>

                <div class="col-lg-7 col-md-5 col-xs-12 col-sm-12">
                    <h3 class="text-center">Detalle de ventas</h3>

                    <div id="table-detail-sales-{{ $type }}">
                        @include('dashboard.tableSalesDetail', ['salesDetail' => $salesDetail])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>