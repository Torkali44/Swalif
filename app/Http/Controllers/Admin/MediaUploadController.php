<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AudioUpload;
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

        // Prefer extensions over mimes: MIME sniffing rejects many valid phone/WhatsApp videos/audio.
        $fileRules = match ($kind) {
            'video' => ['required', 'file', 'extensions:mp4,webm,mov,avi,m4v', 'max:51200'],
            'audio' => ['required', 'file', AudioUpload::extensionsRule(), 'max:'.AudioUpload::maxKilobytes()],
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
                'audio' => 'صيغة الصوت غير مدعومة. استخدم '.AudioUpload::humanFormats().'.',
                default => 'صيغة الملف غير مدعومة.',
            },
            'file.mimes' => 'صيغة الملف غير مدعومة.',
            'file.max' => match ($kind) {
                'audio' => 'حجم الملف الصوتي كبير جدًا (الحد '.((int) (AudioUpload::maxKilobytes() / 1024)).' ميجابايت).',
                'video' => 'حجم الفيديو كبير جدًا (الحد 50 ميجابايت).',
                default => 'حجم الملف كبير جدًا.',
            },
            'file.image' => 'الملف يجب أن يكون صورة.',
        ]);

        $folder = match ($data['kind']) {
            'video' => 'questions/videos',
            'audio' => 'questions/audio',
            default => (string) ($request->input('folder') ?: 'questions'),
        };

        $allowedFolders = ['questions', 'categories', 'classifications', 'characters', 'letter_grids', 'avatars'];
        if ($data['kind'] === 'image' || $data['kind'] === 'answer_image') {
            if (! in_array($folder, $allowedFolders, true)) {
                $folder = 'questions';
            }
        }

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
