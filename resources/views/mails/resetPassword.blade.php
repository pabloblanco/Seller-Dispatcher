@extends('layouts.mail')

@section('content')
	<tr>
		<td>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#fff">
				<tr>
					<td align="center" valign="top">
						<h1 style="color: #000;">Cambio de contrase&ntilde;a netwey</h1>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<p style="color: #000;">
							Hola {{$data->name}} {{$data->last_name}}, para restablecer tu contraseña debes hacer click <a href="{{route('login.changePassword',['hash' => $data->hash])}}" target="_blank" style="color:#000; text-decoration:underline;">aqu&iacute;</a> o copiar el siguiente enlace en tu navegador
							<br>
							{{route('login.changePassword',['hash' => $data->hash])}}
							<br>
						</p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
@stop