@if(session()->has('message_error'))
	<div class="myadmin-alert myadmin-alert-icon myadmin-alert-click {{(session()->has('message_class')) ? session('message_class') : 'alert-success'}} myadmin-alert-top alerttop" style="display: block;">
		<i class="ti-comment"></i> {{ session('message_error') }}
		<a href="#" class="closed">×</a>
	</div>
	{{session()->forget('message_error')}}
	{{session()->save()}}
@endif