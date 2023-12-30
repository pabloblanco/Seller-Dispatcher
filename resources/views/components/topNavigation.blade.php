<!-- Top Navigation -->
<nav class="navbar navbar-default navbar-static-top m-b-0">
    <div class="navbar-header">
        <a class="navbar-toggle hidden-sm hidden-md hidden-lg" href="javascript:void(0)" data-toggle="collapse" data-target=".navbar-collapse">
            <i class="ti-menu"></i>
        </a>
        <!-- Logo -->
        <div class="top-left-part">
            <a class="logo" href="{{route('dashboard')}}">
                <!-- Logo icon image, you can use font-icon also -->
                <b>
                    <img src="{{asset('images/logo_header_img.png')}}" alt="home" style="margin: 15px 0px;" />
                </b>
                <!-- Logo text image you can use text also -->
                <span class="hidden-xs"><img src="{{ asset('images/logo_header_text.png') }}" alt="home" /></span>
            </a>
        </div>
        <!-- /Logo -->
        <!-- This is for mobile view search and menu icon -->
        <ul class="nav navbar-top-links navbar-left hidden-xs">
            <li>
                <a href="javascript:void(0)" class="open-close hidden-xs waves-effect waves-light" @if(request()->is('dashboard')) style="padding-top: 20px;" @endif>
                    <i class="icon-arrow-left-circle ti-menu"></i>
                </a>
            </li>
        </ul>

        @if(showMenu(['RMV-DSE']) && session('user_type') == 'coordinador')
        <ul class="nav navbar-top-links navbar-right pull-right m-r-20" id="icon-notification" hidden>
            <li class="dropdown">
                <a class="dropdown-toggle waves-effect waves-light" data-toggle="dropdown" href="#" style="display: inline-grid;padding-top: 22px;">
                    <i class="ti-check-box"></i>
                    <div class="notify">
                        <span class="heartbit" style="top: -9px;"></span>
                        <span class="point" style="top: 1px;"></span>
                    </div>
                </a>

                <ul class="dropdown-menu mailbox animated bounceInDown">
                    <li>
                        <div class="drop-title">Tienes notificiones</div>
                    </li>
                    
                    <li>
                        <div class="message-center" id="notification-content">

                        </div>
                    </li>

                    <li>
                        <a class="text-center" href="{{route('coordination.reception')}}">
                            <strong>Ver todas</strong>
                            <i class="fa fa-angle-right"></i>
                        </a>
                    </li>
                </ul>
                <!-- /.dropdown-messages -->
            </li>
            <!-- /.dropdown -->
        </ul>
        @endif
    </div>
    <!-- /.navbar-header -->
    <!-- /.navbar-top-links -->
    <!-- /.navbar-static-side -->
</nav>
<!-- End Top Navigation -->