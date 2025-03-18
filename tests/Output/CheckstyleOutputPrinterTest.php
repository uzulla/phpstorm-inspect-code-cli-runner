<?php

declare(strict_types=1);

namespace PhpStormInspect\Tests\Output;

use PhpStormInspect\Output\CheckstyleOutputPrinter;
use PhpStormInspect\Problem\Problem;
use PhpStormInspect\Problem\ProblemFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class CheckstyleOutputPrinterTest extends TestCase
{
    public function testPrintOutput(): void
    {
        // Create a mock ProblemFactory that returns predefined problems
        $problemFactory = $this->createMock(ProblemFactory::class);
        $problemFactory->method('create')
            ->willReturn(
                new Problem(
                    'PhpUndefinedFieldInspection',
                    '/path/to/file.php',
                    10,
                    'Undefined field',
                    'WARNING',
                    'Field "foo" not found in class'
                )
            );
        
        // Create a CheckstyleOutputPrinter with the mock factory
        $printer = $this->getMockBuilder(CheckstyleOutputPrinter::class)
            ->setConstructorArgs([$problemFactory])
            ->onlyMethods(['loadProblems'])
            ->getMock();
        
        // Set up the mock to return predefined problems
        $printer->method('loadProblems')
            ->willReturn([
                '/path/to/file.php' => [
                    new Problem(
                        'PhpUndefinedFieldInspection',
                        '/path/to/file.php',
                        10,
                        'Undefined field',
                        'WARNING',
                        'Field "foo" not found in class'
                    )
                ]
            ]);
        
        // Create a buffered output to capture the output
        $output = new BufferedOutput();
        
        // Call the method being tested
        $returnCode = $printer->printOutput('/path/to/project', '/path/to/output', $output);
        
        // Assert that the return code is correct
        $this->assertSame(CheckstyleOutputPrinter::RETURN_CODE_ERROR, $returnCode);
        
        // Assert that the output contains the expected XML structure
        $outputText = $output->fetch();
        $this->assertStringContainsString('<checkstyle version="1.0.0">', $outputText);
        $this->assertStringContainsString('<file name="/path/to/file.php">', $outputText);
        $this->assertStringContainsString('<error line="10" column="0" severity="warning" message="Field &quot;foo&quot; not found in class"', $outputText);
    }
}
