<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Organization;
use Tests\TestCase;

class OrganizationPaidContestTest extends TestCase
{
    private function makeOrg(array $attrs = []): Organization
    {
        return new Organization(array_merge([
            'bank_name'             => null,
            'bank_bik'              => null,
            'bank_account'          => null,
            'correspondent_account' => null,
            'kpp'                   => null,
            'offer_accepted'        => false,
        ], $attrs));
    }

    public function test_has_complete_bank_details_returns_false_when_all_empty(): void
    {
        $this->assertFalse($this->makeOrg()->hasCompleteBankDetails());
    }

    public function test_has_complete_bank_details_returns_false_when_one_field_missing(): void
    {
        $this->assertFalse($this->makeOrg([
            'bank_name'             => 'Сбербанк',
            'bank_bik'              => '044525225',
            'bank_account'          => '40702810000000000001',
            'correspondent_account' => '30101810400000000225',
            // kpp missing
        ])->hasCompleteBankDetails());
    }

    public function test_has_complete_bank_details_returns_true_when_all_filled(): void
    {
        $this->assertTrue($this->makeOrg([
            'bank_name'             => 'Сбербанк',
            'bank_bik'              => '044525225',
            'bank_account'          => '40702810000000000001',
            'correspondent_account' => '30101810400000000225',
            'kpp'                   => '773601001',
        ])->hasCompleteBankDetails());
    }

    public function test_can_host_paid_contests_requires_both_bank_details_and_offer(): void
    {
        // Bank details complete but offer not accepted
        $org = $this->makeOrg([
            'bank_name'             => 'Сбербанк',
            'bank_bik'              => '044525225',
            'bank_account'          => '40702810000000000001',
            'correspondent_account' => '30101810400000000225',
            'kpp'                   => '773601001',
            'offer_accepted'        => false,
        ]);
        $this->assertFalse($org->canHostPaidContests());
    }

    public function test_can_host_paid_contests_requires_both_bank_details_and_offer_accepted(): void
    {
        // Offer accepted but bank details incomplete
        $org = $this->makeOrg([
            'bank_name'      => 'Сбербанк',
            'offer_accepted' => true,
        ]);
        $this->assertFalse($org->canHostPaidContests());
    }

    public function test_can_host_paid_contests_returns_true_when_both_present(): void
    {
        $org = $this->makeOrg([
            'bank_name'             => 'Сбербанк',
            'bank_bik'              => '044525225',
            'bank_account'          => '40702810000000000001',
            'correspondent_account' => '30101810400000000225',
            'kpp'                   => '773601001',
            'offer_accepted'        => true,
        ]);
        $this->assertTrue($org->canHostPaidContests());
    }
}
