<?php

namespace App\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class ModuleEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $source;
    public $timestamp;

    public function __construct($data, $source = null)
    {
        $this->data = $data;
        $this->source = $source ?? 'system';
        $this->timestamp = now();
    }
}