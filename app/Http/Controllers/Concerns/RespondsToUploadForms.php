<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Forms with attachments are sent by support-upload.js over XHR, so the page
 * can show upload progress and keep what the user typed when the server says
 * no. Such a request gets its destination back as JSON (the flash is set for
 * the page it opens next); a plain form post still gets an ordinary redirect.
 */
trait RespondsToUploadForms
{
    /** @param  array<string, mixed>  $flash */
    protected function uploadFormDone(Request $request, string $url, array $flash = []): RedirectResponse|JsonResponse
    {
        if (! $request->expectsJson()) {
            return redirect()->to($url)->with($flash);
        }

        foreach ($flash as $key => $value) {
            $request->session()->flash($key, $value);
        }

        return response()->json(['redirect' => $url]);
    }

    /** A refusal that isn't about the form's fields, e.g. the ticket was closed meanwhile. */
    protected function uploadFormRefused(Request $request, string $message): RedirectResponse|JsonResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message], 409)
            : back()->withInput()->with('error', $message);
    }
}
