<!-- Left navbar-header -->
<div class="navbar-default sidebar" role="navigation">
  <div class="sidebar-nav navbar-collapse">
    <ul class="nav" id="side-menu">
      <li class="user-pro">
        <a class="waves-effect" href="{{route('dashboard')}}">
          <span class="hide-menu">
            {{session('name')}} {{session('last_name')}}
            <span class="fa arrow">
            </span>
          </span>
        </a>
      </li>
      @if(showMenu(['LCL-DSE','RCL-DSE']))
        <li>
          <a class="waves-effect {{request()->is('prospect/*')?'active':''}}" href="index.html">
            <i class="zmdi zmdi-accounts zmdi-hc-fw fa-fw"></i>
            <span class="hide-menu">
              Prospectos
              <span class="fa arrow"></span>
            </span>
          </a>
          <ul class="nav nav-second-level">
            @if(showMenu(['RCL-DSE']))
            <li>
              <a href="{{route('client.register')}}">
                Registrar
              </a>
            </li>
            @endif
            @if(showMenu(['LCL-DSE']))
            <li>
              <a href="{{route('client.list')}}">
                Listar
              </a>
            </li>
            @endif
          </ul>
        </li>
      @endif

      {{-- Venta en abono --}}
      @if(session('hierarchy') < 7 && showMenu(['SEL-PSI']))
        <li class="custom-item-menu">
          <a class="waves-effect h-it {{request()->is('installments/*')?'active':''}}" href="#" id="sale-ins">
            <div class="noti-cont">
              <i class="ti-wallet fa-fw"></i>
              <div class="notify">
                <span class="heartbit"></span>
                <span class="point"></span>
              </div>
            </div>
            <span class="hide-menu">
              Venta en abono
              <span class="fa arrow"></span>
            </span>
          </a>
          <ul class="nav nav-second-level">
            <li class="custom-item-menu">
              <a class="notify-content" href="{{route('installments.reportsMI')}}">
                <span class="hide-menu txt">
                  Modems en abono
                </span>
              </a>
            </li>
            <li class="custom-item-menu">
              <a class="notify-content" href="{{route('installments.requests')}}">
                <span class="hide-menu txt">
                  Solicitudes
                </span>
                <span class="hide-menu number" id="n-req">
                  0
                </span>
              </a>
            </li>
            <li class="custom-item-menu">
              <a class="notify-content" href="{{route('installments.pendingPay')}}">
                <span class="hide-menu txt">
                  Pendientes por pago
                </span>
                <span class="hide-menu number" id="e-pay">
                  0
                </span>
              </a>
            </li>
          </ul>
        </li>
      @endif

      @if(session('hierarchy') >= 7 && showMenu(['DSI-DSE']))
        <li class="custom-item-menu">
          <a class="waves-effect h-it {{request()->is('installments/*')?'active':''}}" href="#" id="sale-ins">
            <div class="noti-cont">
              <i class="ti-wallet fa-fw"></i>
              <div class="notify">
                <span class="heartbit"></span>
                <span class="point"></span>
              </div>
            </div>
            <span class="hide-menu">
              Venta en abono
              <span class="fa arrow"></span>
            </span>
          </a>
          <ul class="nav nav-second-level">
            <li class="custom-item-menu">
              <a class="notify-content" href="{{route('installments.sellerRequests')}}">
                <span class="hide-menu txt">
                  Solicitudes
                </span>
                <span class="hide-menu number" id="n-req">
                  0
                </span>
              </a>
            </li>
            <li class="custom-item-menu">
              <a class="notify-content" href="{{route('installments.pendingPaySeller')}}">
                <span class="hide-menu txt">
                  Pendientes por pago
                </span>
                <span class="hide-menu number" id="e-pay">
                  0
                </span>
              </a>
            </li>
          </ul>
        </li>
      @endif

      @if(showMenu(['LCV-DSE', 'CDV-DSE']))
        <li>
          <a class="waves-effect {{request()->is('date/*')?'active':''}}" href="index.html">
            <i class="zmdi zmdi-account-calendar zmdi-hc-fw fa-fw"></i>
            <span class="hide-menu">
              Agenda
              <span class="fa arrow"></span>
            </span>
          </a>
          <ul class="nav nav-second-level">
            @if(showMenu(['CDV-DSE']))
              <li>
                <a href="{{route('date.new')}}">
                  Crear cita
                </a>
              </li>
            @endif
            @if(showMenu(['LCV-DSE']))
              <li>
                <a href="{{route('client.scheduleList')}}">
                  Listar citas
                </a>
              </li>
            @endif
          </ul>
        </li>
      @endif

      @if(showMenu(['ACV-DSE','RSC-DSE','ARV-DSE', 'SEL-FIB']))
        <li>
          <a class="waves-effect {{request()->is('sale/*')?'active':''}}" href="index.html">
            <i class="zmdi zmdi-money-box zmdi-hc-fw fa-money-bill-alt"></i>
            <span class="hide-menu">
              Ventas
              <span class="fa arrow"></span>
            </span>
          </a>
          <ul class="nav nav-second-level">
            @if(showMenu(['ARV-DSE']))
              <li>
                <a href="{{route('seller.onlyProduct')}}">
                  Venta
                </a>
              </li>
            @endif
            @if(showMenu(['ACV-DSE']))

          <li>
            <a href="{{route('seller.index')}}">
              Venta + Activación
            </a>
          </li>
          @endif
          @if(showMenu(['SEL-MOV', 'ACV-DSE']))
          <li>
            <a href="{{route('payjoy.associatePayjoy')}}">
              PayJoy
            </a>
          </li>
          <li>
            <a href="{{route('paguitos.associatePaguitos')}}">
              Paguitos
            </a>
          </li>
          @if(showMenu(['SEL-TLP']))
          <li>
            <a href="{{route('telmovpay.initTelmov')}}">
              <i aria-hidden="true" class="fa fa-mobile">
              </i>
              TelmovPay
            </a>
          </li>

          {{--
          <li>
            <a class="nav-link collapsed text-truncate" data-target="#submenuTelmov" data-toggle="collapse" href="#submenuTelmov">
              <i aria-hidden="true" class="fa fa-mobile">
              </i>
              <span class="hide-menu">
                TelmovPay
                <span class="fa arrow">
                </span>
              </span>
            </a>
            <div aria-expanded="false" class="collapse pl-5" id="submenuTelmov">
              <ul class="flex-column pl-2 nav">
                <li class="nav-item">
                  <a class="nav-link py-0" href="#">
                    <a href="{{route('telmovpay.initTelmov')}}">
                      Iniciar financiamiento
                    </a>
                    <span>
                    </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link py-0" href="#">
                    <a href="{{route('telmovpay.asociateFinanceTelmov')}}">
                      Asociar financiamiento
                    </a>
                  </a>
                </li>
              </ul>
            </div>
          </li>
          --}}
          @endif
          @endif
          @if(showMenu(['RSC-DSE']))
          <li>
            <a href="{{route('charger.index')}}">
              Recargas
            </a>
          </li>
          @endif
          @if(showMenu(['MIG-DSE']))
          <li>
            <a href="{{route('seller.migrations')}}">
              Migración
            </a>
          </li>
          @endif
          @if(showMenu(['SEL-FIB']))
          <li>
            <a href="{{route('sellerFiber.index')}}">
              Venta fibra
            </a>
          </li>
          <li>
            <a href="{{route('sellerFiber.payPending')}}">
              Instalaciones de fibra por cobrar
            </a>
          </li>
          @endif
        </ul>
      </li>
      @endif

      @if(showMenu(['ACT-DSE', 'CRV-DSE','EDC-DSE', 'REP-IPS']))
        <li>
          <a class="waves-effect {{request()->is('sale/*')?'active':''}}" href="index.html">
            <i class="fa fa-check-square fa-fw icon"></i>
            <span class="hide-menu">
              Reportes
              <span class="fa arrow"></span>
            </span>
          </a>
          <ul class="nav nav-second-level">
            @if(showMenu(['ACT-DSE']))
              <li class="custom-item-menu">
                <a class="waves-effect" href="{{route('coordination.reportActivations')}}" style="font-weight: 300 !important;">
                  <div class="notify-content">
                    <span class="hide-menu txt">
                      Activaciones
                    </span>
                    <span class="hide-menu number" id="numberActivations">
                      0
                    </span>
                  </div>
                </a>
              </li>
            @endif
            @if(showMenu(['CRV-DSE']))
              <li>
                <a href="{{route('coordination.reportConcilations')}}">
                  Conciliaciones
                </a>
              </li>
            @endif
            @if(showMenu(['EDC-DSE']))
              <li>
                <a href="{{route('coordination.reportDebtStatus')}}">
                  Estado de Deuda
                </a>
              </li>
            @endif
              {{-- @if(showMenu(['ACT-DSE']))
            <li>
              <a href="{{route('coordination.reportUnConcSales')}}">
                Ventas sin conciliar
              </a>
            </li>
            @endif --}}

            @if(showMenu(['REP-IPS']))
            <li>
              <a href="{{route('fiber.getFiberPendingReport')}}">
                Reporte de instalaciones
              </a>
            </li>
            @endif
          </ul>
        </li>
      @endif

      {{--Opcion de bajas de vendedores--}}
      @if(showMenu(['SEL-LOW','SEL-VLW']))
        <li>
          <a class="waves-effect {{request()->is('low/*')?'active':''}}" href="index.html">
            <i aria-hidden="true" class="fa fa-user-times">
            </i>
            <span class="hide-menu">
              Bajas
              <span class="fa arrow">
              </span>
            </span>
          </a>
          <ul class="nav nav-second-level">
            @if(showMenu(['SEL-LOW']) && session('user_type') != 'vendor')
            <li>
              <a href="{{route('low.new-request')}}">
                Solicitar baja
              </a>
            </li>
            @endif
          @if(showMenu(['SEL-VLW']) && session('user_type') != 'vendor')
            <li>
              <a href="{{route('low.viewRequestsList')}}">
                Ver bajas solicitadas
              </a>
            </li>
            @endif
          </ul>
        </li>
      @endif
      {{--END Opcion de bajas de vendedores--}}

      @if(showMenu(['LCN-DSE']))
        <li>
          <a class="waves-effect" href="{{route('client.listClient')}}">
            <i class="zmdi zmdi-face zmdi-hc-fw fa-fw">
            </i>
            <span class="hide-menu">
              Clientes
            </span>
          </a>
        </li>
      @endif
      {{--Quitar esto cuando se active inventarios--}}
      @if(showMenu(['A1V-G1V', 'LST-GIP', 'ACV-DSE', 'SEL-MOV', 'SEL-MIF', 'SEL-FIB', 'SEL-ARI', 'A2V-G2V']))
        <li>
          <a class="waves-effect {{request()->is('coordination/*')?'active':''}}" href="index.html">
            <i class="zmdi zmdi-library zmdi-hc-fw fa-money-bill-alt">
            </i>
            <span class="hide-menu">
              Inventario
              <span class="fa arrow">
              </span>
            </span>
          </a>
          <ul class="nav nav-second-level">
            @if(showMenu(['A1V-G1V']) && session('user_type') != 'vendor')
            <li>
              <a href="{{route('coordination.stock')}}">
                Asignar inventario
              </a>
            </li>
            <li>
              <a href="{{route('inventory.preassignedStatus')}}">
                Estatus inventario pre-asignado
              </a>
            </li>
            @endif
            @if(showMenu(['A2V-G2V']) && session('user_type') == 'vendor')
              <li>
              <a href="{{route('inventory.installers.stock')}}">
                Asignar inventario a instaladores
              </a>
            </li>
            @endif
            @if(showMenu(['LST-GIP']))
            <li>
              <a href="{{route('inventory.pendingFolios')}}">
                Lista de guías pendientes
              </a>
            </li>
            @endif
            @if(showMenu(['ACV-DSE', 'SEL-MOV', 'SEL-MIF', 'SEL-FIB']))
            <li>
              <a href="{{route('inventory.listDNOOR')}}">
                Lista de MSISDNs con notificación
              </a>
            </li>
            @endif
            @if(showMenu(['SEL-ARI']) && session('user_type') == 'vendor')
            <li>
              <a href="{{route('inventory.preassigned')}}">
                Aceptar o Rechazar Inventario Pre-Asignado
              </a>
            </li>
            @endif
          </ul>
        </li>
      @endif

      {{--@if(showMenu(['LST-GIP']))
        <li>
          <a class="waves-effect" href="{{route('inventory.pendingFolios')}}">
            <i class="zmdi zmdi-face zmdi-hc-fw fa-fw">
            </i>
            <span class="hide-menu">
              Lista de guías pendientes
            </span>
          </a>
        </li>
        @endif--}}

      @if(showMenu(['SMV-DSE']))
        <li>
          <a class="waves-effect" href="{{route('seller.statusNumber')}}">
            <i class="zmdi zmdi-portable-wifi fa-fw">
            </i>
            <span class="hide-menu">
              Estatus
            </span>
          </a>
        </li>
      @endif
        <li>
          <a class="waves-effect" href="{{route('seller.comparative')}}">
            <i class="ti-info-alt fa-fw">
            </i>
            <span class="hide-menu">
              Comparativo
            </span>
          </a>
        </li>
      @if(showMenu(['SEL-MOV']))
        <li>
          <a class="waves-effect" href="{{url('/files/guia_movilidad.pdf')}}" target="_blank">
            <i class="ti-info-alt fa-fw">
            </i>
            <span class="hide-menu">
              Soporte Telefonía
            </span>
          </a>
        </li>
      @endif

      {{--@if(showMenu(['A1V-G1V']) && session('user_type') != 'vendor')
        <li>
          <a class="waves-effect" href="{{route('coordination.stock')}}">
            <i class="zmdi zmdi-library zmdi-hc-fw fa-money-bill-alt">
            </i>
            <span class="hide-menu">
              Asignar inventario
            </span>
          </a>
        </li>
        @endif--}}

      @if(showMenu(['RMV-DSE']) && session('user_type') != 'vendor')
        <li class="custom-item-menu" id="icon-rf">
          {{-- h-it oculta la notificación, v-it muestra la notificación --}}
          <a class="waves-effect h-it" href="{{route('coordination.reception')}}">
            <div class="noti-cont">
              <i class="ti-receipt fa-fw">
              </i>
              <div class="notify">
                <span class="heartbit">
                </span>
                <span class="point">
                </span>
              </div>
            </div>
            <span class="hide-menu">
              Recepción efectivo
            </span>
          </a>
        </li>
      @endif

      @if(showMenu(['NMD-DSE']))
        <li class="custom-item-menu">
          {{-- h-it oculta la notificación, v-it muestra la notificación --}}
          <a class="waves-effect h-it" href="{{route('seller.cashDelivery')}}" id="icon-dn">
            <div class="noti-cont">
              <i class="ti-receipt fa-fw">
              </i>
              <div class="notify">
                <span class="heartbit">
                </span>
                <span class="point">
                </span>
              </div>
            </div>
            <span class="hide-menu">
              Entrega de efectivo
            </span>
          </a>
        </li>
      @endif

      {{-- Nomina --}}
      @if(showMenu(['NOM-DSE']))
        <li>
          <a class="waves-effect" href="{{route('Nomina.index')}}">
            <i class="ti-receipt fa-fw">
            </i>
            <span class="hide-menu">
              Recibos de nómina
            </span>
          </a>
        </li>
      @endif

      @if(session('user_type') == 'coordinador')
        <li>
          <a class="waves-effect" href="{{url('/files/Libreta_del Coordinador_V2.pdf')}}" target="_blank">
            <i class="fa fa-arrow-down fa-fw">
            </i>
            <span class="hide-menu">
              Libreta del coordinador
            </span>
          </a>
        </li>
      @endif
      <li>
        <a class="waves-effect" href="index.html">
          <i class="fa fa-check-square fa-fw icon">
          </i>
          <span class="hide-menu">
            Sitio de capacitación
            <span class="fa arrow">
            </span>
          </span>
        </a>
        <ul class="nav nav-second-level">
          <li>
            <a href="https://sites.google.com/view/asesor-capacitacion-netwey/inicio?authuser=1" target="_blank">
              Ingresa al sitio de capacitación y comunicación para conocer toda la información disponible de todos nuestros productos
            </a>
          </li>
        </ul>
      </li>
      <li>
        <a class="waves-effect" href="{{route('logout')}}">
          <i class="zmdi zmdi-power zmdi-hc-fw fa-fw">
          </i>
          <span class="hide-menu">
            Salir
          </span>
        </a>
      </li>
    </ul>
  </div>
</div>
<!-- Left navbar-header end -->
