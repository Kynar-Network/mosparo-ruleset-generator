<?php
// src/Command/DeleteYesterdayTempDirectoryCommand.php
namespace App\Command;

use App\Service\TempDirectoryService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class DeleteYesterdayTempDirectoryCommand extends Command
{
    protected static $defaultName = 'app:delete-yesterday-temp-directory';
    private $tempDirectoryService;

    public function __construct(TempDirectoryService $tempDirectoryService)
    {
        parent::__construct();
        $this->tempDirectoryService = $tempDirectoryService;
    }

    protected function configure()
    {
        $this
            ->setName('app:delete-yesterday-temp-directory') // Add this line
            ->setDescription('Deletes the temporary directory from yesterday')
            ->setHelp('This command allows you to delete the temporary directory created yesterday.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();
        $tempDirectoryYesterday = $this->tempDirectoryService->getTempDirectoryYesterday();

        if ($filesystem->exists($tempDirectoryYesterday)) {
            $filesystem->remove($tempDirectoryYesterday);
            $io->success('Yesterday\'s temporary directory has been deleted.');
        } else {
            $io->warning('Yesterday\'s temporary directory does not exist.');
        }

        return Command::SUCCESS;
    }
}
