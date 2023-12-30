@extends('layouts.mail')
@section('content')
<tr>
	<td bgcolor="#fff" align="center">
		<table width="90%">
			<tr>
				<td>
					<h1> Enlace de pago para servicios Netwey</h1>
				</td>
			</tr>
			<tr>
				<td>
					<p> Bienvenido sr(a) <b> {{$DataBody['client_name']}}</b> </p>
				</td>
			</tr>
			<tr>
				<td>
					<p>
						{{$DataBody['bodytext']}}
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<h3> Ya esta casi listo la instalación del servicio, procede a pagar el servicio dando click <a href="{{$DataBody['urlqr']}}"> AQUÍ</a> para ser redireccionado a la plataforma de pago</br></br>
					</h3>
				</td>
			</tr>
			@if(!empty($DataBody['nota']))
				<tr>
					<td>
						<p><strong>Nota:</strong> {{$DataBody['nota']}}</p></br>
					</td>
				</tr>
			@endif
	</table>
</td>
</tr>
@stop
