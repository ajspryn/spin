<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired immediately (ShouldBroadcastNow) so it bypasses the queue
 * and is sent to the TV display without delay.
 */
class PrizeWon implements ShouldBroadcastNow
{
     use Dispatchable, InteractsWithSockets, SerializesModels;

     public function __construct(
          public readonly int    $prizeIndex,
          public readonly string $prizeName,
          public readonly string $visitorName,
          public readonly string $claimCode,
          public readonly bool   $isZonk = false,
     ) {}

     /**
      * Public channel — no authentication required for TV display.
      */
     public function broadcastOn(): array
     {
          return [
               new Channel('exhibition-channel'),
          ];
     }

     /**
      * Custom event name consumed by the front-end Echo listener.
      * Echo listens with .spin.completed (dot prefix means no namespace prepend).
      */
     public function broadcastAs(): string
     {
          return 'spin.completed';
     }

     /**
      * Payload sent to the TV display.
      */
     public function broadcastWith(): array
     {
          return [
               'prizeIndex'  => $this->prizeIndex,
               'prizeName'   => $this->prizeName,
               'visitorName' => $this->visitorName,
               'claimCode'   => $this->claimCode,
               'isZonk'      => $this->isZonk,
          ];
     }
}
