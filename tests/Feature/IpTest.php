<?php

namespace Spinzar\Firewall\Tests\Feature;

use Spinzar\Firewall\Middleware\Ip;
use Spinzar\Firewall\Models\Ip as Model;
use Spinzar\Firewall\Tests\TestCase;

class IpTest extends TestCase
{
    public function testShouldAllow()
    {
        $this->assertEquals('next', (new Ip())->handle($this->app->request, $this->getNextClosure()));
    }

    public function testShouldBlock()
    {
        Model::create(['ip' => '127.0.0.1', 'log_id' => 1]);

        $this->assertEquals('403', (new Ip())->handle($this->app->request, $this->getNextClosure())->getStatusCode());
    }
}
