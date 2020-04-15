<?php

namespace Wovosoft\LaravelMessenger;

use App\User;
use Illuminate\Support\Facades\DB;
use Wovosoft\LaravelMessenger\Models\Messages;

class LaravelMessenger
{
    /**
     * The primary purpose of the method is to return only the list of contacts and messages,
     * associated with a certain Model (Basically User). It will return only
     * that users, with whom the User made conversation.
     * Other calling functions/methods should utilize the returned data according to their
     * needs.
     * @param int $first_id First Model's ID
     * @param int $second_id Second Model's ID
     * @param bool $paginate It decides the return type, whether the returned data should be an collection
     * of all messages or paginated.
     * @param int $perPage
     * @param string $first_type First Model's class, eg, App\User::class
     * @param string $second_type Second Model's class, eg, App\User::class
     * @param array $with By default, Messages support two related Models, sender and receiver.
     * By default both are returned. But you can customize it using this param.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function conversation(int $first_id, int $second_id, bool $paginate = false, int $perPage = 15, string $first_type = User::class, string $second_type = User::class, array $with = ["sender", "receiver"])
    {
        $messages = Messages::query()
            ->where(function ($q) use ($first_id, $first_type, $second_id, $second_type) {
                $q
                    ->where('sender_id', $first_id)
                    ->where('sender_type', $first_type)
                    ->where('receiver_id', $second_id)
                    ->where('receiver_type', $second_type);
            })
            ->orWhere(function ($q) use ($first_id, $first_type, $second_id, $second_type) {
                $q
                    ->where('sender_id', $second_id)
                    ->where('sender_type', $second_type)
                    ->where('receiver_id', $first_id)
                    ->where('receiver_type', $first_type);
            })
            ->latest()
            ->with($with);
        if ($paginate) {
            return $messages->paginate($perPage);
        }
        return $messages->get();
    }

    /**
     * @param int $sender
     * @param string $sender_type
     * @param int $receiver
     * @param string $receiver_type
     * @param string|null $message
     * @param bool $getModel
     * @return bool | mixed
     * @throws \Throwable | \Exception
     */
    public function send(int $sender, string $sender_type, int $receiver, string $receiver_type, string $message, bool $getModel = false)
    {
        try {
            $item = new Messages;
            $item->sender_id = $sender;
            $item->sender_type = $sender_type;
            $item->receiver_id = $receiver;
            $item->receiver_type = $receiver_type;
            $item->message = $message;
            if ($item->saveOrFail()) {
                return $getModel ? $item : true;
            }
            return false;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function contacts()
    {
        return config("laravel-messenger.contacts_from")::query();
    }

    public function conversionByModel($sender, $receiver)
    {
        $messages = $sender->messages;
    }
}
