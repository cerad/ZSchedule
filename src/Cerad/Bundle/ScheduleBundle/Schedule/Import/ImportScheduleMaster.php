<?php
namespace Cerad\Bundle\ScheduleBundle\Schedule\Import;

use Symfony\Component\Stopwatch\Stopwatch;

class ImportScheduleMaster
{
    protected $manager;
    protected $excel;
    
    public function __construct($manager,$excel = null)
    {
        $this->manager = $manager;
        $this->excel   = $excel;
    }
    public function importFileXML($params)
    {
        $inputFileName = $params['inputFileName'];
        
        // Must be a report file
        $reader = new \XMLReader();
        $reader->open($inputFileName,null,LIBXML_COMPACT | LIBXML_NOWARNING);

        // Position to Report node
        if (!$reader->next('Report')) 
        {
            $reader->close();
            return;
        }
        // Verify report type
       $reportType = $reader->getAttribute('Name');
       switch($reportType)
       {
           case 'Games with Slots': $importClass = 'Cerad\Bundle\ScheduleBundle\Schedule\Import\ImportScheduleSlots'; break;
           default:
               $reader->close();
               return;
       }
       $import = new $importClass($this->manager);
       
       return $import->importFile($params,$reader);  
    }
    public function importFileXLS($params)
    {
        $inputFileName = $params['inputFileName'];
        
        $reader = $this->excel->load($inputFileName);

        $ws = $reader->getSheet(0);

        $rows = $ws->toArray();
     
        $importClass = 'Cerad\ArbiterBundle\Schedule\Import\ImportSchedulePortrait';
        
        $import = new $importClass($this->manager);
        
        return $import->importFile($params,$rows);
    }        

    public function importFile($params)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('importFile');
        
        // Do the truncate
        if (isset($params['truncate']) && $params['truncate']) 
        {
            $this->manager->gameManager->resetDatabase();
        }
        // Check file type
        if (isset($params['clientFileName'])) $ext = pathinfo($params['clientFileName'], PATHINFO_EXTENSION);
        else
        {
            $params['clientFileName'] = pathinfo  ($params['inputFileName'],  PATHINFO_BASENAME);
            
            $ext = pathinfo($params['inputFileName'],  PATHINFO_EXTENSION);
        }
        // XML Slots
        if ($ext == 'xml') 
        {
            $results = $this->importFileXML($params);
            $event = $stopwatch->stop('importFile');
            $results->duration = $event->getDuration();
            $results->memory   = $event->getMemory();
            return $results;
        }
        //if ($ext == 'xls') return $this->importFileXLS($params);
        
        return;       
    }
}
?>
