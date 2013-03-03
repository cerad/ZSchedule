<?php
namespace Cerad\Bundle\ScheduleBundle\Schedule\Import;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\PropertyChangedListener;

use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;

class ImportScheduleSlots extends ImportScheduleBase
{
    protected function processGameTeam($team,$game,$gameReportStatus,$name,$score)
    {
        $team->setLevel($game->getLevel());
        $team->setName ($name);
        
        if ($gameReportStatus != 'No Report')
        {
            $team->setScore($score);
        }
        $game->addTeam($team);
        
        return $team;
    }
    protected $games = array();
    protected function processRow($row)
    {
        // Trying to avoid creating multiple arrays for each row
        $params = array(
            'season'    => $row['season'],
            'sport'     => $row['sport'], 
            'domain'    => $row['domain'],
            'domainSub' => $row['domainSub'],
        );
        // Some basic info      
        $project = $this->projectManager->processEntity($params,$this->persistFlag);
     
        $params['name'] = $row['level'];
        $level = $this->levelManager->processEntity($params,$this->persistFlag);
        
        $params['venue']    = $row['venue'];
        $params['venueSub'] = $row['venueSub'];
        unset($params['name']);
        
        $field = $this->fieldManager->processEntity($params, $this->persistFlag);
        
        // Typecast is important because the property change stuff is type specific
        $num = (int)$row['num'];
                
        /* ==========================================================
         * Check to see if already have game
         * Duration
         * With query: 16
         * Without      9
         */
        $gameManager = $this->gameManager;
        $gamex = $gameManager->loadGameForProjectNum($project,$num);
      //$gamex = null;
        if ($gamex)
        {
            return $this->processExistingGame($row,$gamex,$project,$level,$field,$num);
        }
        
        /* =========================================================
         * Some games seem to be exported twice? 1300491 is an example
         */
        if (isset($this->games[$num]))
        {
          //echo sprintf("Dup Game %d\n",$num); // 1300491, 1300869 in NasoaSlots20130220.xml
            return;
        }
        $this->games[$num] = $num;
        
        // New game
        $game = $gameManager->createGame();
        
        // Could do an array thing here
        $game->setNum    ($num);
        $game->setProject($project);
        $game->setLevel  ($level);
        $game->setField  ($field);
        $game->setStatus ($row['status']);
        
        // 2013-03-08T16:30:00
        $dtBeg = \DateTime::createFromFormat('Y-m-d*H:i:s',$row['dtBeg']);
        $dtEnd = \DateTime::createFromFormat('Y-m-d*H:i:s',$row['dtEnd']);
        
        $game->setDtBeg($dtBeg);
        $game->setDtEnd($dtEnd);
        
        $gameReportStatus = $row['gameReportStatus'];
        
        $this->processGameTeam(
                $gameManager->createGameTeamHome(),
                $game,$gameReportStatus,
                $row['homeTeamName'],$row['homeTeamScore']);
        
        $this->processGameTeam(
                $gameManager->createGameTeamAway(),
                $game,$gameReportStatus,
                $row['awayTeamName'],$row['awayTeamScore']);
        
        /* ==========================================================
         * The officials
         */
        $slots = $row['officialSlots'];
        for($slot = 1; $slot <= $slots; $slot++)
        {
            $role = $row['officialRole' . $slot];
            $name = $row['officialName' . $slot];
            
            $person = $gameManager->createGamePerson(array('slot' => $slot, 'role' => $role, 'name' => $name));
            $game->addPerson($person);
        }
        // Persist and flush
        $this->persist($game);
        $this->flush();
        
        return;
    }
    /* ====================================================================
     * Goint to be messy
     * Maybe not, just do the same sets on the existing game
     * 
     * Only flushing on game changes does not seem to help
     * Adding the listeners take just a tiny amount
     */
    protected function processExistingGame($row,$game,$project,$level,$field,$num)
    {
       // Maybe use changeListener?
       $this->gameHasChanged = false;
     //$game->addPropertyChangedListener($this);
       
       // The basics
       $game->setProject($project);
       $game->setLevel  ($level);
       $game->setField  ($field);
       $game->setNum    ($num);
       
       $game->setStatus ($row['status']);
       
       // Date triggers a change, the whole immutable thing, fixed in entity
       $dtBeg = \DateTime::createFromFormat('Y-m-d*H:i:s',$row['dtBeg']);
       $dtEnd = \DateTime::createFromFormat('Y-m-d*H:i:s',$row['dtEnd']);
       
       $game->setDtBeg($dtBeg);
       $game->setDtEnd($dtEnd);
       
       // For setting scores
       $gameReportStatus = $row['gameReportStatus'];
      
       $team = $game->getHomeTeam();
     //$team->addPropertyChangedListener($this);
       $this->processGameTeam($team,$game,$gameReportStatus,$row['homeTeamName'],$row['homeTeamScore']);
       
       $team = $game->getAwayTeam();
     //$team->addPropertyChangedListener($this);
       $this->processGameTeam($team,$game,$gameReportStatus,$row['awayTeamName'],$row['awayTeamScore']);
       
        /* ==========================================================
         * The officials
         */
        $slots = $row['officialSlots'];
        for($slot = 1; $slot <= $slots; $slot++)
        {
            $role = $row['officialRole' . $slot];
            $name = $row['officialName' . $slot];
            
            $person = $game->getPersonForSlot($slot);
            if (!$person)
            {
                // Game 1300182 20130202
                $person = $this->gameManager->createGamePerson(array('slot' => $slot, 'role' => $role, 'name' => $name));
                $game->addPerson($person);
                
                $this->gameHasChanged = true;
                
                //echo sprintf("No game person for slot %d %d\n",$game->getNum(),$slot);
                //die('');
            }
          //$person->addPropertyChangedListener($this);
            $person->setRole($role);
            $person->setName($name);
        }
      
        // Tracking changed games make no difference
        if ($this->gameHasChanged || true)
        {
            $this->flush();
        }
        return;
    }
    /* ====================================================================
     * Import a file
     */
    public function importFile($params,$reader = null)
    {
        if (!$reader) throw new \Exception('ImportScheduleSlots with no xml reader');
       
        $this->gameManager->getEventManager()->addEventSubscriber($this);
        
        $this->results = new ImportScheduleResults();
        
        $this->results->inputFileName  = $params['inputFileName'];
        $this->results->clientFileName = $params['clientFileName'];
        $this->results->totalGameCount = 0;
        
        if ($params['output'] == 'Post') $this->persistFlag = true;
        
        // Kind of screw but oh well
        while ($reader->read() && $reader->name !== 'Detail');
        
        while($reader->name == 'Detail')
        {
            $row = $params;
            foreach($this->map as $key => $attr)
            {
                $row[$key] = $reader->getAttribute($attr);
            }
            if ($row['gameNote'] != 'No Note')
            {
              //print_r($item);
              //echo sprintf("Note: %s\n",$item['gameNote']);
              //die();
            }
            $this->results->totalGamesCount++;
            $this->processRow($row);

            // Does not really help
            // $row = null;
            // unset($row);
            
            // On to the next one
            $reader->next('Detail');
        }
        $reader->close();
        
        $this->flush(true);
        
        $this->gameManager->getEventManager()->removeEventSubscriber($this);
         
        return $this->results;
    }
    /* ===============================================================
     * The main map
     */
    /* =========================================================================
    [sport] => Soccer
    [season] => SP2013
    [group] => NASOA
    [defaultGameStatus] => Normal
    [inputFileName] => /home/impd04/datax/arbiter/SP2013/NasoaSlots20130124.xml
    [num] => 402
    [dateTimeBegin] => 2013-01-26T10:00:00
    [dateTimeEnd] => 2013-01-26T11:15:00
    [groupSub] => MSSL
    [level] => MS-B
    [site] => John Hunt 4
    [siteSub] =>
    [homeTeamName] => Huntsville Boys
    [homeTeamScore] => 0
    [awayTeamName] => Whitesburg Boys
    [awayTeamScore] => 0
    [status] => Normal
    [officialSlots] => 3
    [officialRole1] => Referee
    [officialRole2] => AR1
    [officialRole3] => AR2
    [officialRole4] => No Fourth Position
    [officialRole5] => No Fifth Position
    [officialName1] =>
    [officialName2] =>
    [officialName3] =>
    [officialName4] => Empty
    [officialName5] => Empty
    [billTo] => MSSL, Mark Tillman
    [billAmount] => 0.00
    [billFees] => 0.00
    [gameNote] => No Note
    [gameNoteDate] =>
    [gameReportComments] =>
    [gameReportDateTime] => 1900-01-01T00:00:00
    [gameReportStatus] => No Report
    [gameReportOfficial] =>
     * 
     * Level
     * [domain] => NASOA
     * [sub]    => MSSL
     * [level]  => MS-B
     * [sport]  => Soccer
)    *
    */
    protected $map = array
    (
        'num'           => 'GameID',
        'dtBeg'         => 'From_Date',    // 2013-03-08T16:30:00
        'dtEnd'         => 'To_Date',
        'domainSub'     => 'Sport',        // AHSAA
        'level'         => 'Level',        // MS-B
        'venue'         => 'Site',
        'venueSub'      => 'Subsite',
        'homeTeamName'  => 'Home_Team',
        'homeTeamScore' => 'Home_Score',
        'awayTeamName'  => 'Away_Team',
        'awayTeamScore' => 'Away_Score',
        
        'status'        => 'Status',
        
        'officialSlots' => 'Slots_Total',
        
        'officialRole1' => 'First_Position',  // Referee 
        'officialRole2' => 'Second_Position', // AR1 (or possibly dual?
        'officialRole3' => 'Third_Position',  // AR2
        'officialRole4' => 'Fourth_Position', // 'No Fourth Position'
        'officialRole5' => 'Fifth_Position',  // 'No Fifth Position' 
        
        'officialName1' => 'First_Official', 
        'officialName2' => 'Second_Official', 
        'officialName3' => 'Third_Official', 
        'officialName4' => 'Fourth_Official',  // 'Empty'
        'officialName5' => 'Fifth_Official',   // 'Empty'
        
        'billTo'        => 'BillTo_Name',
        'billAmount'    => 'Bill_Amount',     // 100.00
        'billFees'      => 'Total_Game_Fees', //  37.00 ?
        
        'gameNote'      => 'Game_Note',    // 'No Note'
        'gameNoteDate'  => 'Note_Date=',   //  Blank
        
        'gameReportComments' => 'Game_Report_Comments',
        'gameReportDateTime' => 'Report_Posted_Date',   // 1900-01-01T00:00:00
        'gameReportStatus'   => 'Report_Status',        // 'No Report'
        'gameReportOfficial' => 'Reporting_Official',
        
    );

}
?>
