<?php

declare(strict_types=1);

namespace PhpStormInspect\Tests\Problem;

use PhpStormInspect\Problem\ProblemFactory;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class ProblemFactoryTest extends TestCase
{
    public function testExtractInspectionName(): void
    {
        $factory = new ProblemFactory();
        
        // Use reflection to access private method
        $reflectionMethod = new \ReflectionMethod(ProblemFactory::class, 'getInspectionName');
        $reflectionMethod->setAccessible(true);
        
        $result = $reflectionMethod->invoke($factory, 'PhpUndefinedFieldInspection.xml');
        
        $this->assertSame('PhpUndefinedFieldInspection', $result);
    }
}
