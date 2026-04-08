<?php

namespace App\Http\Controllers;

use App\Jobs\TestJob;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function dispatch(Request $request)
    {
        TestJob::dispatch();

        return redirect()->back()->with('success', 'Test job dispatched to queue. It will process shortly.');
    }
}
