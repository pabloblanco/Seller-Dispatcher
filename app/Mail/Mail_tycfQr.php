<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mail_tycfQr extends Mailable
{
  use Queueable, SerializesModels;
  public $DataBody;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct($data = null)
  {
    $this->DataBody = $data;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->subject($this->DataBody['asunto'])->view('mails.mailTycFQr');
  }
}
