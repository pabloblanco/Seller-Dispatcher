@extends('layouts.admin')

@section('content')
	@include('components.messages')
    @include('components.messagesAjax')

	@if ($errors->any())
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
            <h4 class="page-title"> N&oacute;mina </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">N&oacute;mina</a></li>
                {{-- <li class="active">Comparativo</li> --}}
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="table-responsive">
                    @if(!empty($user_c) && !empty($user_c->url_latter_contract))
                    <h3 class="box-title">Carta patronal</h3>

                    <table class="table table-nomina">
                        <thead>
                            <tr>
                                <th width="50%">RFC</th>
                                <th width="50%" class="icon-content">Archivo para descargar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $user_c->dni }}</td>
                                <td class="icon-content">
                                    <a href="#" data-name="{{ $user_c->url_latter_contract }}" class="download-contract waves-effect">
                                        <img class="icon-pdf" src="{{ asset('images/pdf.png') }}">
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    @endif

                    <h3 class="box-title">Recibos de n&oacute;mina y asimilados</h3>

                    @if(count($recs))
                        <table class="table table-nomina">
                            <thead>
                                <tr>
                                    <th width="50%">Periodo</th>
                                    <th width="50%" class="icon-content">Archivo para descargar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recs as $rec)
                                    <tr>
                                        <td>{{ date('d/m/Y', strtotime($rec->date_nom)) }}</td>
                                        <td class="icon-content">
                                            <a href="#" data-name="{{ $rec->name_file }}" class="download-recive waves-effect" data-type="{{ $rec->type }}">
                                                <img class="icon-pdf" src="{{ asset('images/pdf.png') }}">
                                                @if($rec->type == 'N')
                                                <small>N&oacute;mina</small>
                                                @else
                                                <small>Asimilados</small>
                                                @endif
                                            </a>

                                            @if(!empty($rec->rel))
                                            <a href="#" data-name="{{ $rec->rel->name_file }}" data-type="{{ $rec->rel->type }}" class="download-recive waves-effect">
                                                <img class="icon-pdf" src="{{ asset('images/pdf.png') }}">
                                                @if($rec->rel->type == 'N')
                                                <small>N&oacute;mina</small>
                                                @else
                                                <small>Asimilados</small>
                                                @endif
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No tienes recibos de n&oacute;mina registrados.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script type="text/javascript">
        $(function () {
            $('.download-contract').on('click', function(e){
                var name = $(e.currentTarget).data('name');

                e.preventDefault();

                if(name && name != ''){
                    $(".preloader").fadeIn();

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        url: '{{ route('Nomina.getFileContract') }}',
                        type: 'post',
                        data: {name: name},
                        success: function (data){
                            $(".preloader").fadeOut();

                            if(!data.error){
                                var link = document.createElement('a');
                                link.href = data.url;
                                link.target = '_blank';

                                if(document.createEvent) {
                                    var e = document.createEvent('MouseEvents');
                                    e.initEvent('click', true, true);
                                    link.dispatchEvent(e);
                                    return true;
                                }

                                window.open(data.url, 'letter.pdf');
                            }else{
                                if(data.message == 'TOKEN_EXPIRED'){
                                    showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                                }else{
                                    showMessageAjax('alert-danger', data.message);
                                }
                            }
                        },
                        error: function (res) {
                            console.log(res);
                            $(".preloader").fadeOut();
                        }
                    });
                }
            });


            $('.download-recive').on('click', function(e){
                var name = $(e.currentTarget).data('name'),
                    type = $(e.currentTarget).data('type');

                e.preventDefault();

                if(name && name != '' && type && type != ''){
                    $(".preloader").fadeIn();

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        url: '{{ route('Nomina.getFile') }}'+'/'+type,
                        type: 'post',
                        data: {name: name},
                        success: function (data){
                            $(".preloader").fadeOut();

                            if(!data.error){
                                var link = document.createElement('a');
                                link.href = data.url;
                                link.target = '_blank';

                                if(document.createEvent) {
                                    var e = document.createEvent('MouseEvents');
                                    e.initEvent('click', true, true);
                                    link.dispatchEvent(e);
                                    return true;
                                }

                                window.open(data.url, 'recibo.pdf');
                            }else{
                                if(data.message == 'TOKEN_EXPIRED'){
                                    showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                                }else{
                                    showMessageAjax('alert-danger', data.message);
                                }
                            }
                        },
                        error: function (res) {
                            console.log(res);
                            $(".preloader").fadeOut();
                        }
                    });
                }
            });
        });
    </script>
@stop