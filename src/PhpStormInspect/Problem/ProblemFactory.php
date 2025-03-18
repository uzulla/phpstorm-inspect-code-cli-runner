<?php

declare(strict_types=1);

namespace PhpStormInspect\Problem;

use SimpleXMLElement;

class ProblemFactory
{
    /**
     * Creates a Problem object from XML data
     */
    public function create(string $projectPath, string $xmlFilename, SimpleXMLElement $problemXml): Problem
    {
        return new Problem(
            inspectionName: $this->getInspectionName($xmlFilename),
            filename: $this->getFilename($projectPath, $problemXml),
            line: (int)$problemXml->line,
            class: (string)$problemXml->problem_class,
            severity: (string)$problemXml->problem_class['severity'],
            description: (string)$problemXml->description
        );
    }

    /**
     * Extracts the inspection name from the XML filename
     */
    private function getInspectionName(string $xmlFilename): string
    {
        return preg_replace('/(.*)\.xml/', '$1', $xmlFilename);
    }

    /**
     * Gets the absolute filename from the XML problem data
     */
    private function getFilename(string $projectPath, SimpleXMLElement $problemXml): string
    {
        $filename = str_replace('file://$PROJECT_DIR$/', $projectPath . '/', (string)$problemXml->file);
        
        $realpath = realpath($filename);
        if ($realpath === false) {
            throw new \RuntimeException(sprintf('File "%s" not found', $filename));
        }
        
        return $realpath;
    }
}
