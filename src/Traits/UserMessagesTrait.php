<?php

namespace Wovosoft\LaravelMessenger\Traits;

use Wovosoft\LaravelMessenger\Models\Messages;

trait UserMessagesTrait
{

    public function sentMessages()
    {
        return $this->morphMany(Messages::class, 'sender');
    }

    public function receivedMessages()
    {
        return $this->morphMany(Messages::class, 'receiver');
    }
}
