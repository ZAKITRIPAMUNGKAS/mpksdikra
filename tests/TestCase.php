<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test tidak boleh menyentuh layanan luar (terutama WA Gateway): request yang tidak di-fake akan gagal.
        Http::preventStrayRequests();
    }
}
