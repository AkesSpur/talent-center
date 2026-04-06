<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_all_three_cases_exist(): void
    {
        $this->assertSame('accepted',    PaymentStatus::Accepted->value);
        $this->assertSame('refunded',    PaymentStatus::Refunded->value);
        $this->assertSame('distributed', PaymentStatus::Distributed->value);
    }

    public function test_labels(): void
    {
        $this->assertSame('Принят',    PaymentStatus::Accepted->label());
        $this->assertSame('Возврат',   PaymentStatus::Refunded->label());
        $this->assertSame('Разнесён',  PaymentStatus::Distributed->label());
    }

    public function test_all_statuses_have_colors(): void
    {
        foreach (PaymentStatus::cases() as $status) {
            $this->assertNotEmpty($status->color(), "PaymentStatus {$status->value} has no color");
        }
    }
}
