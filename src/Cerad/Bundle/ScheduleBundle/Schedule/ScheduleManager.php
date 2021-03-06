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
    /* =================================================================
     * Still not sure about having individual managers etc
     * Do some traffic directing here
     */
    public function loadFieldChoices($params = array())
    {
        return $this->fieldManager->loadFieldChoices($params);
    }
    public function loadTeamChoices($params = array())
    {
        return $this->gameManager->loadTeamChoices($params);
    }
    public function loadLevelChoices($params = array())
    {
        return $this->levelManager->loadLevelChoices($params);
    }
    public function loadDomainSubChoices($params = array())
    {
        return $this->levelManager->loadDomainSubChoices($params);
    }
    public function loadDomainChoices($params = array())
    {
        return $this->levelManager->loadDomainChoices($params);
    }
    public function loadSportChoices($params = array())
    {
        return $this->levelManager->loadSportChoices($params);
    }
    public function loadSeasonChoices($params = array())
    {
        return $this->projectManager->loadSeasonChoices($params);
    }
    public function loadGames($params = array())
    {
        return $this->gameManager->loadGames($params);
    }
}
?>
