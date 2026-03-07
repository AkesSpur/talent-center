<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Diploma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DiplomaController extends Controller
{
    /**
     * GET /dashboard/diplomas
     * Participant's diploma list (own + children's diplomas).
     */
    public function index(Request $request): View
    {
        $user    = $request->user();
        $userIds = $user->children()->pluck('id')->prepend($user->id);

        $diplomas = Diploma::with(['contest.organization', 'application.category', 'user'])
            ->whereIn('user_id', $userIds)
            ->where('is_preview', false)
            ->latest()
            ->paginate(20);

        return view('dashboard.diplomas', compact('diplomas'));
    }

    /**
     * GET /diplomas/{diploma}/download
     * Stream the PDF diploma to the browser.
     */
    public function download(Request $request, Diploma $diploma): StreamedResponse|RedirectResponse
    {
        $user = $request->user();

        $diploma->loadMissing(['user', 'contest.organization', 'application.category']);

        $isJury = $diploma->contest?->organization
            ?->representatives()
            ->where('users.id', $user->id)
            ->wherePivot('can_evaluate', true)
            ->exists() ?? false;

        $allowed = $user->isAdmin()
            || $diploma->user_id === $user->id
            || ($diploma->user && $diploma->user->parent_id === $user->id)
            || $isJury;

        if (! $allowed) {
            abort(403);
        }

        if (! $diploma->file_path || ! Storage::disk('public')->exists($diploma->file_path)) {
            return redirect()->back()->with('error', 'Файл диплома не найден.');
        }

        $parts = array_filter([
            'Диплом',
            $diploma->user?->last_name,
            $diploma->user?->first_name,
            $diploma->application?->status?->label(),
            $diploma->application?->category?->name,
            $diploma->contest?->evaluation_end_at?->format('Y'),
        ]);

        $filename = implode('_', $parts) . '.pdf';

        return Storage::disk('public')->download($diploma->file_path, $filename);
    }
}
