<?php
namespace App\Events\TrustedContact;
use App\Models\TrustedContact;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrustedContactRevoked
{
    use Dispatchable, SerializesModels;
    public function __construct(public TrustedContact $contact) {}
}
