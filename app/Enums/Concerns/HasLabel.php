<?php

namespace App\Enums\Concerns;

/**
 * Contract for backed enums that are rendered in the UI.
 *
 * "color" returns a semantic token (success, warning, danger, info,
 * neutral, primary) that the StatusBadge component maps to classes.
 */
interface HasLabel
{
    public function label(): string;

    public function color(): string;
}
