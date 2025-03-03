<?php
// src/Service/TempDirectoryService.php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TempDirectoryService
{
    private $params;
    private $tempDirectory;
    private $tempDirectoryYesterday;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->setTempDirectoryYesterday();
        $this->setTempDirectory();
    }

    private function setTempDirectoryYesterday()
    {
        $baseDirectory = $this->params->get('temp_directory');
        $yesterday = (new \DateTime())->modify('-1 day')->format('Y-m-d');
        $this->tempDirectoryYesterday = $baseDirectory . '/' . $yesterday;
    }

    public function getTempDirectoryYesterday()
    {
        return $this->tempDirectoryYesterday;
    }

    private function setTempDirectory()
    {
        $baseDirectory = $this->params->get('temp_directory');
        $today = (new \DateTime())->format('Y-m-d');
        $this->tempDirectory = $baseDirectory . '/' . $today;
    }

    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }
}
