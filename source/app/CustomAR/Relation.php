<?php
declare(strict_types=1);

namespace App\CustomAR;

/**
 * Enum of possible relation options.
 */
final class Relation
{
    // Relation types (default)
    public const HAS_ONE      = 'hasOne';
    public const HAS_MANY     = 'hasMany';
    public const BELONGS_TO   = 'belongsTo';
    public const REFERS_TO    = 'refersTo';
    public const MANY_TO_MANY = 'manyToMany';

    // Morphed relations
    public const BELONGS_TO_MORPHED = 'belongsToMorphed';
    public const MORPHED_HAS_ONE    = 'morphedHasOne';
    public const MORPHED_HAS_MANY   = 'morphedHasMany';
}
