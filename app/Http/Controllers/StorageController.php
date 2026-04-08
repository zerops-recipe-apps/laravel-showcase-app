<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();

        Storage::disk('s3')->put($filename, file_get_contents($file));

        return redirect()->back()->with('success', "File '{$filename}' uploaded successfully.");
    }

    public function destroy(string $filename)
    {
        Storage::disk('s3')->delete($filename);

        return redirect()->back()->with('success', "File '{$filename}' deleted successfully.");
    }
}
