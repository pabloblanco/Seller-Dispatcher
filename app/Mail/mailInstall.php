<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class mailInstall extends Mailable
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
    $this->dataBody = $data;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->subject('Solicitud de instalación.')->view('mails.mail_install');
  }
}
