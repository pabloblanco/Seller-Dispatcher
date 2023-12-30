<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<title>Netwey</title>

		<style type="text/css">
			/* CLIENT-SPECIFIC STYLES */
			body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
			table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
			img { -ms-interpolation-mode: bicubic; }

			/* RESET STYLES */
			img { border: 0; outline: none; text-decoration: none; }
			table { border-collapse: collapse !important; }
			body { margin: 0 !important; padding: 0 !important; width: 100% !important; }

			/* iOS BLUE LINKS */
			a[x-apple-data-detectors] {
			color: inherit !important;
			text-decoration: none !important;
			font-size: inherit !important;
			font-family: inherit !important;
			font-weight: inherit !important;
			line-height: inherit !important;
			}

			/* ANDROID CENTER FIX */
			div[style*="margin: 16px 0;"] { margin: 0 !important; }

			/* MEDIA QUERIES */
			@media all and (max-width:639px){ 
			.wrapper{ width:320px!important; padding: 0 !important; }
			.container{ width:300px!important;  padding: 0 !important; }
			.mobile{ width:300px!important; display:block!important; padding: 0 !important; }
			.img{ width:100% !important; height:auto !important; }
			*[class="mobileOff"] { width: 0px !important; display: none !important; }
			*[class*="mobileOn"] { display: block !important; max-height:none !important; }
			}

			@yield('customCSS') 
		</style> 
	</head>

	<body style="margin:0; padding:0; background-color:#fff;">
		<center>
			<table width="640" border="0" cellpadding="0" cellspacing="0" bgcolor="#000">
				<tr>
					<td align="center" valign="top">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td height="30" style="font-size:30px; line-height:30px; class="mobileOn"">&nbsp;</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<img src="{{asset('images/logo_header.png')}}" width="" height="" style="margin:0; padding:0; border:none; display:block;" border="0" alt="header" /> 
								</td>
							</tr>
							<tr>
								<td height="30" style="font-size:30px; line-height:30px;" class="mobileOn">&nbsp;</td>
							</tr> 
						</table>     
					</td>
				</tr>

				@yield('content')

				<tr>
					<td align="center" valign="top">
						<table width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td height="30" style="font-size:30px; line-height:30px;" class="mobileOn" >&nbsp;</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<img src="{{asset('images/logo_footer.png')}}" width="" height="" style="margin:0; padding:0; border:none; display:block;" border="0" alt="header" /> 
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<p style="color: #fff">Servicio de navegación en tu hogar.</p>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<p style="color: #fff"> Copyright © {{date('Y')}} <a href="{{env('APP_URL')}}" target="_blank" style="color:#fff; text-decoration:underline;">netwey</a>. Todos los derechos reservados.</p>
								</td>
							</tr>
							<tr>
								<td height="30" style="font-size:30px; line-height:30px;" class="mobileOn">&nbsp;</td>
							</tr> 
						</table>     
					</td>
				</tr>
			</table>
		</center>
	</body>
</html>