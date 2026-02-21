<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Traits\HandlesImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use HandlesImages;

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'participants' => $user->children()->orderBy('last_name')->get(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp,bmp', 'max:4096'],
        ], [
            'avatar.required' => 'Выберите изображение.',
            'avatar.image' => 'Файл должен быть изображением.',
            'avatar.mimes' => 'Допустимые форматы: JPEG, PNG, GIF, WebP, BMP.',
            'avatar.max' => 'Максимальный размер файла — 4 МБ.',
        ]);

        $user = $request->user();

        // Delete old avatar
        $this->deleteStoredImage($user->avatar_path);

        // Store new avatar as WebP (max 512px wide)
        $path = $this->storeImageAsWebp($request->file('avatar'), 'avatars', 512);

        $user->update(['avatar_path' => $path]);

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }

    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->deleteStoredImage($user->avatar_path);
        $user->update(['avatar_path' => null]);

        return Redirect::route('profile.edit')->with('status', 'avatar-deleted');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Clean up avatar
        $this->deleteStoredImage($user->avatar_path);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
