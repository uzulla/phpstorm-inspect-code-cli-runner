<?php

declare(strict_types=1);

namespace PhpStormInspect\Problem;

class Problem
{
    public function __construct(
        public readonly string $inspectionName,
        public readonly string $filename,
        public readonly int $line,
        public readonly string $class,
        public readonly string $severity,
        public readonly string $description
    ) {
    }
}
