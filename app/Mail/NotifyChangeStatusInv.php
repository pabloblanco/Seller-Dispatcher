<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyChangeStatusInv extends Mailable
{
  use Queueable, SerializesModels;

  public $data;
  public $Infodn;
  public $InfoUser;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct($data, $Infodn, $InfoUser)
  {
    $this->data     = $data;
    $this->Infodn   = json_decode(json_encode($Infodn));
    $this->InfoUser = json_decode(json_encode($InfoUser));
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->subject('Notificacion de cambio de status de inventario.')->view('mails.notifyChangeStatusInventary')
      ->attach($this->data->url_evidencia);
  }
}
