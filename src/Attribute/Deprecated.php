<?php

declare(strict_types=1);

namespace DFrame\Attribute;

/**
 * Deprecate attribute to mark methods or classes as deprecated.
 */
#[\Attribute]
class Deprecated
{
    /**
     * Constructor to initialize deprecation properties (using for attributes)
     * 
     * Example: #[Deprecate(message: 'This method is deprecated and will be removed in future versions.')]
     *
     * @param string $message The deprecation message to inform developers about the deprecation
     */
    public function __construct(
        public string $message = 'This method is deprecated and will be removed in future versions.',
        public ?string $since = null,
    ){}
}
