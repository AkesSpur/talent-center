<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Diploma;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiplomaVerifyController extends Controller
{
    /**
     * GET /diplomvtrifi
     * Public search form.
     */
    public function index(): View
    {
        return view('diplomas.verify-search');
    }

    /**
     * POST /diplomvtrifi
     * Redirect to the diploma's public page.
     */
    public function find(Request $request): RedirectResponse
    {
        $request->validate([
            'diploma_number' => ['required', 'string', 'max:30'],
        ]);

        $number = $request->input('diploma_number');

        $exists = Diploma::where('diploma_number', $number)
            ->where('is_preview', false)
            ->exists();

        if (! $exists) {
            return redirect()->route('diplomvtrifi.search')
                ->with('error', "Диплом с номером «{$number}» не найден. Проверьте номер и попробуйте снова.")
                ->withInput();
        }

        return redirect()->route('diplomvtrifi.show', $number);
    }

    /**
     * GET /diplomvtrifi/{number}
     * Public diploma information page (QR code destination).
     */
    public function show(string $number): RedirectResponse|View
    {
        $diploma = Diploma::with(['user', 'contest.organization', 'application.category', 'application.ageGroup'])
            ->where('diploma_number', $number)
            ->where('is_preview', false)
            ->first();

        if (! $diploma) {
            return redirect()->route('diplomvtrifi.search')
                ->with('error', "Диплом с номером «{$number}» не найден. Проверьте номер и попробуйте снова.");
        }

        return view('diplomas.verify-show', compact('diploma'));
    }
}
