<div class="col-md-12">
    <div class="white-box">
        <h3 class="box-title">{{ $title }}</h3>

        <div class="row">
            <div class="col-lg-2 col-md-3 col-xs-12 col-sm-6 text-center">
                <div class="icon-dashboard">
                    <i class="fa fa-money icon"></i>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-xs-12 col-sm-12">
                <div class="white-box text-center bg-theme-dark">
                    <h1 class="text-white">$<span class="counter">{{ $amount }}<span></h1>
                    <p class="text-white font-18">Monto</p>
                </div>
            </div>

            @if(!empty($detailCash) && $detailCash)
            <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 m-b-15">
                <button type="button" data-toggle="modal" data-target="#detail-modal" class="btn btn-success waves-effect waves-light m-r-10 btn-dashboard-v">Ver detalle</button>
            </div>
            @endif
        </div>
    </div>
</div>