<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContestCover;
use App\Models\PlatformCategory;
use App\Traits\HandlesImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContestCoverController extends Controller
{
    use HandlesImages;

    public function index(): View
    {
        $covers             = ContestCover::with('platformCategories')->latest()->get();
        $platformCategories = PlatformCategory::active()->get();

        return view('admin.contest-covers.index', compact('covers', 'platformCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                    => ['nullable', 'string', 'max:255'],
            'image'                   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'platform_category_ids'   => ['nullable', 'array'],
            'platform_category_ids.*' => ['integer', 'exists:platform_categories,id'],
        ], [
            'image.required' => 'Загрузите изображение обложки.',
            'image.image'    => 'Файл должен быть изображением.',
            'image.mimes'    => 'Допустимые форматы: JPG, PNG, WebP.',
            'image.max'      => 'Файл не должен превышать 4 МБ.',
        ]);

        $imagePath = $this->storeImageAsWebp(
            $request->file('image'),
            'contest-covers',
            1200,
            85
        );

        $cover = ContestCover::create([
            'name'       => $request->input('name'),
            'image_path' => $imagePath,
        ]);

        if ($request->filled('platform_category_ids')) {
            $cover->platformCategories()->sync($request->input('platform_category_ids'));
        }

        return redirect()->route('admin.contest-covers.index')
            ->with('status', 'contest-cover-created');
    }

    public function update(Request $request, ContestCover $contestCover): RedirectResponse
    {
        $request->validate([
            'name'                    => ['nullable', 'string', 'max:255'],
            'image'                   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'platform_category_ids'   => ['nullable', 'array'],
            'platform_category_ids.*' => ['integer', 'exists:platform_categories,id'],
        ]);

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($contestCover->image_path);
            $contestCover->image_path = $this->storeImageAsWebp(
                $request->file('image'),
                'contest-covers',
                1200,
                85
            );
        }

        $contestCover->name = $request->input('name');
        $contestCover->save();

        $contestCover->platformCategories()->sync($request->input('platform_category_ids', []));

        return redirect()->route('admin.contest-covers.index')
            ->with('status', 'contest-cover-updated');
    }

    public function destroy(ContestCover $contestCover): RedirectResponse
    {
        $this->deleteStoredImage($contestCover->image_path);
        $contestCover->delete();

        return redirect()->route('admin.contest-covers.index')
            ->with('status', 'contest-cover-deleted');
    }
}
