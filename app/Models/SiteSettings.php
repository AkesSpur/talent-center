<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSettings extends Model
{
    protected $fillable = ['key', 'value'];

    // Setting keys
    const CONTACT_PHONE      = 'contact_phone';
    const CONTACT_EMAIL      = 'contact_email';
    const CONTACT_ADDRESS    = 'contact_address';
    const PRIVACY_POLICY     = 'privacy_policy_path';
    const SITE_LOGO          = 'site_logo_path';
    const SITE_FAVICON       = 'site_favicon_path';
    const SITE_NAME          = 'site_name';
    const SITE_NAME_COLOR    = 'site_name_color';
    const SITE_SUBTITLE      = 'site_subtitle';
    const SITE_SUBTITLE_COLOR        = 'site_subtitle_color';
    const OFFER_DOCUMENT             = 'offer_document_path';
    const DEFAULT_APPLICATION_LIMIT  = 'default_application_limit';

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }
}
