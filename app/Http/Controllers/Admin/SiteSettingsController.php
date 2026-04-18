<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SiteSettingsController extends Controller
{
    public function index(): View
    {
        $settings = SiteSettings::allAsArray();

        return view('admin.settings.index', compact('settings'));
    }

    public function updateContacts(Request $request): RedirectResponse
    {
        $request->validate([
            'contact_phone'   => ['nullable', 'string', 'max:50'],
            'contact_email'   => ['nullable', 'email', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:255'],
        ], [
            'contact_email.email' => 'Введите корректный адрес электронной почты.',
        ]);

        SiteSettings::set(SiteSettings::CONTACT_PHONE,   $request->input('contact_phone'));
        SiteSettings::set(SiteSettings::CONTACT_EMAIL,   $request->input('contact_email'));
        SiteSettings::set(SiteSettings::CONTACT_ADDRESS, $request->input('contact_address'));

        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'contacts'])
            ->with('status', 'contacts-saved');
    }

    public function updatePrivacyPolicy(Request $request): RedirectResponse
    {
        $request->validate([
            'privacy_policy' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'privacy_policy.required' => 'Выберите PDF-файл.',
            'privacy_policy.mimes'    => 'Файл должен быть в формате PDF.',
            'privacy_policy.max'      => 'Файл не должен превышать 10 МБ.',
        ]);

        $existing = SiteSettings::get(SiteSettings::PRIVACY_POLICY);
        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        $path = $request->file('privacy_policy')->store('site', 'public');

        SiteSettings::set(SiteSettings::PRIVACY_POLICY, $path);
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'privacy'])
            ->with('status', 'privacy-policy-saved');
    }

    public function deletePrivacyPolicy(): RedirectResponse
    {
        $existing = SiteSettings::get(SiteSettings::PRIVACY_POLICY);
        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        SiteSettings::set(SiteSettings::PRIVACY_POLICY, null);
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'privacy'])
            ->with('status', 'privacy-policy-deleted');
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'site_logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ], [
            'site_logo.required' => 'Выберите файл логотипа.',
            'site_logo.image'    => 'Файл должен быть изображением.',
            'site_logo.mimes'    => 'Допустимые форматы: JPG, PNG, WebP, SVG.',
            'site_logo.max'      => 'Файл не должен превышать 2 МБ.',
        ]);

        $existing = SiteSettings::get(SiteSettings::SITE_LOGO);
        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        $path = $request->file('site_logo')->store('site', 'public');

        SiteSettings::set(SiteSettings::SITE_LOGO, $path);
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'branding'])
            ->with('status', 'logo-saved');
    }

    public function deleteLogo(): RedirectResponse
    {
        $existing = SiteSettings::get(SiteSettings::SITE_LOGO);
        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        SiteSettings::set(SiteSettings::SITE_LOGO, null);
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'branding'])
            ->with('status', 'logo-deleted');
    }

    public function updateFavicon(Request $request): RedirectResponse
    {
        $request->validate([
            'site_favicon' => ['required', 'file', 'mimes:ico,png,jpg,jpeg,svg', 'max:512'],
        ], [
            'site_favicon.required' => 'Выберите файл фавикона.',
            'site_favicon.mimes'    => 'Допустимые форматы: ICO, PNG, JPG, SVG.',
            'site_favicon.max'      => 'Файл не должен превышать 512 КБ.',
        ]);

        $existing = SiteSettings::get(SiteSettings::SITE_FAVICON);
        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        $path = $request->file('site_favicon')->store('site', 'public');

        SiteSettings::set(SiteSettings::SITE_FAVICON, $path);
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'branding'])
            ->with('status', 'favicon-saved');
    }

    public function deleteFavicon(): RedirectResponse
    {
        $existing = SiteSettings::get(SiteSettings::SITE_FAVICON);
        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        SiteSettings::set(SiteSettings::SITE_FAVICON, null);
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'branding'])
            ->with('status', 'favicon-deleted');
    }

    public function updateBrandText(Request $request): RedirectResponse
    {
        $request->validate([
            'site_name'           => ['nullable', 'string', 'max:100'],
            'site_name_color'     => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'site_subtitle'       => ['nullable', 'string', 'max:200'],
            'site_subtitle_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'site_name_color.regex'     => 'Введите корректный HEX-цвет (например, #8B4513).',
            'site_subtitle_color.regex' => 'Введите корректный HEX-цвет (например, #9A8B7A).',
        ]);

        SiteSettings::set(SiteSettings::SITE_NAME,           $request->input('site_name') ?: null);
        SiteSettings::set(SiteSettings::SITE_NAME_COLOR,     $request->input('site_name_color') ?: null);
        SiteSettings::set(SiteSettings::SITE_SUBTITLE,       $request->input('site_subtitle') ?: null);
        SiteSettings::set(SiteSettings::SITE_SUBTITLE_COLOR, $request->input('site_subtitle_color') ?: null);

        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'branding'])
            ->with('status', 'brand-text-saved');
    }

    public function updateOfferDocument(Request $request): RedirectResponse
    {
        $request->validate([
            'offer_document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'offer_document.required' => 'Выберите PDF-файл.',
            'offer_document.mimes'    => 'Файл должен быть в формате PDF.',
            'offer_document.max'      => 'Файл не должен превышать 10 МБ.',
        ]);

        $existing = SiteSettings::get(SiteSettings::OFFER_DOCUMENT);
        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        $path = $request->file('offer_document')->store('site', 'public');

        SiteSettings::set(SiteSettings::OFFER_DOCUMENT, $path);
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'privacy'])
            ->with('status', 'offer-document-saved');
    }

    public function deleteOfferDocument(): RedirectResponse
    {
        $existing = SiteSettings::get(SiteSettings::OFFER_DOCUMENT);
        if ($existing && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }

        SiteSettings::set(SiteSettings::OFFER_DOCUMENT, null);
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'privacy'])
            ->with('status', 'offer-document-deleted');
    }

    public function updateContestSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'default_application_limit' => ['required', 'integer', 'min:1'],
        ], [
            'default_application_limit.required' => 'Укажите лимит заявок.',
            'default_application_limit.integer'  => 'Лимит должен быть целым числом.',
            'default_application_limit.min'       => 'Минимальное значение: 1.',
        ]);

        SiteSettings::set(SiteSettings::DEFAULT_APPLICATION_LIMIT, (string) $request->input('default_application_limit'));
        $this->clearSettingsCache();

        return redirect()->route('admin.settings.index', ['tab' => 'app-settings'])
            ->with('status', 'contest-settings-saved');
    }

    public function privacyPolicy(): BinaryFileResponse
    {
        $path = SiteSettings::get(SiteSettings::PRIVACY_POLICY);

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'Политика конфиденциальности не загружена.');
        }

        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="privacy-policy.pdf"',
        ]);
    }

    private function clearSettingsCache(): void
    {
        cache()->forget('site_settings');
    }
}
