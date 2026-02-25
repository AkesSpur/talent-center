<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformCategory;
use App\Services\ActionLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformCategoryController extends Controller
{
    public function index(): View
    {
        $categories = PlatformCategory::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.platform-categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = $request->boolean('is_active', true);

        $category = PlatformCategory::create($data);

        ActionLogService::log('platform_category.created', $category, ['name' => $category->name]);

        return redirect()->route('admin.platform-categories.index')
            ->with('status', 'platform-category-created');
    }

    public function update(Request $request, PlatformCategory $platformCategory): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = $request->boolean('is_active', true);

        $platformCategory->update($data);

        ActionLogService::log('platform_category.updated', $platformCategory, ['name' => $platformCategory->name]);

        return redirect()->route('admin.platform-categories.index')
            ->with('status', 'platform-category-updated');
    }

    public function destroy(PlatformCategory $platformCategory): RedirectResponse
    {
        $contestsCount = $platformCategory->contests()->count();

        if ($contestsCount > 0) {
            return redirect()->route('admin.platform-categories.index')
                ->with('error', "Невозможно удалить категорию: к ней привязано {$contestsCount} конкурс(ов). Сначала переназначьте их.");
        }

        ActionLogService::log('platform_category.deleted', null, ['name' => $platformCategory->name]);

        $platformCategory->delete();

        return redirect()->route('admin.platform-categories.index')
            ->with('status', 'platform-category-deleted');
    }
}
