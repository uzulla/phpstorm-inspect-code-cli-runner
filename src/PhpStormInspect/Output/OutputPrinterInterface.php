<?php

declare(strict_types=1);

namespace PhpStormInspect\Output;

use Symfony\Component\Console\Output\OutputInterface;

interface OutputPrinterInterface
{
    public const RETURN_CODE_OK = 0;
    public const RETURN_CODE_ERROR = 1;
    
    /**
     * Prints the output of the inspection
     */
    public function printOutput(string $projectPath, string $outputPath, OutputInterface $output): int;
}
