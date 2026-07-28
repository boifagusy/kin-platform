<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Use production database for testing (migrations already applied)
    // This avoids the migration ordering issue (INFRA-001)
    protected $seed = false;
}
