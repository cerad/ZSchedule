<?php
namespace Cerad\Bundle\ScheduleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName       ('schedule:import')
            ->setDescription('Schedule Import')
            ->addArgument   ('inputFileName', InputArgument::REQUIRED, 'Input File Name')
            ->addArgument   ('truncate',      InputArgument::OPTIONAL, 'Truncate')
        ;
    }
    protected function getService($id)     { return $this->getContainer()->get($id); }
    protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputFileName = $input->getArgument('inputFileName');
        $truncate      = $input->getArgument('truncate');
        
        if ($truncate) $truncate = true;
        
        echo sprintf("Import %s %d\n",$inputFileName,$truncate);
        
        $this->loadFile($inputFileName,$truncate);
        
    }
    protected function loadFile($file, $truncate = false)
    {
        switch(substr($file,0,4))
        {
            case 'Naso': $domain = 'NASOA'; break;
            case 'Alys': $domain = 'ALYS';  break;
            default:
                return;
        }
        $datax = $this->getParameter('datax');
        $params = array
        (
            'truncate' => $truncate,
            'output'   => 'Post', // Scan, Excel
            'sport'    => 'Soccer',
            'season'   => 'SP2013',
            'domain'   => $domain,
            
            'defaultGameStatus' => 'Normal',
            'inputFileName'     => $datax . '/arbiter/SP2013/' . $file,
        );
        $import = $this->getService('cerad_schedule.schedule.import.master');
        
        $results = $import->importFile($params);
        
        echo sprintf("Import Complete %-5s %s\n",$domain,$params['inputFileName']);
        echo $results;
    }
}

?>
