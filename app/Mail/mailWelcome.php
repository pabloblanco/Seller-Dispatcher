<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class mailWelcome extends Mailable
{
    use Queueable, SerializesModels;
    public $dataBody;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data = null)
    {
        /*
        Se recibe un array en la varible $data con la informacion a ser adjuntada en el correo, los campos son:

        name = El nombre de la persona que compro
        lastname = El apellido de la persona que compro
        DN = Numero msisdn asignado
        phone1 = Telefono de contacto
        email = correo con el que se registro en netwey
        address = Direccion a la cual sera usado el modem

        Ejemplo:

        $infodata = array(
        'name'        => 'luis',
        'lastname'    => 'Perez',
        'dn'          => '1234567890',
        'phone1'      => '414123456789',
        'email'       => 'correo@dominio.com',
        'address'     => 'Av 1 sector rivera',
        );
         */
        $this->dataBody = $data;
        /*
    Para ejecutar se debe enviar el correo y la data del correo

    Ejemplo de ejecucion:

    $mailuser = 'luis@gdalab.com';
    try {
    Mail::to($mailuser)->send(new mailWelcome($infodata));
    } catch (\Exception $e) {}
     */
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Bienvenido a Netwey.')->view('mails.mailWelcome');

    }

}
