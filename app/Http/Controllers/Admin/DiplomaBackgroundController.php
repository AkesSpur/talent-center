<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiplomaBackground;
use App\Models\PlatformCategory;
use App\Traits\HandlesImages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DiplomaBackgroundController extends Controller
{
    use HandlesImages;

    public function index(): View
    {
        $backgrounds = DiplomaBackground::with('platformCategories')->latest()->get();
        $platformCategories = PlatformCategory::active()->get();

        return view('admin.diploma-backgrounds.index', compact('backgrounds', 'platformCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                  => ['nullable', 'string', 'max:255'],
            'image'                 => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'platform_category_ids' => ['nullable', 'array'],
            'platform_category_ids.*' => ['integer', 'exists:platform_categories,id'],
        ], [
            'image.required' => 'Загрузите изображение фона.',
            'image.image'    => 'Файл должен быть изображением.',
            'image.mimes'    => 'Допустимые форматы: JPG, PNG, WebP.',
            'image.max'      => 'Файл не должен превышать 4 МБ.',
        ]);

        $imagePath = $this->storeImageAsWebp(
            $request->file('image'),
            'diploma-backgrounds',
            1920,
            90
        );

        $bg = DiplomaBackground::create([
            'name'       => $request->input('name'),
            'image_path' => $imagePath,
        ]);

        if ($request->filled('platform_category_ids')) {
            $bg->platformCategories()->sync($request->input('platform_category_ids'));
        }

        return redirect()->route('admin.diploma-backgrounds.index')
            ->with('status', 'diploma-background-created');
    }

    public function update(Request $request, DiplomaBackground $diplomaBackground): RedirectResponse
    {
        $request->validate([
            'name'                  => ['nullable', 'string', 'max:255'],
            'image'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'platform_category_ids' => ['nullable', 'array'],
            'platform_category_ids.*' => ['integer', 'exists:platform_categories,id'],
        ]);

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($diplomaBackground->image_path);
            $diplomaBackground->image_path = $this->storeImageAsWebp(
                $request->file('image'),
                'diploma-backgrounds',
                1920,
                90
            );
        }

        $diplomaBackground->name = $request->input('name');
        $diplomaBackground->save();

        $diplomaBackground->platformCategories()->sync($request->input('platform_category_ids', []));

        return redirect()->route('admin.diploma-backgrounds.index')
            ->with('status', 'diploma-background-updated');
    }

    public function destroy(DiplomaBackground $diplomaBackground): RedirectResponse
    {
        $this->deleteStoredImage($diplomaBackground->image_path);
        $diplomaBackground->delete();

        return redirect()->route('admin.diploma-backgrounds.index')
            ->with('status', 'diploma-background-deleted');
    }
}
