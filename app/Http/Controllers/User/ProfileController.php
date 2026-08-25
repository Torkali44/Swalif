<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Support\MediaStore;
use App\Support\PublicMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'character',
            'subscriptions' => fn ($q) => $q->latest()->with('plan'),
        ]);

        $characters = Character::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name_ar', 'name_en', 'slug', 'image', 'icon', 'accent_color', 'is_active', 'sort_order']);

        return view('user.profile', compact('user', 'characters'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'character_id' => [
                'nullable',
                'integer',
                Rule::exists('characters', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'clear_character' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'phone_code' => $data['phone_code'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
        ];

        $wantsPhoto = $request->hasFile('avatar');
        $characterId = $data['character_id'] ?? null;
        $clearCharacter = $request->boolean('clear_character');

        if ($wantsPhoto) {
            // Uploaded photo replaces character identity
            if ($user->avatar && Storage::disk(PublicMedia::DISK)->exists($user->avatar)) {
                Storage::disk(PublicMedia::DISK)->delete($user->avatar);
            }
            $payload['avatar'] = MediaStore::store($request->file('avatar'), 'avatars', 400);
            $payload['character_id'] = null;
        } elseif ($clearCharacter) {
            $payload['character_id'] = null;
        } elseif ($characterId) {
            // Selecting a character becomes the displayed identity (drop old photo)
            if ($user->avatar && Storage::disk(PublicMedia::DISK)->exists($user->avatar)) {
                Storage::disk(PublicMedia::DISK)->delete($user->avatar);
            }
            $payload['avatar'] = null;
            $payload['character_id'] = (int) $characterId;
        }

        $user->update($payload);

        return back()->with('success', 'تم حفظ التغييرات بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }
}
