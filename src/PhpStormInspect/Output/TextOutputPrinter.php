<?php

declare(strict_types=1);

namespace PhpStormInspect\Output;

use PhpStormInspect\Problem\Problem;
use PhpStormInspect\Problem\ProblemFactory;
use Symfony\Component\Console\Output\OutputInterface;

class TextOutputPrinter implements OutputPrinterInterface
{
    public function __construct(
        private readonly ProblemFactory $problemFactory
    ) {
    }

    public function printOutput(string $projectPath, string $outputPath, OutputInterface $output): int
    {
        $problemsByFile = $this->loadProblems($projectPath, $outputPath);
        $this->printProblems($problemsByFile, $output);
        
        return count($problemsByFile) > 0 ? self::RETURN_CODE_ERROR : self::RETURN_CODE_OK;
    }
    
    /**
     * Loads problems from XML files
     * 
     * @return array<string, array<Problem>>
     */
    protected function loadProblems(string $projectPath, string $outputPath): array
    {
        $outputFiles = glob($outputPath . '/*.xml') ?: [];
        $problemsByFile = [];

        foreach ($outputFiles as $outputFile) {
            $xml = simplexml_load_file($outputFile);
            if ($xml === false) {
                continue;
            }

            $problemsXml = $xml->xpath('/problems/problem');
            if ($problemsXml === false) {
                continue;
            }
            
            foreach ($problemsXml as $problemXml) {
                $problem = $this->problemFactory->create($projectPath, basename($outputFile), $problemXml);
                $problemsByFile[$problem->filename][] = $problem;
            }
        }
        
        return $problemsByFile;
    }

    /**
     * Prints problems to the output
     * 
     * @param array<string, array<Problem>> $problemsByFile
     */
    protected function printProblems(array $problemsByFile, OutputInterface $output): void
    {
        ksort($problemsByFile);

        foreach ($problemsByFile as $filename => $problems) {
            $this->sortProblemsByLine($problems);

            $output->writeln("File: {$filename}");
            $output->writeln("--------------------------------------------------------------------------------");
            $output->writeln("Found " . count($problems) . " problems");
            $output->writeln("--------------------------------------------------------------------------------");

            foreach ($problems as $problem) {
                $output->writeln("Line {$problem->line}: {$problem->class}: {$problem->description}");
            }

            $output->writeln("--------------------------------------------------------------------------------\n");
        }
    }

    /**
     * Sorts problems by line number
     * 
     * @param array<Problem> $problems
     */
    protected function sortProblemsByLine(array &$problems): void
    {
        usort($problems, fn (Problem $a, Problem $b) => $a->line - $b->line);
    }
}
