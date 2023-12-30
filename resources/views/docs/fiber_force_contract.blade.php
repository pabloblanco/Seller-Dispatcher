<body id="archivebody" style="height: 100%;margin: 0;padding: 0;width: 100%; background-color: #FAFAFA; font-family: Arial, Helvetica, sans-serif; font-size: 0.7rem;">
    <center>
        <table style="border: solid lightgrey; width: 100%; padding: 1px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="border-right: solid lightgrey;">
                    <p style="color: grey; padding-left: 5px;">
                        <b>ISLIM TELCO, S.A.P.I. DE C.V. </b><br>
                        Javier Barros Sierra 495, Piso 2, Oficina 110 <br> 
                        Santa Fe Centro Ciudad, Alcaldía Álvaro Obregón <br>
                        C.P. 01376, Ciudad de México <br>
                        RFC: ITE180215P68
                    </p>
                </td>
                <td style="width: 35%;">
                    <center>
                        <img src="{{public_path('images/image4.png')}}" style="opacity: 0.4; height: 48px; width: 218px;">
                    </center>
                </td>
            </tr>
        </table>
        <br><br><br>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">
            <tr style="background-color: lightgrey;">
                <th style="align-content: center; border-bottom: solid grey;" colspan="8">
                    <b>NOMBRE DEL SUSCRIPTOR Y DOMICILIO DE INSTALACIÓN</b>
                </th>
            </tr>
            <tr style="background-color: lightgrey;">
                <th style="align-content: center; border-bottom: solid grey;" colspan="4">
                    <b>Nombres</b>
                </th>
                <th style="align-content: center; border-bottom: solid grey;" colspan="4" >
                    <b>Apellidos</b>
                </th>
            </tr>
            <tr>
                <td style="text-align: center;" colspan="4">
                    {{ $data['client_name'] }}
                </td>
                <td style="text-align: center;" colspan="4">
                    {{ $data['client_lname'] }}
                </td>
            </tr>
            <tr style="background-color: lightgrey;">
                <th style="align-content: center; border-bottom: solid grey; border-top: solid grey;" colspan="8">
                    <b>DOMICILIO</b>
                </th>
            </tr>
            <tr>
                <td style="text-align: center;" colspan="8">
                    {{ $data['address'] }}
                </td>
            </tr>
        </table>
        <table style="border: solid grey; border-top: none; width: 100%;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="text-align: center; border-bottom: solid grey; border-right: solid grey; padding: 5px; background-color: lightgrey;" colspan="1">
                    <b>
                        Teléfono&nbsp;Fijo
                    </b>
                </td>
                <td style="text-align: center; border-bottom: solid grey; padding: 5px;" colspan="2">
                   {{ $data['client_phonehome'] }}
                </td>
                <td style="text-align: center; border-bottom: solid grey; border-left: solid grey; border-right: solid grey; padding: 5px; background-color: lightgrey;" colspan="1">
                    <b>
                        Teléfono&nbsp;Móvil
                    </b>
                </td>
                <td style="text-align: center; border-bottom: solid grey; padding: 5px;" colspan="2">
                    {{ $data['client_phone'] ? $data['client_phone'] : 'N/A' }}
                </td>
                <td style="text-align: center; border-bottom: solid grey; border-left: solid grey; border-right: solid grey; padding: 5px; background-color: lightgrey;" colspan="1">
                    <b>
                        RFC
                    </b>
                </td>
                <td style="text-align: center; border-bottom: solid grey; padding: 5px;" colspan="1">
                    N/A
                </td>
            </tr>
            <tr>
                <td style="text-align: center; border-right: solid grey;  background-color: lightgrey;" colspan="3">
                    <b>Fecha de instalación y prestación del servicio.</b><br>
                    (máximo 10 días naturales posteriores a la firma del contrato)
                </td>
                <td style="text-align: center;" colspan="1">
                   {{ $data['date'] }}
                </td>
                <td style="text-align: center; border-right: solid grey; border-left: solid grey; background-color: lightgrey;" colspan="1">
                    <b>
                        Hora
                    </b>
                </td>
                <td style="text-align: center;" colspan="1">
                    {{ $data['schedule'] }}
                </td>
                <td style="text-align: center; border-right: solid grey; border-left: solid grey; background-color: lightgrey;" colspan="1">
                    <b>
                        Costo
                    </b>
                </td>
                <td style="text-align: center;" colspan="1">
                    {{ $data['price'] }}
                </td>
            </tr>
        </table>
        <br>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">
            <tr style="background-color: lightgrey;">
                <th style="text-align: center; padding: 5px;" colspan="8">
                    <b>
                        SERVICIO DE INTERNET FIJO CON PLAZO FORZOSO Y PAGOS MENSUALES
                    </b>
                </th>
            </tr>
            <tr style="background-color: lightgrey;">
                <th style="text-align: center; padding: 5px;" colspan="3">
                    <b>
                        DESCRIPCIÓN PAQUETE/OFERTA <br>
                        (Numeral 5.1.2.1 NOM 184)
                    </b>
                </th>
                <th style="padding: 5px;" colspan="2">
                    <div style="text-align: center;">
                        <b>
                            TARIFA
                        </b>
                    </div>
                    <div  style="text-align: left;">
                        <b>
                            FOLIO IFT:
                        </b>
                    </div>
                </th>
                <th style="text-align: center; padding: 5px;" colspan="3">
                    <b>
                        MODALIDAD PAGO MENSUAL FIJO
                    </b>
                </th>
            </tr>
            <tr>
                <td style="text-align: center; padding: 5px;" colspan="3" rowspan="2">
                    {{ $data['pack'] }}
                </td>
                <td style="text-align: center; padding: 5px; background-color: lightgrey;" colspan="1" rowspan="1">
                    <br>
                    <b>
                        MESUALIDAD
                    </b>
                </td>
                <td style="text-align: center; padding: 5px;" colspan="1" rowspan="1">
                    $ {{ $data['price'] }} M.N
                </td>
                <td style="text-align: center; padding: 5px; background-color: lightgrey;" colspan="3" rowspan="1">
                    <b>
                        VIGENCIA Y PENALIDAD
                    </b>
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 5px; background-color: lightgrey;" colspan="1" rowspan="1">
                    <b>
                        RECONEXIÓN
                    </b>
                </td>
                <td style="text-align: center; padding: 5px;" colspan="1" rowspan="1">
                    $ 100 M.N
                </td>
                 <td style="text-align: left; padding: 5px;" colspan="3" rowspan="1">
                    &#x2022; Plazo Forzoso: 12 Meses <br>
                    &#x2022; Con Penalidad: Pago de mensualidades faltantes <br> 
                    del plazo forzoso, en caso de cancelación anticipada.
                </td>
            </tr>
            <tr style="background-color: lightgrey;">
                <th style="text-align: center; padding: 5px;" colspan="8">
                    <b>
                        SERVICIO DE INTERNET FIJO CON PLAZO FORZOSO Y PAGOS MENSUALES
                    </b>
                </th>
            </tr>
        </table>
        <br>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">
            <tr style="background-color: lightgrey;">
                <td style="text-align: center; padding: 5px;" colspan="8">
                    <b>
                        DATOS DEL EQUIPO DE INTERNET FIJO ENTREGADO
                    </b>
                </td>
            </tr>
            <tr style="background-color: lightgrey;">
                <td style="text-align: center; padding: 5px;" colspan="3">
                    <b>
                        <div style="float: left; width: 50%; margin-left: 25%;">
                            COMODATO&nbsp;
                        </div>
                        <div style="margin-left: 75%; margin-bottom: 5%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;">X</div>
                        </div>
                    </b>
                </td>
                <td style="text-align: center; padding: 5px;" colspan="2">
                    <b>
                        <div style="float: left; width: 70%; margin-left: 5%;">
                            COMPRA-VENTA&nbsp;
                        </div>
                        <div style="margin-left: 80%; margin-bottom: 5%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"></div>
                        </div>
                    </b>
                </td>
                <td style="text-align: center; padding: 5px;" colspan="3">
                    <b>
                        <div style="float: left; width: 60%; margin-left: 5%;">
                            ARRENDAMIENTO&nbsp;
                        </div>
                        <div style="margin-left: 65%; margin-bottom: 5%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"></div>
                        </div>
                    </b>
                </td>
            </tr>
        </table>
        <table style="border: solid grey; border-top: none; width: 100%;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding: 5px; border-bottom: solid grey; border-right: solid grey;" colspan="4">
                    <b>Marca: </b>Fiberstore
                </td>
                <td style="padding: 5px; border-bottom: solid grey;" colspan="4">
                    <b>Número de Equipos: </b>1
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; border-bottom: solid grey; border-right: solid grey;" colspan="4">
                    <b>Modelo: </b>Router Onu
                </td>
                <td style="padding: 5px; border-bottom: solid grey;" colspan="4">
                    <b>Cantidad a pagar por equipo: </b>$ 0 total por mes
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; border-right: solid grey;" colspan="4">
                    <b>
                        Garantía de cumplimiento de obligación <br>
                        en caso de comodato
                    </b>
                </td>
                <td style="padding: 5px;" colspan="4">
                    Pagaré para garantizar la devolución del equipo <br>entregado en comodato, anexo de esta carátula <br>
                    y contrato de adhesión.
                </td>
            </tr>
        </table>
        <br>
        <table style="border: solid grey; border-bottom: none; width: 100%;" cellpadding="0" cellspacing="0">
            <tr style="background-color: lightgrey;">
                <td style="text-align: center; padding: 5px;" colspan="8">
                    <b>
                        CONCEPTOS FACTURABLES (Cambio de domicilio, Costosadministrativos adicionales)
                    </b>
                </td>
            </tr>

        </table>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">
            <tr style="">
                <td style="background-color: lightgrey; padding: 5px; border-right: solid grey; background-color: lightgrey;" colspan="1">
                    <b>
                        1.- 
                    </b>
                </td>
                <td style="padding: 5px; border-right: solid grey; width: 42%;" colspan="2">
                    <b>
                        Cambio de domicilo
                    </b>
                    
                </td>
                <td style="background-color: lightgrey; padding: 5px; border-right: solid grey; background-color: lightgrey;" colspan="1">
                    <b>
                        2.- 
                    </b>
                </td>
                <td style="padding: 5px; width: 42%;" colspan="2">
                    <b>
                        Daño de Equipo
                    </b>
                </td>
            </tr>
            <tr style="">
                <td style="background-color: lightgrey; border-top: solid grey; border-bottom: solid grey; padding: 5px; border-right: solid grey; background-color: lightgrey;" colspan="3">
                    <b>
                        Un cliente con servicio activo puede solicitar un cambio de domicilio siempre y cuando Netwey cuente con cobertura en su nuevo domicilio.
                    </b>
                </td>
                <td style="background-color: lightgrey; border-top: solid grey; border-bottom: solid grey; padding: 5px; " colspan="3">
                    <b>
                        Si el cliente daña el equipo en comodato o arrendamiento, deberá resarcir el daño del equipo asignado en comodato o arrendamiento.
                    </b>
                </td>
            </tr>
            <tr style="">
                <td style="padding: 5px; border-right: solid grey;" colspan="3">
                    <b>
                        $ 0 M.N.
                    </b>
                </td>
                <td style=" padding: 5px; " colspan="3">
                    <b>
                        $ 0 M.N.
                    </b>
                </td>
            </tr>
        </table>
        <br>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">

            <tr style="background-color: lightgrey;">
                <td style="text-align: center; padding: 5px; border-bottom: solid grey;" colspan="8">
                    <b>
                        MEDIOS DE CONTACTO DEL PROVEEDOR PARA FALLAS, ACLARACIONES, CONSULTAS Y CANCELACIONES
                    </b>
                </td>
            </tr>

            <tr>
                <td style="background-color: lightgrey; text-align: center; padding: 5px; border-bottom: solid grey; border-right: solid grey;" colspan="2">
                    <b>
                        TELÉFONO:
                    </b>
                </td>

                <td style="text-align: center; padding: 5px; border-bottom: solid grey; border-right: solid grey;" colspan="2">
                    55 4742 1245
                </td>
                <td style="text-align: center; padding: 5px; border-bottom: solid grey;" colspan="4">
                    Disponible las 24 horas del día los 7 días de la semana
                </td>
            </tr>

            <tr>
                <td style="background-color: lightgrey; text-align: center; padding: 5px; border-right: solid grey;" colspan="2">
                    <b>
                        CORREO ELECTRONICO:
                    </b>
                </td>

                <td style="text-align: center; padding: 5px; border-right: solid grey;" colspan="2">
                    atención_clientes@netwey.com.mx
                </td>
                <td style="text-align: center; padding: 5px;" colspan="4">
                    Disponible las 24 horas del día los 7 días de la semana
                </td>
            </tr>

        </table>

        <table style="border: solid grey; border-top: none; width: 100%;" cellpadding="0" cellspacing="0">

            <tr>
                <td style="background-color: lightgrey; text-align: center; padding: 5px; border-right: solid grey;" colspan="3">
                    <b>
                        CENTROS DE ATENCIÓN A CLIENTES:
                    </b>
                </td>

                <td style="text-align: center; padding: 5px;" colspan="5">
                    Consultar horarios disponibles, días disponibles y centros de <br>atención a clientes  disponibles en la página <br>de internet www.netwey.com.mx
                </td>
            </tr>
        </table>

        <br>
        <br>
        <br>
        <br>
        <br>

        <table style="border: solid lightgrey; width: 100%; padding: 1px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="border-right: solid lightgrey;">
                    <p style="color: grey; padding-left: 5px;">
                        <b>ISLIM TELCO, S.A.P.I. DE C.V. </b><br>
                        Javier Barros Sierra 495, Piso 2, Oficina 110 <br> 
                        Santa Fe Centro Ciudad, Alcaldía Álvaro Obregón <br>
                        C.P. 01376, Ciudad de México <br>
                        RFC: ITE180215P68
                    </p>
                </td>
                <td style="width: 35%;">
                    <center>
                        <img src="{{public_path('images/image4.png')}}" style="opacity: 0.4; height: 48px; width: 218px;">
                    </center>
                </td>
            </tr>
        </table>
        <br>
        <br>
        <br>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">

            <tr>
                <td style="background-color: lightgrey; text-align: center; padding: 5px; border-bottom: solid grey;" colspan="8">
                    <b>
                        MÉTODOS DE PAGO
                    </b>
                </td>
            </tr>
            <tr>    
                <td style="padding-left: 10px; padding-right: 5px;" colspan="8">
                    <p>
                        <div style="float: left; width: 5%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;">X</div>
                        </div>
                        <div style="margin-left: 5%;">
                            En tiendas de conveniencia indicadas en netwey.com.mx
                        </div>
                    </p>
                </td>
            </tr>
            <tr>    
                <td style="padding-left: 10px; padding-right: 5px; padding-bottom: 15px;" colspan="8">
                    <p>
                        <div style="float: left; width: 5%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;">X</div>
                        </div>
                        <div style="margin-left: 5%;">
                            Cargo a tarjeta de crédito o débito mediante el uso de plataformas de pago electrónico certificadas.
                        </div>
                    </p>
                </td>
            </tr>
        </table>
        <br>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">

            <tr>
                <td style="background-color: lightgrey; text-align: center; padding: 5px; border-bottom: solid grey;" colspan="8">
                    <b>
                        CONSULTA CREDITICIA
                    </b>
                </td>
            </tr>
            <tr>    
                <td style="text-align: justify; padding: 5px;" colspan="8">
                    <p style="margin-top: -15%;">
                        <div style="float: left; width: 15%;">
                            Por este conducto 
                        </div>
                        <div style="float: left; width: 2%; margin-left: 0%;">
                            SI&nbsp;
                        </div>
                        <div style="float: left; margin-left: 1%; width: 2%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;">X</div>
                        </div>
                        <div style="float: left; width: 2%; margin-left: 2%;">
                            NO&nbsp;
                        </div>
                        <div style="float: left; width: 2%; margin-left: 1%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"></div>
                        </div>
                        <div style="float: left; margin-left: 2%;" >
                            autorizo a ISLIM TELCO, S.A.P.I. de C.V. para que lleve a cabo investigaciones sobre mi 
                        </div>
                    </p>
                    <p style="margin-top: -15%;">
                      comportamiento crediticio o el de la empresa que represento en las Sociedades de Información Crediticia que estime conveniente. Declaro que conozco la naturaleza y alcance de la información que se solicitará y acepto el uso que ISLIM TELCO, S.A.P.I. de C.V. hará de tal información. También autorizo a ISLIM TELCO, S.A.P.I. de C.V. que realice consultas periódicas de mi historial crediticio o de la empresa que represento. Esta autorización estará vigente a partir de esta fecha y durante todo el tiempo que mantengamos una relación jurídica y comercial. Quien suscribe declara bajo protesta de decir verdad que cuenta con facultades suficientes para hacerlo.
                    </p>
                </td>
            </tr>
        </table>
        <br>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">

            <tr>
                <td style="background-color: lightgrey; text-align: center; padding: 5px; border-bottom: solid grey;" colspan="8">
                    <b>
                        EL SUSCRIPTOR SOLICITA Y AUTORIZA SE LE ENVIE POR MEDIOS ELECTRÓNICOS:
                    </b>
                </td>
            </tr>
            <tr>    
                <td style="text-align: center; padding: 5px; background-color: lightgrey; border-right: solid grey;" colspan="1" rowspan="2">
                    <b>
                        Factura
                    </b>
                </td>
                <td style="text-align: center; padding: 5px;" colspan="1">
                    <b>
                        <div style="float: left; width: 50%; text-align: right;">
                            SI&nbsp;
                        </div>
                        <div style="float: left; margin-left: 50%; text-align: left;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"></div>
                        </div>
                    </b>
                </td>
                <td style="text-align: center; padding: 5px; background-color: lightgrey; border-right: solid grey; border-left: solid grey;" colspan="2" rowspan="2">
                    <b>
                        Carta de Derechos Mínimos
                    </b>
                </td>
                <td style="text-align: center; padding: 5px;" colspan="1">
                    <b>
                        <div style="float: left; width: 50%; text-align: right;">
                            SI&nbsp;
                        </div>
                        <div style="float: left; margin-left: 50%; text-align: left;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"><b>X</b></div>
                        </div>
                    </b>
                </td>
                <td style="text-align: center; padding: 5px; background-color: lightgrey; border-right: solid grey; border-left: solid grey;" colspan="2" rowspan="2">
                    <b>
                        Contrato de Adhesión
                    </b>
                </td>
                <td style="text-align: center; padding: 5px;" colspan="1">
                    <b>
                        <div style="float: left; width: 50%; text-align: right;">
                            SI&nbsp;
                        </div>
                        <div style="float: left; margin-left: 50%; text-align: left;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"><b>X</b></div>
                        </div>
                    </b>
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 5px;" colspan="1">
                    <b>
                        <div style="float: left; width: 50%; text-align: right;">
                            NO&nbsp;
                        </div>
                        <div style="float: left; margin-left: 50%; text-align: left;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"><b>X</b></div>
                        </div>
                    </b>
                </td>

                <td style="text-align: center; padding: 5px;" colspan="1">
                    <b>
                        <div style="float: left; width: 50%; text-align: right;">
                            NO&nbsp;
                        </div>
                        <div style="float: left; margin-left: 50%; text-align: left;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"></div>
                        </div>
                    </b>
                </td>
                <td style="text-align: center; padding: 5px;" colspan="1">
                    <b>
                        <div style="float: left; width: 50%; text-align: right;">
                            NO&nbsp;
                        </div>
                        <div style="float: left; margin-left: 50%; text-align: left;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"></div>
                        </div>
                    </b>
                    <br>
                </td>
            </tr>
        </table>
        <table style="border: solid grey; border-top: none; width: 100%;" cellpadding="0" cellspacing="0">

            <tr>
                <td style="background-color: lightgrey; text-align: center; padding: 5px; border-right: solid grey;" colspan="3">
                    <b>
                        MEDIO ELECTRÓNICO AUTORIZADO:
                    </b>
                </td>

                <td style="padding: 5px;" colspan="5">
                    <b>
                        netwey.com.mx&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </b>
                </td>
            </tr>
        </table>
        <br>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">

            <tr>
                <td style="background-color: lightgrey; text-align: center; padding: 5px; border-bottom: solid grey;" colspan="8">
                    <b>
                        AUTORIZACIÓN PARA USO DE INFORMACIÓN DEL SUSCRIPTOR
                    </b>
                </td>
            </tr>
            <tr>    
                <td style="padding: 5px;" colspan="8">
                    <p style="margin-top: -10%;">
                        <div style="float: left; width: 13%;">
                            1.   &nbsp;&nbsp;El Suscriptor 
                        </div>
                        <div style="float: left; width: 3%;">
                            SI&nbsp;
                        </div>
                        <div style="float: left; width: 3%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"><b>X</b></div>
                        </div>
                        <div style="float: left; width: 3%; margin-left: 1%;">
                            NO&nbsp;
                        </div>
                        <div style="float: left; width: 3%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"></div>
                        </div>
                        <div style="margin-left: 27%;">
                            autoriza que su información sea transmitida por el Proveedor a terceros con fines mercadotécnicos
                        </div>
                        <div style="float: left; width:15%; margin-top: 0.7%; margin-left: -25%;">
                            o publicitarios.
                        </div>
                    </p>
                </td>
            </tr>
            <tr>    
                <td style="padding: 5px;" colspan="8">
                    <p>
                        <div style="float: left; width: 13%;">
                            2.   &nbsp;&nbsp;El Suscriptor 
                        </div>
                        <div style="float: left; width: 3%;">
                            SI&nbsp;
                        </div>
                        <div style="float: left; width: 3%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"><b>X</b></div>
                        </div>
                        <div style="float: left; width: 3%; margin-left: 1%;">
                            NO&nbsp;
                        </div>
                        <div style="float: left; width: 3%;">
                            <div style="border: solid black; text-align: center; width: 20px; height: 10px;"></div>
                        </div>
                        <div style="margin-left: 27%;">
                            acepta recibir llamadas del proveedor de promociones de servicios o paquetes. 
                        </div>
                    </p>
                </td>
            </tr>
        </table>

        <br>
        <table style="border: solid grey; width: 100%;" cellpadding="0" cellspacing="0">

            <tr>
                <td style="background-color: lightgrey; text-align: center; padding: 5px; border-bottom: solid grey;" colspan="8">
                    <b>
                        LA PRESENTE CARÁTULA Y EL CONTRATO DE ADHESIÓN SE ENCUENTRAN DISPONIBLES EN:
                    </b>
                </td>
            </tr>
            <tr>
                <td style="background-color: lightgrey; padding: 5px; border-bottom: solid grey; border-right: solid grey;" colspan="3">
                    1.  &nbsp;&nbsp;La página del proveedor
                </td>

                <td style="padding: 5px; border-bottom: solid grey;" colspan="5">
                    www.netwey.com.mx
                </td>
            </tr>
            <tr>
                <td style="background-color: lightgrey; padding: 5px; border-bottom: solid grey; border-right: solid grey;" colspan="3">
                    2.  &nbsp;&nbsp;Buró comercial de PROFECO
                </td>

                <td style="padding: 5px; border-bottom: solid grey;" colspan="5">
                    https://burocomercial.profeco.gob.mx/
                </td>
            </tr>
            <tr>
                <td style="background-color: lightgrey; padding: 5px; border-right: solid grey;" colspan="3">
                    3.  &nbsp;&nbsp;Físicamente en los centros de <br>  atención del proveedor
                </td>

                <td style="padding: 5px;" colspan="5">
                    Consultar centros de atención a clientes en www.netwey.com.mx
                </td>
            </tr>
        </table>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <table style="width: 100%;">
            <tr>
                <td  colspan="3" style="text-align: center;">
                    Ciudad de {{ $data['city'] }}, a {{ $data['now']->day }} de {{ ucwords($data['now']->monthName) }} de {{ $data['now']->year }}.
                </td>
            </tr>
        </table>
        <br><br><br>

        <table style="width: 100%;">
            <tr>
                <td  colspan="1" style="text-decoration-line: underline; text-align: center;">
                    Aceptación Electronica
                </td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td  colspan="1" style="text-decoration-line: underline; text-align: center;">
                    Aceptación Electronica
                </td>
            </tr>
            <tr>
                <td  colspan="1" style="text-align: center;">
                    <b style="border-top: solid black;">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Netwey&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </b>
                </td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td  colspan="1" style="text-align: center;">
                    <b style="border-top: solid black;">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SUSCRIPTOR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </b>
                </td>
            </tr>
        </table>

        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <table style="border: solid lightgrey; width: 100%; padding: 1px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="border-right: solid lightgrey;">
                    <p style="color: grey; padding-left: 5px;">
                        <b>ISLIM TELCO, S.A.P.I. DE C.V. </b><br>
                        Javier Barros Sierra 495, Piso 2, Oficina 110 <br> 
                        Santa Fe Centro Ciudad, Alcaldía Álvaro Obregón <br>
                        C.P. 01376, Ciudad de México <br>
                        RFC: ITE180215P68
                    </p>
                </td>
                <td style="width: 35%;">
                    <center>
                        <img src="{{public_path('images/image4.png')}}" style="opacity: 0.4; height: 48px; width: 218px;">
                    </center>
                </td>
            </tr>
        </table>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <table style="width: 100%; padding: 1px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="text-align: center; padding: 5px;">
                    <b>
                        PAGARÉ
                    </b>
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 5px;">
                    <b>
                        NO NEGOCIABLE
                    </b>
                </td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 5px;">
                    <b>
                        $ 1000.00 (un mil pesos 00/00 moneda nacional)
                    </b>
                </td>
            </tr>
            <tr>
                <td style="text-align: justify; padding: 5px;">
                    <p>
                        Por valor recibido, el suscrito {{ $data['client_name'] }} {{ $data['client_lname'] }} (el “Emisor”) por este pagaré (el “Pagaré”) promete incondicionalmente pagar a la orden de Islim Telco, S.A.P.I. de C.V., (el “Tenedor”) con domicilio en Javier Barros Sierra 495, Piso 2, Oficina 110, Santa Fe Centro Ciudad, Alcaldía Álvaro Obregón, C.P. 01376, Ciudad de México, la suma principal de $1000.00 (un mil pesos 00/100 moneda nacional) pagadera el día {{ $data['now']->day }} de {{ ucwords($data['now']->monthName) }} de {{ $data['now']->year }} (la “Fecha de Pago”).
                        Este Pagaré se gira e interpretará de acuerdo con las leyes de los Estados Unidos Mexicanos. Cualquier acción o procedimiento legal que surja de o se relacione con el Pagaré será instituido en los tribunales competentes en la Ciudad de México, renunciando el Emisor a cualquier otra jurisdicción de cualesquiera otros tribunales que pudiere corresponderle por razón de su domicilio presente o futuro.
                        El Emisor renuncia en este acto a toda diligencia, demanda protesto, presentación, notificación de no aceptación y cualquiera notificación o demanda de cualquier naturaleza.
                        EN VIRTUD DE LO CUAL, el Emisor ha firmado esta Pagaré en la fecha abajo mencionada.
                        Ciudad de México, a {{ $data['now']->day }} de {{ ucwords($data['now']->monthName) }} de {{ $data['now']->year }}.

                    </p>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">
                    <b>
                        El Suscriptor
                    </b>
                </td>
            </tr>
        </table>
        <br>
        <table style="width: 100%; padding: 1px;" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="1" style="padding: 5px; width: 50%;">
                    <b>
                        Nombre:
                    </b>
                </td>
                <td colspan="1" style="padding: 5px;">
                    <b>
                        Firma:
                    </b>
                </td>
            </tr>
            <tr>
                <td colspan="1" style="padding: 5px;">
                    {{ $data['client_name'] }} {{ $data['client_lname'] }}
                </td>
                <td colspan="1" style="padding: 5px;">
                    <b>
                        SE AUTORIZÓ POR FIRMA ELECTRÓNICA
                    </b>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px;">
                    <b>
                        Domicilio:
                    </b>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 5px;">
                    {{ $data['address'] }}
                </td>
            </tr>
            <tr>
                <td colspan="1" style="padding: 5px;">
                    <b>
                        Identificación:
                    </b>
                </td>
                <td colspan="1" style="padding: 5px;">
                    <b>
                        Teléfono(s):
                    </b>
                </td>
            </tr>
            <tr>
                <td colspan="1" style="padding: 5px;">
                    ({{ $data['doc_type'] }}): {{ $data['doc_id'] }}
                </td>
                <td colspan="1" style="padding: 5px;">
                    {{ $data['client_phonehome'] }}
                    @if($data['client_phone']) <br> {{ $data['client_phone'] }} @endif
                </td>
            </tr>
        </table>
    </center>
</body>