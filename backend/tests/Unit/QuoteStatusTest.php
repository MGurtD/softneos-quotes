<?php

namespace Tests\Unit;

use App\Enums\QuoteStatus;
use PHPUnit\Framework\TestCase;

class QuoteStatusTest extends TestCase
{
    /**
     * State table from the spec. Iterating over cases() makes the test fail
     * if a new case (e.g. "expired") is added without a rule.
     *
     * @var array<string, list<string>>
     */
    private const ALLOWED = [
        'draft' => ['sent'],
        'sent' => ['accepted', 'rejected', 'draft'],
        'accepted' => [],
        'rejected' => ['draft'],
    ];

    public function test_every_transition_follows_the_state_table(): void
    {
        foreach (QuoteStatus::cases() as $from) {
            $this->assertArrayHasKey($from->value, self::ALLOWED, "Missing rule for {$from->value}");

            foreach (QuoteStatus::cases() as $to) {
                $this->assertSame(
                    in_array($to->value, self::ALLOWED[$from->value], true),
                    $from->canTransitionTo($to),
                    "{$from->value} -> {$to->value}",
                );
            }
        }
    }

    public function test_only_drafts_are_editable(): void
    {
        $editable = array_filter(QuoteStatus::cases(), fn (QuoteStatus $status): bool => $status->isEditable());

        $this->assertSame([QuoteStatus::Draft], array_values($editable));
    }
}
