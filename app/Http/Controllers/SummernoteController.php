<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SummernoteController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $folder = 'newsletters/' . date('Y-m-d');

                // Generate unique filename
                $filename = Str::random(20) . '_' . time() . '.' . $file->getClientOriginalExtension();

                // Store the file to storage/app/public
                $path = $file->storeAs('storage/'.$folder, $filename, 'public');

                // Return correct URL for public access
                $url = asset('' . $path);

                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'path' => $path
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No image file provided'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $request->validate([
                'src' => 'required|string'
            ]);

            $src = $request->input('src');

            // Extract path from URL - remove the domain and /storage/ part
            $path = parse_url($src, PHP_URL_PATH);
            $path = str_replace('/storage/', '', $path);

            // Delete the file from storage
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);

                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
}