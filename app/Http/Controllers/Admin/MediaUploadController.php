<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MediaStore;
use App\Support\PublicMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MediaUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $kind = (string) $request->input('kind', 'image');

        // Prefer extensions over mimes: MIME sniffing rejects many valid phone/WhatsApp videos.
        $fileRules = match ($kind) {
            'video' => ['required', 'file', 'extensions:mp4,webm,mov,avi,m4v', 'max:51200'],
            'audio' => ['required', 'file', 'extensions:mp3,wav,ogg,m4a,aac', 'max:20480'],
            'answer_image' => ['required', 'image', 'max:5120'],
            default => ['required', 'image', 'max:4096'],
        };

        $data = $request->validate([
            'kind' => ['required', Rule::in(['image', 'video', 'audio', 'answer_image'])],
            'file' => $fileRules,
        ], [
            'file.required' => 'اختر ملفًا للرفع.',
            'file.extensions' => match ($kind) {
                'video' => 'صيغة الفيديو غير مدعومة. استخدم mp4 أو webm أو mov.',
                'audio' => 'صيغة الصوت غير مدعومة. استخدم mp3 أو wav أو ogg.',
                default => 'صيغة الملف غير مدعومة.',
            },
            'file.mimes' => 'صيغة الملف غير مدعومة.',
            'file.max' => 'حجم الملف كبير جدًا.',
            'file.image' => 'الملف يجب أن يكون صورة.',
        ]);

        $folder = match ($data['kind']) {
            'video' => 'questions/videos',
            'audio' => 'questions/audio',
            default => 'questions',
        };

        $maxWidth = match ($data['kind']) {
            'answer_image' => 1200,
            'image' => 1400,
            default => 1200,
        };

        $path = MediaStore::store($request->file('file'), $folder, $maxWidth);

        return response()->json([
            'path' => $path,
            'url' => PublicMedia::url($path),
        ]);
    }
}
