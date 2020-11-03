<?php

namespace Spinzar\Firewall\Listeners;

use Spinzar\Firewall\Events\AttackDetected;
use Spinzar\Firewall\Models\Ip;
use Spinzar\Firewall\Models\Log;
use Carbon\Carbon;

class BlockIp
{
    /**
     * Handle the event.
     *
     * @param AttackDetected $event
     *
     * @return void
     */
    public function handle(AttackDetected $event)
    {
        $end = Carbon::now(config('app.timezone'));
        $start = $end->copy()->subSeconds(config('firewall.middleware.' . $event->log->middleware . '.auto_block.frequency'));

        $log = config('firewall.models.log', Log::class);
        $count = $log::where('ip', $event->log->ip)->whereBetween('created_at', [$start, $end])->count();

        if ($count != config('firewall.middleware.' . $event->log->middleware . '.auto_block.attempts')) {
            return;
        }

        $ip = config('firewall.models.ip', Ip::class);
        $ip::create([
            'ip' => $event->log->ip,
            'log_id' => $event->log->id,
        ]);
    }
}
