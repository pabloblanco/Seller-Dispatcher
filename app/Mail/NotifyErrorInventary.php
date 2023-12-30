<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyErrorInventary extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $dataDelivery;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($dataDelivery, $data = null)
    {
        $this->dataDelivery = $dataDelivery;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reporte de error en inventario')->view('mails.notifyErrorInventary');
    }
}
