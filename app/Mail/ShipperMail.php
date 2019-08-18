<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ShipperMail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $name;
    public $uuid;
    public $password;
    public $update;

    public function __construct($name, $uuid =null, $password = null, $update = false)
    {
        $this->name = $name;
        $this->uuid = $uuid;
        $this->password = $password;
        $this->update = $update;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('admin.elements.users.mail')->with(['name' => $this->name, 'uuid' => $this->uuid, 'password' => $this->password, 'update' => $this->update]);
    }
}
