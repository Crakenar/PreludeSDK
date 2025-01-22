<?php
namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

//class TestCase extends BaseTestCase
class TestCase extends OrchestraTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Delete all the models contained into the collections.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
