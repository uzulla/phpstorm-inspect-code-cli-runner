<?php

declare(strict_types=1);

namespace PhpStormInspect\Tests\Problem;

use PhpStormInspect\Problem\ProblemFactory;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class ProblemFactoryTest extends TestCase
{
    public function testCreateProblem(): void
    {
        $projectPath = '/path/to/project';
        $xmlFilename = 'PhpUndefinedFieldInspection.xml';
        
        $xml = <<<XML
<problem>
    <file>file://\$PROJECT_DIR\$/src/Controller/UserController.php</file>
    <line>17</line>
    <problem_class severity="WARNING">Undefined field</problem_class>
    <description>Field 'usre' not found in class</description>
</problem>
XML;
        
        $problemXml = new SimpleXMLElement($xml);
        
        // Mock realpath to return a predictable value
        $factory = $this->getMockBuilder(ProblemFactory::class)
            ->onlyMethods(['getFilename'])
            ->getMock();
        
        $factory->method('getFilename')
            ->willReturn('/path/to/project/src/Controller/UserController.php');
        
        $problem = $factory->create($projectPath, $xmlFilename, $problemXml);
        
        $this->assertSame('PhpUndefinedFieldInspection', $problem->inspectionName);
        $this->assertSame('/path/to/project/src/Controller/UserController.php', $problem->filename);
        $this->assertSame(17, $problem->line);
        $this->assertSame('Undefined field', $problem->class);
        $this->assertSame('WARNING', $problem->severity);
        $this->assertSame("Field 'usre' not found in class", $problem->description);
    }
}
