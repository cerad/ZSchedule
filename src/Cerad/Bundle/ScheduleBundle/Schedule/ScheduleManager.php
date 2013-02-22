<?php

namespace Cerad\Bundle\ScheduleBundle\Schedule;

// Need something to tie all the game bundle managers together
class ScheduleManager
{
    public $projectManager;
    public $levelManager;
    public $fieldManager;
    public $gameManager;
    
    public function __construct($projectManager,$levelManager,$fieldManager,$gameManager)
    {
        $this->projectManager = $projectManager;
        $this->levelManager   = $levelManager;
        $this->fieldManager   = $fieldManager;
        $this->gameManager    = $gameManager;
    }
}
?>
