<?php

declare(strict_types=1);

namespace PhpStormInspect\Tests\Output;

use PhpStormInspect\Output\TextOutputPrinter;
use PhpStormInspect\Problem\Problem;
use PhpStormInspect\Problem\ProblemFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class TextOutputPrinterTest extends TestCase
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
        
        // Create a TextOutputPrinter with the mock factory
        $printer = $this->getMockBuilder(TextOutputPrinter::class)
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
        $this->assertSame(TextOutputPrinter::RETURN_CODE_ERROR, $returnCode);
        
        // Assert that the output contains the expected text
        $outputText = $output->fetch();
        $this->assertStringContainsString('File: /path/to/file.php', $outputText);
        $this->assertStringContainsString('Found 1 problems', $outputText);
        $this->assertStringContainsString('Line 10: Undefined field: Field "foo" not found in class', $outputText);
    }
}
