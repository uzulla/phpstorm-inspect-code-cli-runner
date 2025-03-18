<?php

declare(strict_types=1);

namespace PhpStormInspect\Inspection;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InspectionRunner
{
    private const CACHE_DIR = 'caches';
    private const INDEX_DIR = 'index';

    public function __construct(
        private readonly Filesystem $filesystem
    ) {
    }

    /**
     * Clears PhpStorm's cache to prevent stale cache issues
     */
    public function clearCache(string $phpstormSystemPath): void
    {
        if (is_dir($phpstormSystemPath . '/' . self::CACHE_DIR)) {
            $this->clearDirectory($phpstormSystemPath . '/' . self::CACHE_DIR);
        }

        if (is_dir($phpstormSystemPath . '/' . self::INDEX_DIR)) {
            $this->clearDirectory($phpstormSystemPath . '/' . self::INDEX_DIR);
        }
    }

    /**
     * Clears the output directory
     */
    public function clearOutputDirectory(string $outputPath): void
    {
        $files = glob($outputPath . '/*.xml') ?: [];
        $this->filesystem->remove($files);
    }

    /**
     * Runs the PhpStorm inspection
     */
    public function runInspection(
        string $inspectShExecutableFilepath,
        string $projectPath,
        string $inspectionProfileFilepath,
        string $outputPath,
        string $inspectedDirectory
    ): void {
        $process = new Process([
            $inspectShExecutableFilepath,
            $projectPath,
            $inspectionProfileFilepath,
            $outputPath,
            '-d',
            $inspectedDirectory
        ]);
        
        $process->setTimeout(3600); // 1 hour timeout
        $process->run();
        
        $output = $process->getOutput() . $process->getErrorOutput();
        
        // PhpStorm exits without error when another instance is already running
        if ($process->isSuccessful() && str_contains($output, 'Too Many Instances')) {
            throw new \RuntimeException('PhpStorm inspection failed: Too Many Instances');
        }
        
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('PhpStorm inspection failed: ' . $output);
        }
    }

    /**
     * Clears a directory by removing all files
     */
    private function clearDirectory(string $directory): void
    {
        $files = glob($directory . '/*') ?: [];
        $this->filesystem->remove($files);
    }
}
