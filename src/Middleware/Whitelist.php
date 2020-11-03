<?php

namespace Spinzar\Firewall\Middleware;

use Spinzar\Firewall\Abstracts\Middleware;

class Whitelist extends Middleware
{
    public function check($patterns)
    {
        return ($this->isWhitelist() === false);
    }
}
