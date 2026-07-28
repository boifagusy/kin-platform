<?php

namespace App\Events\TrustedContact;

use App\Models\TrustedContact;
use Illuminate\Foundation\Events\Dispatchable;

class TrustedContactRequestDeclined
{
    use Dispatchable;

    public function __construct(
        public TrustedContact $contact,
        public int $declinedByUserId
    ) {}
}
