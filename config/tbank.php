<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | T-Bank (Tinkoff) Acquiring API
    |--------------------------------------------------------------------------
    | Terminal key and password from the T-Bank merchant dashboard.
    | Use the DEMO terminal for development, swap to production in .env on server.
    |
    | Test terminal:  1698927993527DEMO / 0wehg18rji3moc4q
    | Test API URL:   https://rest-api-test.tinkoff.ru/v2/
    |
    | Prod terminal:  25159284
    | Prod API URL:   https://securepay.tinkoff.ru/v2/
    */

    'terminal_key' => env('TBANK_TERMINAL_KEY', '1698927993527DEMO'),
    'password'     => env('TBANK_PASSWORD', '0wehg18rji3moc4q'),
    'base_url'     => env('TBANK_BASE_URL', 'https://rest-api-test.tinkoff.ru/v2/'),

    /*
    | Receipt (ФЗ-54 / онлайн-касса) settings.
    | taxation: osn | usn_income | usn_income_outcome | patent | envd | esn
    | vat:      none | vat0 | vat10 | vat110 | vat20 | vat120
    */
    'taxation' => env('TBANK_TAXATION', 'usn_income'),
    'vat'      => env('TBANK_VAT', 'none'),
];
