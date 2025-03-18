<?php

declare(strict_types=1);

namespace PhpStormInspect\Tests\Problem;

use PhpStormInspect\Problem\Problem;
use PHPUnit\Framework\TestCase;

class ProblemTest extends TestCase
{
    public function testProblemCreation(): void
    {
        $problem = new Problem(
            'PhpUndefinedFieldInspection',
            '/path/to/file.php',
            10,
            'Undefined field',
            'WARNING',
            'Field "foo" not found in class'
        );
        
        $this->assertSame('PhpUndefinedFieldInspection', $problem->inspectionName);
        $this->assertSame('/path/to/file.php', $problem->filename);
        $this->assertSame(10, $problem->line);
        $this->assertSame('Undefined field', $problem->class);
        $this->assertSame('WARNING', $problem->severity);
        $this->assertSame('Field "foo" not found in class', $problem->description);
    }
}
