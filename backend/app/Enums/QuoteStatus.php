<?php

namespace App\Enums;

/**
 * Quote lifecycle. Business rules about states live here, not in
 * controllers: the API, the Blade print view and the tests all ask the enum.
 */
enum QuoteStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    /**
     * Human-readable label shown in the UI (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Sent => 'Enviado',
            self::Accepted => 'Aceptado',
            self::Rejected => 'Rechazado',
        };
    }

    /**
     * Only drafts can change their header and lines, or be deleted.
     */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /**
     * Whether the quote may move from the current status to $target.
     *
     *   draft    -> sent
     *   sent     -> accepted, rejected, draft (reopen)
     *   accepted -> (final)
     *   rejected -> draft (reopen)
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => $target === self::Sent,
            self::Sent => in_array($target, [self::Accepted, self::Rejected, self::Draft], true),
            self::Accepted => false,
            self::Rejected => $target === self::Draft,
        };
    }
}
