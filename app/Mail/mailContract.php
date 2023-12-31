<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class mailContract extends Mailable
{
  use Queueable, SerializesModels;
  public $data;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct($data = null)
  {
    $this->data = $data;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->subject('Contrato de Adhesión.')->view('mails.mail_send_contract');
  }
}
