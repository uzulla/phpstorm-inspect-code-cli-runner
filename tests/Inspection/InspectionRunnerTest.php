<?php

declare(strict_types=1);

namespace PhpStormInspect\Tests\Inspection;

use PhpStormInspect\Inspection\InspectionRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InspectionRunnerTest extends TestCase
{
    public function testClearCache(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        
        // PHPUnit 10 では withConsecutive() が削除されたため、個別に設定
        $filesystem->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function ($arg) {
                static $callCount = 0;
                $callCount++;
                
                $this->assertIsArray($arg);
                
                return null;
            });
        
        $runner = new InspectionRunner($filesystem);
        
        // Create temporary directories for testing
        $tempDir = sys_get_temp_dir() . '/phpstorm-inspect-test-' . uniqid();
        mkdir($tempDir);
        mkdir($tempDir . '/caches');
        mkdir($tempDir . '/index');
        
        try {
            $runner->clearCache($tempDir);
        } finally {
            // Clean up
            rmdir($tempDir . '/caches');
            rmdir($tempDir . '/index');
            rmdir($tempDir);
        }
    }
    
    public function testClearOutputDirectory(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('remove')
            ->with($this->isType('array'));
        
        $runner = new InspectionRunner($filesystem);
        $runner->clearOutputDirectory('/path/to/output');
    }
}
