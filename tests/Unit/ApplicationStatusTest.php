<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ApplicationStatus;
use Tests\TestCase;

class ApplicationStatusTest extends TestCase
{
    public function test_payment_pending_case_exists(): void
    {
        $this->assertSame('payment_pending', ApplicationStatus::PaymentPending->value);
    }

    public function test_payment_pending_label(): void
    {
        $this->assertSame('В процессе оплаты', ApplicationStatus::PaymentPending->label());
    }

    public function test_payment_pending_color_is_purple(): void
    {
        $this->assertStringContainsString('purple', ApplicationStatus::PaymentPending->color());
    }

    public function test_payment_pending_is_not_evaluated(): void
    {
        $this->assertFalse(ApplicationStatus::PaymentPending->isEvaluated());
    }

    public function test_new_is_not_evaluated(): void
    {
        $this->assertFalse(ApplicationStatus::New->isEvaluated());
    }

    public function test_placed_statuses_are_evaluated(): void
    {
        $this->assertTrue(ApplicationStatus::Participant->isEvaluated());
        $this->assertTrue(ApplicationStatus::Place1->isEvaluated());
        $this->assertTrue(ApplicationStatus::Place2->isEvaluated());
        $this->assertTrue(ApplicationStatus::Place3->isEvaluated());
        $this->assertTrue(ApplicationStatus::Rejected->isEvaluated());
    }

    public function test_all_statuses_have_labels(): void
    {
        foreach (ApplicationStatus::cases() as $status) {
            $this->assertNotEmpty($status->label(), "Status {$status->value} has no label");
        }
    }

    public function test_all_statuses_have_colors(): void
    {
        foreach (ApplicationStatus::cases() as $status) {
            $this->assertNotEmpty($status->color(), "Status {$status->value} has no color");
        }
    }
}
