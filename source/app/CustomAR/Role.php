<?php

declare(strict_types=1);

namespace App\CustomAR;

/**
 * Enum of possible role options.
 */
final class Role
{
    // Role types (default)
    public const ADMIN     = 'admin';
    public const STUDENT   = 'student';
    public const REVIEWER  = 'reviewer';
}
