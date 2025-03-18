<?php

declare(strict_types=1);

namespace PhpStormInspect\Command;

use NinjaMutex\Lock\FlockLock;
use NinjaMutex\Mutex;
use PhpStormInspect\Inspection\InspectionRunner;
use PhpStormInspect\Output\CheckstyleOutputPrinter;
use PhpStormInspect\Output\OutputPrinterInterface;
use PhpStormInspect\Output\TextOutputPrinter;
use PhpStormInspect\Problem\ProblemFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'inspect',
    description: 'Run PhpStorm inspections from CLI',
)]
class InspectCommand extends Command
{
    private const FORMAT_TEXT = 'text';
    private const FORMAT_CHECKSTYLE = 'checkstyle';

    protected function configure(): void
    {
        $this
            ->addOption(
                'inspect-sh',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to inspect.sh script'
            )
            ->addOption(
                'system-path',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to .WebIde*/system directory'
            )
            ->addOption(
                'project-path',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to project directory (that contains .idea directory)'
            )
            ->addOption(
                'profile',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to inspection profile XML file'
            )
            ->addOption(
                'directory',
                null,
                InputOption::VALUE_REQUIRED,
                'Path in which are the inspected sources'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Format of output result (text or checkstyle)',
                self::FORMAT_TEXT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            // Get options from command line or environment variables
            $inspectShPath = $this->getOptionWithEnvFallback($input, 'inspect-sh', 'PHPSTORM_INSPECT_SH');
            $systemPath = $this->getOptionWithEnvFallback($input, 'system-path', 'PHPSTORM_SYSTEM_PATH');
            $projectPath = $this->getRequiredOption($input, 'project-path');
            $profilePath = $this->getRequiredOption($input, 'profile');
            $directoryPath = $this->getRequiredOption($input, 'directory');
            $format = $input->getOption('format');
            
            $outputPath = realpath(__DIR__ . '/../../../output') ?: throw new \RuntimeException('Output directory not found');
            
            $lock = new FlockLock(sys_get_temp_dir());
            $mutex = new Mutex('phpstorm-inspect', $lock);
            
            if (!$mutex->acquireLock(2 * 3600 * 1000)) {
                throw new \RuntimeException('Could not acquire lock');
            }
            
            $inspectionRunner = new InspectionRunner(new Filesystem());
            $inspectionRunner->clearCache($systemPath);
            $inspectionRunner->clearOutputDirectory($outputPath);
            $inspectionRunner->runInspection(
                $inspectShPath,
                $projectPath,
                $profilePath,
                $outputPath,
                $directoryPath
            );
            
            $outputPrinter = $this->getOutputPrinter($format);
            $returnCode = $outputPrinter->printOutput($projectPath, $outputPath, $output);
            $inspectionRunner->clearOutputDirectory($outputPath);
            
            $mutex->releaseLock();
            
            return $returnCode;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
    
    private function getRequiredOption(InputInterface $input, string $name): string
    {
        $value = $input->getOption($name);
        if ($value === null) {
            throw new \InvalidArgumentException(sprintf('Option "%s" is required', $name));
        }
        
        $realpath = realpath($value);
        if ($realpath === false) {
            throw new \InvalidArgumentException(sprintf('Path "%s" not found', $value));
        }
        
        return $realpath;
    }
    
    /**
     * Gets an option value with fallback to environment variable
     */
    private function getOptionWithEnvFallback(InputInterface $input, string $optionName, string $envName): string
    {
        $value = $input->getOption($optionName);
        
        // If option is not provided, try to get from environment
        if ($value === null) {
            $envValue = \PhpStormInspect\Config\EnvLoader::get($envName);
            if ($envValue === null) {
                throw new \InvalidArgumentException(
                    sprintf('Option "%s" is required or set it in .env file as %s', $optionName, $envName)
                );
            }
            
            $value = $envValue;
        }
        
        $realpath = realpath($value);
        if ($realpath === false) {
            throw new \InvalidArgumentException(sprintf('Path "%s" not found', $value));
        }
        
        return $realpath;
    }
    
    private function getOutputPrinter(string $format): OutputPrinterInterface
    {
        $problemFactory = new ProblemFactory();
        
        return match ($format) {
            self::FORMAT_TEXT => new TextOutputPrinter($problemFactory),
            self::FORMAT_CHECKSTYLE => new CheckstyleOutputPrinter($problemFactory),
            default => throw new \InvalidArgumentException(sprintf('Undefined format "%s"', $format)),
        };
    }
}
