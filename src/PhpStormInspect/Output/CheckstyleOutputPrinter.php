<?php

declare(strict_types=1);

namespace PhpStormInspect\Output;

use PhpStormInspect\Problem\Problem;
use Symfony\Component\Console\Output\OutputInterface;

class CheckstyleOutputPrinter extends TextOutputPrinter
{
    /**
     * Prints problems in checkstyle format
     * 
     * @param array<string, array<Problem>> $problemsByFile
     */
    protected function printProblems(array $problemsByFile, OutputInterface $output): void
    {
        ksort($problemsByFile);

        $report = new \SimpleXMLElement('<checkstyle/>');
        $report->addAttribute('version', '1.0.0');

        foreach ($problemsByFile as $filename => $problems) {
            $this->sortProblemsByLine($problems);

            $file = $report->addChild('file');
            $file->addAttribute('name', $filename);

            foreach ($problems as $problem) {
                $error = $file->addChild('error');
                $error->addAttribute('line', (string)$problem->line);
                $error->addAttribute('column', '0');
                $error->addAttribute('severity', strtolower($problem->severity));
                $error->addAttribute('message', $problem->description);
            }
        }

        $document = dom_import_simplexml($report)->ownerDocument;
        if ($document !== null) {
            $document->formatOutput = true;
            $output->write($document->saveXML());
        }
    }
}
