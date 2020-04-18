<?php

namespace Wovosoft\LaravelMessenger\Events;

use App\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Wovosoft\LaravelMessenger\Models\Messages;

class MyEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message, $user;

    public function __construct(User $user, Messages $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("my-channel");
    }

    public function broadcastAs()
    {
        return 'NewMessage';
    }
}
