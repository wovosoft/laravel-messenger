<?php


namespace Wovosoft\LaravelMessenger\Http\Controllers;

use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
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
    }

    public function store(Request $request)
    {
        try {
            if ($item = Messages::send(auth()->id(), User::class, $request->post('send_to'), User::class, $request->post('message'), true)) {
                $item->sender = auth()->user();
                $item->receiver = User::find($request->post('send_to'));
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
        return User::query()
            ->where('id', '!=', auth()->id())
            ->paginate($request->has('per_page') ? $request->post('per_page') : 20);
    }
}
