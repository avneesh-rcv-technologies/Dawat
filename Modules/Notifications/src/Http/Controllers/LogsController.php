<?php

namespace Modules\Notifications\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Notifications\DataView\LogsGrid;
use Modules\Notifications\Models\NotificationLog;

class LogsController extends Controller
{
    public function index()
    {   
        $lists = fn_datagrid(LogsGrid::class)->process();
        return view('notifications::logs.index', compact('lists'));
     
    }

    public function show(Request $request, $id) {
        try {
            $log = NotificationLog::findOrFail($id);
            return view('notifications::logs.show', compact('log'));
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

}