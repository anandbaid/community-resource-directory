<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CkeditorUploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        return $this->uploadAsset(
            $request,
            'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'ckeditor/images'
        );
    }

    public function uploadFile(Request $request)
    {
        return $this->uploadAsset(
            $request,
            'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,rtf,odt,ods,odp,zip|max:10240',
            'ckeditor/files'
        );
    }

    private function uploadAsset(Request $request, string $uploadRule, string $path)
    {
        $validator = validator($request->all(), [
            'upload' => $uploadRule,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'uploaded' => false,
                'error' => ['message' => $validator->errors()->first('upload')],
            ], 422);
        }

        try {
            if (!$request->hasFile('upload')) {
                return response()->json([
                    'uploaded' => false,
                    'error' => ['message' => 'No file uploaded'],
                ], 400);
            }

            $file = $request->file('upload');
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'file');
            $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'ckeditor-upload';
            $fileName = $baseName . '-' . time() . '-' . uniqid() . '.' . $extension;
            $uploadedPath = $file->storeAs($path, $fileName, 'public');

            return response()->json([
                'uploaded' => true,
                'url' => asset('storage/' . trim($uploadedPath, '/')),
                'fileName' => $originalName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'uploaded' => false,
                'error' => ['message' => $e->getMessage()],
            ], 500);
        }
    }
}
