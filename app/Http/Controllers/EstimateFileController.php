<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\EstimateFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EstimateFileController extends Controller
{
    public function store(Request $request, Estimate $estimate)
    {
        $data = $request->validate([
            'file' => ['required','file','max:10240'],
        ]);
        $file = $data['file'];
        $disk = 'public';
        $path = $file->store('estimates/'.$estimate->id, $disk);

        $record = $estimate->files()->create([
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['file' => $record]);
        }
        return back()->with('success', 'File uploaded.');
    }

    public function destroy(Estimate $estimate, EstimateFile $file)
    {
        if ($file->estimate_id !== $estimate->id) {
            abort(404);
        }
        if ($file->disk && $file->path) {
            Storage::disk($file->disk)->delete($file->path);
        }
        $file->delete();
        return back()->with('success', 'File removed.');
    }
}
