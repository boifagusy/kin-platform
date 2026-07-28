<?php

namespace App\Events\TrustedContact;

use App\Models\TrustedContact;
use Illuminate\Foundation\Events\Dispatchable;

class TrustedContactRequestAccepted
{
    use Dispatchable;

    public function __construct(
        public TrustedContact $contact,
        public int $acceptedByUserId
    ) {}
}
