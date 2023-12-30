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
            <h4 class="page-title"> Comparativos </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">informaci&oacute;n</a></li>
                <li class="active">Comparativo</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                <h3 class="box-title">Comparativos NETWEY VS : </h3>
                <div class="col-md-12">
                    <blockquote>
                        <a href="https://secure.netwey.com.mx/site/download/comparative/Telcel.pdf" target="_blank" class="edit waves-effect font-20 m-r-10">
                            <label>1.- TELCEL</label>
                        </a>
                    </blockquote>
                    <blockquote>
                        <a href="https://secure.netwey.com.mx/site/download/comparative/ATT.pdf" target="_blank" class="edit waves-effect font-20 m-r-10">
                            <label>2.- AT&T</label>
                        </a>
                    </blockquote>
                    <blockquote>
                        <a href="https://secure.netwey.com.mx/site/download/comparative/Blue_Telecomm.pdf" target="_blank" class="edit waves-effect font-20 m-r-10">
                            <label>3.- BLUE TELECOM</label>
                        </a>
                    </blockquote>
                    <blockquote>
                        <a href="https://secure.netwey.com.mx/site/download/comparative/Dish_ON.pdf" target="_blank" class="edit waves-effect font-20 m-r-10">
                            <label>4.- DISH ON</label>
                        </a>
                    </blockquote>
                    <blockquote>
                        <a href="https://secure.netwey.com.mx/site/download/comparative/Izzi_Flex.pdf" target="_blank" class="edit waves-effect font-20 m-r-10">
                            <label>5.- IZZI FLEX</label>
                        </a>
                    </blockquote>
                    <blockquote>
                        <a href="https://secure.netwey.com.mx/site/download/comparative/Movistar.pdf" target="_blank" class="edit waves-effect font-20 m-r-10">
                            <label>6.- MOVISTAR</label>
                        </a>
                    </blockquote>
                </div>
                </div>
            </div>
            <div class="white-box">
                <div class="row">
                <div class="col-md-12">
                    <blockquote>
                        <a href="https://secure.netwey.com.mx/site/download/comparative/Netwey.pdf" target="_blank" class="edit waves-effect font-20 m-r-10">
                            <label>A.- PORQU&Eacute; NETWEY</label>
                        </a>
                    </blockquote>
                    <blockquote>
                        <a href="https://secure.netwey.com.mx/site/download/comparative/mis_datos.pdf" target="_blank" class="edit waves-effect font-20 m-r-10">
                            <label>B.- QUE PUEDO HACER CON MIS GB</label>
                        </a>
                    </blockquote>
                </div>
                </div>
            </div>
        </div>
    </div>
@stop