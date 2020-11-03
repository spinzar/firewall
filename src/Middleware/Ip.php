<?php

namespace Spinzar\Firewall\Middleware;

use Spinzar\Firewall\Abstracts\Middleware;
use Spinzar\Firewall\Models\Ip as Model;
use Illuminate\Database\QueryException;

class Ip extends Middleware
{
    public function check($patterns)
    {
        $status = false;

        try {
            $ip = config('firewall.models.ip', Model::class);
            $status = $ip::blocked($this->ip())->pluck('id')->first();
        } catch (QueryException $e) {
            // Base table or view not found
            //$status = ($e->getCode() == '42S02') ? false : true;
        }

        return $status;
    }
}
