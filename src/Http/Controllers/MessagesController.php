<?php


namespace Wovosoft\LaravelMessenger\Http\Controllers;

use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Wovosoft\LaravelMessenger\Events\MyEvent;
use Wovosoft\LaravelMessenger\Models\Messages as ItemModel;
use Wovosoft\LaravelMessenger\Facades\LaravelMessenger as Messages;

class MessagesController extends Controller
{
    public static function routes()
    {
        Route::post("LaravelMessenger/inboxContacts", '\\' . __CLASS__ . '@inboxContacts')->name('LaravelMessenger.InboxContacts');
        Route::post("LaravelMessenger/conversation", '\\' . __CLASS__ . '@conversation')->name('LaravelMessenger.conversation');
        Route::post("LaravelMessenger/search", '\\' . __CLASS__ . '@search')->name('LaravelMessenger.Search');
        Route::post("LaravelMessenger/store", '\\' . __CLASS__ . '@store')->name('LaravelMessenger.Store');
        Route::post("LaravelMessenger/delete", '\\' . __CLASS__ . '@delete')->name('LaravelMessenger.Delete');
        Route::post("LaravelMessenger/getUser", '\\' . __CLASS__ . '@getUser')->name('LaravelMessenger.getUser');
        Route::get("ttt", '\\' . __CLASS__ . '@ttt');
    }

    public function ttt()
    {
        $contacts = ItemModel::query()
            ->where('sender_id', auth()->id())
            ->orWhere('receiver_id', auth()->id())
            ->selectRaw("(CASE WHEN sender_id=? THEN receiver_id ELSE sender_id END) as id", [auth()->id()])
            ->latest()
            ->get()
            ->unique('id')
            ->pluck('id');

        $order = implode($contacts->toArray());

        return User::query()
            ->whereIn('id', $contacts);
    }

    public function store(Request $request)
    {
//        Messages::where('sender_id',1)
//            ->orWhere('receiver_id',1)
//            ->select('sender_id','receiver_id',DB::raw("CASE(WHEN sender_id=1 THEN sender_id ELSE receiver_id END) as tt"))
//            ->get()
//            ->toArray()

        try {
            if ($item = Messages::send(auth()->id(), User::class, $request->post('send_to'), User::class, $request->post('message'), true)) {
                $item->sender = auth()->user();
                $item->receiver = User::find($request->post('send_to'));
                broadcast(new MyEvent(auth()->user(), \Wovosoft\LaravelMessenger\Models\Messages::query()->with(['sender', 'receiver'])->find($item->id)))
                    ->toOthers();
                return response()->json([
                    "status" => true,
                    "title" => 'SUCCESS!',
                    "type" => "success",
                    "data" => $item,
                    "msg" => 'Sent Successfully'
                ]);
            }
            return response()->json([
                "status" => false,
                "title" => 'FAILED!',
                "type" => "warning",
                "msg" => 'Operation Failed.'
            ]);
        } catch (\Exception $exception) {
            if (env('APP_DEBUG')) {
                return response()->json([
                    "code" => $exception->getCode(),
                    "status" => false,
                    "title" => 'Failed!',
                    "type" => "warning",
                    "msg" => $exception->getMessage(),
                    "line" => $exception->getLine(),
                    "file" => $exception->getFile(),
                    "trace" => $exception->getTrace(),
                ], Response::HTTP_FORBIDDEN, [], JSON_PRETTY_PRINT);
            }
            return response()->json([
                "status" => false,
                "title" => 'Failed!',
                "type" => "warning",
                "msg" => "Unable to Process the request"
            ], Response::HTTP_FORBIDDEN);
        }
    }

    public function conversation(Request $request)
    {
        $user = User::find($request->post('user_id'));
        return response()->json(Messages::conversation(auth()->id(), $request->post('user_id'), true))
            ->header("auth_id", auth()->id())
            ->header('user_name_email', "{$user->name} ({$user->email})");
    }


    public function inboxContacts(Request $request)
    {
        return Messages::contacts($request);
    }

    /**
     * This function can be implemented by
     * @param Request $request
     */
    public function getUser(Request $request)
    {
        try {
            return User::find($request->post("user_id"));
        } catch (\Exception $exception) {
            return response()->json([
                "msg" => $exception->getMessage()
            ], 404);
        }
    }
}




