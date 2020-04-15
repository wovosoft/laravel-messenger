<?php

namespace Wovosoft\LaravelMessenger\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messages extends Model
{
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('laravel-messenger.table');
    }

    public function sender()
    {
        return $this->morphTo('sender', 'sender_type', 'sender_id');
    }

    public function receiver()
    {
        return $this->morphTo('receiver', 'receiver_type', 'receiver_id');
    }
}
