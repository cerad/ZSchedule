<?php
namespace Cerad\Bundle\ScheduleBundle\Twig;


class ScheduleExtension extends \Twig_Extension
{
    protected $env;
    
    public function getName()
    {
        return 'cerad_schedule_extension';
    }
    
    public function initRuntime(\Twig_Environment $env)
    {
        parent::initRuntime($env);
        $this->env = $env;
    }
    protected function escape($string)
    {
        return twig_escape_filter($this->env,$string);
    }
    public function getFunctions()
    {
        return array(            
            'game_person_slot_class'   => new \Twig_Function_Method($this, 'gamePersonSlotClass'),
            'game_person_slot_count'   => new \Twig_Function_Method($this, 'gamePersonSlotCount'),
            'game_person_status_short' => new \Twig_Function_Method($this, 'gamePersonStatusShort'),
            
            'game_is_section_match'    => new \Twig_Function_Method($this, 'isSectionMatch'),
       );
    }
    /* =====================================
     * Person slot stuff
     */
    public function gamePersonSlotCount($game)
    {
        $total  = 0;
        $filled = 0;
        foreach($game->getPersons() as $person)
        {
            $total++;
            if ($person->getName()) $filled++;
        }
        return sprintf('%d-%d',$filled,$total);
    }
    public function gamePersonSlotClass($game)
    {
        $total    = 0;
        $filled   = 0;
        $accepted = 0;
        foreach($game->getPersons() as $person)
        {
            $total++;
            if ($person->getName()) $filled++;
            if ($person->getStatus() == 'Accepted') $accepted++; // Need to have status loaded
        }
        // Filled and accepted, assume can't have accepted unless someone is on it
        if ($accepted == $total) return 'game-person-slots-Filled';
        
        // Somone is on each slot but at least one has not accepted
        if ($filled == $total) return 'game-person-slots-Filled-Pending';
        
        // No one assigned at all
        if ($filled == 0) return 'game-person-slots-Empty';
        
        // Catch all
        return 'game-person-slots-Other';
    }
    public function gamePersonStatusShort($gamePerson)
    {
        $status = $gamePerson->getStatus();
        
        switch($status)
        {
            case '':          return '...';
            case 'Unknown':   return '...';
            case 'Accepted':  return 'ACC';
            case 'Published': return 'PUB';
            case 'Notified':  return 'NOT';
       }
       return substr($status,0,3);
         
        die('Game Person Status ' . $status);
    }

    /* ===================================================
     * Well that was strange, think using a tab character messed up the file
     * 
     * Initial attempt at determining section matches
     * 
     * Need to filter tournament games
     */
    public function isSectionMatch($game)
    {   
        $levelName = $game->getLevel()->getName();
        
        if (($levelName != 'HS-Var B') && ($levelName != 'HS-Var G')) return false;
        
        $homeName = $levelName . ' ' . $game->getHomeTeam()->getName();
        $awayName = $levelName . ' ' . $game->getAwayTeam()->getName();
        
        if (isset($this->nameMap[$homeName])) $homeName = $this->nameMap[$homeName];
        if (isset($this->nameMap[$awayName])) $awayName = $this->nameMap[$awayName];
        
        if (!$homeName) return;
        if (!$awayName) return;
                
        if (isset($this->sectionMap[$homeName])) $homeSection = $this->sectionMap[$homeName];
        else                                     $homeSection = null;
        
        if (isset($this->sectionMap[$awayName])) $awaySection = $this->sectionMap[$awayName];
        else                                     $awaySection = null; // die('No section for ' . $homeName);
        
        if (!$homeSection) die('No section for ' . $homeName); // return false;
        if (!$awaySection) die('No section for ' . $awayName); // return false;
        
        if (!($homeSection == $awaySection)) return false;
        
        return true;
    }
    protected $nameMap = array
    (
        'HS-Var G W. Limestone HS' => 'HS-Var G West Limestone HS',
        'HS-Var B W. Limestone HS' => 'HS-Var B West Limestone HS',
        
        'HS-Var G Randolph'        => 'HS-Var G Randolph HS',
        'HS-Var B Randolph'        => 'HS-Var G Randolph HS',
        'HS-Var G Randolph School' => 'HS-Var G Randolph HS', // ***
        'HS-Var B Randolph School' => 'HS-Var B Randolph HS', // ***
        
        'HS-Var G Catholic HS'     => 'HS-Var G Pope John Paul II HS',
        'HS-Var B Catholic HS'     => 'HS-Var B Pope John Paul II HS',
        'HS-Var G Westminster HS'  => 'HS-Var G Westminster Christian HS', // ***
        'HS-Var G Westminster CA'  => 'HS-Var G Westminster Christian HS', // ***
        'HS-Var B Westminster CA'  => 'HS-Var B Westminster Christian HS',
        'HS-Var G Lee HS'          => 'HS-Var G Lee-Huntsville HS',
        'HS-Var B Lee HS'          => 'HS-Var B Lee-Huntsville HS',
        'HS-Var G Madison Academy' => 'HS-Var G Madison Academy HS',
        'HS-Var B Madison Academy' => 'HS-Var B Madison Academy HS',
        'HS-Var G E. Limestone HS' => 'HS-Var G East Limestone HS',
        'HS-Var B E. Limestone HS' => 'HS-Var B East Limestone HS',
        'HS-Var G St. Bernard HS'  => 'HS-Var G Saint Bernard HS',
        'HS-Var B St. Bernard HS'  => 'HS-Var B Saint Bernard HS',
        'HS-Var G Whitesburg CA'   => 'HS-Var G Whitesburg Christian HS',
        'HS-Var B Whitesburg CA'   => 'HS-Var B Whitesburg Christian HS',
        'HS-Var G Ft. Payne HS'    => 'HS-Var G Fort Payne HS',
        'HS-Var B Ft. Payne HS'    => 'HS-Var B Fort Payne HS',
        'HS-Var G Athens Bible'    => 'HS-Var G Athens Bible HS',
        'HS-Var B Athens Bible'    => 'HS-Var B Athens Bible HS',
        'HS-Var G Mars Hill HS'    => 'HS-Var G Mars Hill Bible HS',
        'HS-Var B Mars Hill HS'    => 'HS-Var B Mars Hill Bible HS',
        'HS-Var G Athens'          => 'HS-Var G Athens HS',
        'HS-Var B Athens'          => 'HS-Var B Athens HS',
        'HS-Var G Ben Russell HS'  => 'HS-Var G Benjamin Russell HS',
        'HS-Var B Ben Russell HS'  => 'HS-Var B Benjamin Russell HS',
        'HS-Var G Southside HS'    => 'HS-Var G Southside-Gadsden HS',
        'HS-Var B Southside HS'    => 'HS-Var B Southside-Gadsden HS',
        'HS-Var G Gadsden HS'      => 'HS-Var G Gadsden City HS', // Two Gadsden
        'HS-Var B Gadsden HS'      => 'HS-Var B Gadsden City HS', 
        'HS-Var G Hartsell HS'     => 'HS-Var G Hartselle HS',       // *** Spelling
        'HS-Var B Hartsell HS'     => 'HS-Var B Hartselle HS',
        'HS-Var G Vestavia HS'     => 'HS-Var G Vestavia Hills HS',
        'HS-Var B Vestavia HS'     => 'HS-Var B Vestavia Hills HS',
        
        '' => '',
       
        'HS-Var G Center Point HS' => 'HS-Var G Center Point HS', // *** Skipped?, Nope not in listing
        
       
        'HS-Var B Lincoln CountyHS' => '', // TN Team?  Is it Lawerence
        'HS-Var B Brentwood HS'     => '',
        
        'HS-Var B Memphis Univ.'    => '',
        
        'HS-Var B Germantown HS' => '',
        'HS-Var B Collierville HS' => '',
        'HS-Var B Norman North HS' => '',
        'HS-Var B G1L' => '',
        'HS-Var B G2L' => '',
        'HS-Var B G3L' => '',
        'HS-Var B G4L' => '',
        'HS-Var B G5L' => '',
        'HS-Var B G6L' => '',
        'HS-Var B G7L' => '',
        'HS-Var B G8L' => '',
        'HS-Var B G1W' => '',
        'HS-Var B G2W' => '',
        'HS-Var B G3W' => '',
        'HS-Var B G4W' => '',
        'HS-Var B G5W' => '',
        'HS-Var B G6W' => '',
        'HS-Var B G7W' => '',
        'HS-Var B G8W' => '',
        '' => '',
        '' => '',
        '' => '',
       
        'HS-Var G Team 1' => '', // Needs to be something, null causes isset to fail
        'HS-Var G Team 2' => '',
        'HS-Var G Team 3' => '',
        'HS-Var G Team 4' => '',
        'HS-Var G Team 5' => '',
        'HS-Var G Team 6' => '',
        'HS-Var G Team 7' => '',
        'HS-Var G Team 8' => '',
        
        'HS-Var G Wild Card 1' => '',
        'HS-Var G Wild Card 2' => '',
        'HS-Var G Wild Card 3' => '',
        'HS-Var G Wild Card 4' => '',
        
        'HS-Var G Place 1' => '',
        'HS-Var G Place 2' => '',
        'HS-Var G Place 3' => '',
        'HS-Var G Place 4' => '',
        'HS-Var G Place 5' => '',
        'HS-Var G Place 6' => '',
        'HS-Var G Place 7' => '',
        'HS-Var G Place 8' => '',
   
        'HS-Var G '   => '', // Some teams have no name 1300764
        'HS-Var B '   => '',
        
        'HS-Var B A1' => '',
        'HS-Var B A2' => '',
        'HS-Var B A3' => '',
        'HS-Var B B1' => '',
        'HS-Var B B2' => '',
        'HS-Var B B3' => '',
        'HS-Var B C1' => '',
        'HS-Var B C2' => '',
        'HS-Var B C3' => '',
        
        'HS-Var B 1SEED' => '',
        'HS-Var B 2SEED' => '',
        'HS-Var B 3SEED' => '',
        'HS-Var B 4SEED' => '',
        
        'HS-Var B TBD1' => '',
        'HS-Var B TBD2' => '',
        'HS-Var B TBD3' => '',
        'HS-Var B TBD4' => '',
        
        'HS-Var B Team 1' => '',
        'HS-Var B Team 2' => '',
        'HS-Var B Team 3' => '',
        'HS-Var B Team 4' => '',
       
        'HS-Var G Winner Game 13' => '',
        'HS-Var G TBD' => '',
        'HS-Var B TBD' => '',
        '' => '',
    );
    protected $sectionMap = array
    (
        'HS-Var B Bayside Academy HS'               => '4-1',
        'HS-Var B Charles Henderson HS'             => '4-2',
        'HS-Var B LAMP HS'                          => '4-3',
        'HS-Var B Alabama Christian HS'             => '4-4',
        'HS-Var B Elmore County HS'                 => '4-5',
        'HS-Var B American Christian HS'            => '4-6',
        'HS-Var B Indian Springs HS'                => '4-7',
        'HS-Var B Altamont HS'                      => '4-8',
        'HS-Var B Cottage Hill Christian HS'        => '4-1',
        'HS-Var B Daleville HS'                     => '4-2',
        'HS-Var B Montgomery Academy HS'            => '4-3',
        'HS-Var B Brewbaker Tech HS'                => '4-4',
        'HS-Var B Holtville HS'                     => '4-5',
        'HS-Var B Calera HS'                        => '4-6',
        'HS-Var B Parkway Christian HS'             => '4-7',
        'HS-Var B Fultondale HS'                    => '4-8',
        'HS-Var B UMS-Wright HS'                    => '4-1',
        'HS-Var B Houston Academy HS'               => '4-2',
        'HS-Var B Prattville Christian HS'          => '4-3',
        'HS-Var B Montgomery Catholic HS'           => '4-4',
        'HS-Var B Tallassee HS'                     => '4-5',
        'HS-Var B Holy Spirit HS'                   => '4-6',
        'HS-Var B Shades Mountain Christian HS'     => '4-7',
        'HS-Var B Saint Bernard HS'                 => '4-8',
        'HS-Var B Providence Christian HS'          => '4-2',
        'HS-Var B Trinity Presbyterian HS'          => '4-3',
        'HS-Var B Saint James HS'                   => '4-4',
        'HS-Var B Sipsey Valley HS'                 => '4-6',
        'HS-Var B Westminster-Oak Mountain HS'      => '4-7',
        'HS-Var B Leeds HS'                         => '4-9',
        'HS-Var B Anniston HS'                      => '4-10',
        'HS-Var B Donoho HS'                        => '4-11',
        'HS-Var B Cherokee County HS'               => '4-12',
        'HS-Var B Athens Bible HS'                  => '4-13',
        'HS-Var B Ardmore HS'                       => '4-14',
        'HS-Var B Brindlee Mountain HS'             => '4-15',
        'HS-Var B Madison Academy HS'               => '4-16',
        'HS-Var B Oneonta HS'                       => '4-9',
        'HS-Var B Jacksonville HS'                  => '4-10',
        'HS-Var B Faith Christian HS'               => '4-11',
        'HS-Var B Collinsville HS'                  => '4-12',
        'HS-Var B Covenant Christian HS'            => '4-13',
        'HS-Var B Priceville HS'                    => '4-14',
        'HS-Var B Madison County HS'                => '4-15',
        'HS-Var B Westminster Christian HS'         => '4-16',
        'HS-Var B St. Clair County HS'              => '4-9',
        'HS-Var B Saks HS'                          => '4-10',
        'HS-Var B Sacred Heart HS'                  => '4-11',
        'HS-Var B Crossville HS'                    => '4-12',
        'HS-Var B Danville HS'                      => '4-13',
        'HS-Var B Tanner HS'                        => '4-14',
        'HS-Var B New Hope HS'                      => '4-15',
        'HS-Var B Pope John Paul II HS'             => '4-16',
        'HS-Var B Susan Moore HS'                   => '4-9',
        'HS-Var B Guntersville HS'                  => '4-12',
        'HS-Var B Mars Hill Bible HS'               => '4-13',
        'HS-Var B West Limestone HS'                => '4-14',
        'HS-Var B Randolph HS'                      => '4-15',
        'HS-Var B Whitesburg Christian HS'          => '4-16',
        'HS-Var B West Morgan HS'                   => '4-14',
        'HS-Var B B.C. Rain HS'                     => '5-1',
        'HS-Var B Citronelle HS'                    => '5-2',
        'HS-Var B Benjamin Russell HS'              => '5-3',
        'HS-Var B Briarwood Christian HS'           => '5-4',
        'HS-Var B Central-Tuscaloosa HS'            => '5-5',
        'HS-Var B Center Point HS'                  => '5-6',
        'HS-Var G Center Point HS'                  => '5-6', // *** Did I skip Center Point Girls?
        'HS-Var B Arab HS'                          => '5-7',
        'HS-Var B Athens HS'                        => '5-8',
        'HS-Var B Gulf Shores HS'                   => '5-1',
        'HS-Var B Faith Academy HS'                 => '5-2',
        'HS-Var B Carroll HS'                       => '5-3',
        'HS-Var B Homewood HS'                      => '5-4',
        'HS-Var B Chilton County HS'                => '5-5',
        'HS-Var B Moody HS'                         => '5-6',
        'HS-Var B Brewer HS'                        => '5-7',
        'HS-Var B Cullman HS'                       => '5-8',
        'HS-Var B Leflore HS'                       => '5-1',
        'HS-Var B Saraland HS'                      => '5-2',
        'HS-Var B Eufaula HS'                       => '5-3',
        'HS-Var B John Carroll HS'                  => '5-4',
        'HS-Var B Demopolis HS'                     => '5-5',
        'HS-Var B Pinson Valley HS'                 => '5-6',
        'HS-Var B Columbia HS'                      => '5-7',
        'HS-Var B East Limestone HS'                => '5-8',
        'HS-Var B Saint Paul￿s HS'                   => '5-1',
        'HS-Var B Satsuma HS'                       => '5-2',
        'HS-Var B Marbury HS'                       => '5-3',
        'HS-Var B Ramsay HS'                        => '5-4',
        'HS-Var B Paul Bryant HS'                   => '5-5',
        'HS-Var B Springville HS'                   => '5-6',
        'HS-Var B Fort Payne HS'                    => '5-7',
        'HS-Var B Hartselle HS'                     => '5-8',
        'HS-Var B Spanish Fort HS'                  => '5-1',
        'HS-Var B Vigor HS'                         => '5-2',
        'HS-Var B Russell County HS'                => '5-3',
        'HS-Var B Sylacauga HS'                     => '5-4',
        'HS-Var B Pleasant Grove HS'                => '5-5',
        'HS-Var B Walker HS'                        => '5-6',
        'HS-Var B Southside-Gadsden HS'             => '5-7',
        'HS-Var B Lawrence County HS'               => '5-8',
        'HS-Var B Valley HS'                        => '5-3',
        'HS-Var B Talladega HS'                     => '5-4',
        'HS-Var B Muscle Shoals HS'                 => '5-8',
        'HS-Var B Alma Bryant HS'                   => '6-1',
        'HS-Var B Baldwin County HS'                => '6-2',
        'HS-Var B Daphne HS'                        => '6-3',
        'HS-Var B Dothan HS'                        => '6-4',
        'HS-Var B Auburn HS'                        => '6-5',
        'HS-Var B Lee-Montgomery HS'                => '6-6',
        'HS-Var B Hillcrest-Tuscaloosa HS'          => '6-7',
        'HS-Var B Chelsea HS'                       => '6-8',
        'HS-Var B Baker HS'                         => '6-1',
        'HS-Var B Mary G. Montgomery HS'            => '6-2',
        'HS-Var B Fairhope HS'                      => '6-3',
        'HS-Var B Enterprise HS'                    => '6-4',
        'HS-Var B Central-Phenix City HS'           => '6-5',
        'HS-Var B Prattville HS'                    => '6-6',
        'HS-Var B Northridge HS'                    => '6-7',
        'HS-Var B Oak Mountain HS'                  => '6-8',
        'HS-Var B Davidson HS'                      => '6-1',
        'HS-Var B McGill-Toolen HS'                 => '6-2',
        'HS-Var B Foley HS'                         => '6-3',
        'HS-Var B Northview HS'                     => '6-4',
        'HS-Var B Opelika HS'                       => '6-5',
        'HS-Var B Stanhope Elmore HS'               => '6-6',
        'HS-Var B Tuscaloosa County HS'             => '6-7',
        'HS-Var B Pelham HS'                        => '6-8',
        'HS-Var B Theodore HS'                      => '6-1',
        'HS-Var B Murphy HS'                        => '6-2',
        'HS-Var B Robertsdale HS'                   => '6-3',
        'HS-Var B Smiths Station HS'                => '6-5',
        'HS-Var B Wetumpka HS'                      => '6-6',
        'HS-Var B Thompson HS'                      => '6-8',
        'HS-Var B Bessemer City HS'                 => '6-9',
        'HS-Var B Mountain Brook HS'                => '6-10',
        'HS-Var B Clay-Chalkville HS'               => '6-11',
        'HS-Var B Gadsden City HS'                  => '6-12',
        'HS-Var B Albertville HS'                   => '6-13',
        'HS-Var B Bob Jones HS'                     => '6-14',
        'HS-Var B Buckhorn HS'                      => '6-15',
        'HS-Var B Austin HS'                        => '6-16',
        'HS-Var B Hoover HS'                        => '6-9',
        'HS-Var B Shades Valley HS'                 => '6-10',
        'HS-Var B Gardendale HS'                    => '6-11',
        'HS-Var B Pell City HS'                     => '6-12',
        'HS-Var B Grissom HS'                       => '6-13',
        'HS-Var B James Clemens HS'                 => '6-14',
        'HS-Var B Hazel Green HS'                   => '6-15',
        'HS-Var B Decatur HS'                       => '6-16',
        'HS-Var B Minor HS'                         => '6-9',
        'HS-Var B Vestavia Hills HS'                => '6-10',
        'HS-Var B Hewitt-Trussville HS'             => '6-11',
        'HS-Var B Oxford HS'                        => '6-12',
        'HS-Var B Huntsville HS'                    => '6-13',
        'HS-Var B Lee-Huntsville HS'                => '6-14',
        'HS-Var B Sparkman HS'                      => '6-15',
        'HS-Var B Florence HS'                      => '6-16',
        'HS-Var B Spain Park HS'                    => '6-9',
        'HS-Var B Huffman HS'                       => '6-11',
        'HS-Var G Bayside Academy HS'               => '4-1',
        'HS-Var G Daleville HS'                     => '4-2',
        'HS-Var G LAMP HS'                          => '4-3',
        'HS-Var G Alabama Christian HS'             => '4-4',
        'HS-Var G Elmore County HS'                 => '4-5',
        'HS-Var G American Christian HS'            => '4-6',
        'HS-Var G Altamont HS'                      => '4-7',
        'HS-Var G Leeds HS'                         => '4-8',
        'HS-Var G Cottage Hill Christian HS'        => '4-1',
        'HS-Var G Houston Academy HS'               => '4-2',
        'HS-Var G Montgomery Academy HS'            => '4-3',
        'HS-Var G Brewbaker Tech HS'                => '4-4',
        'HS-Var G Holtville HS'                     => '4-5',
        'HS-Var G Calera HS'                        => '4-6',
        'HS-Var G Fultondale HS'                    => '4-7',
        'HS-Var G Oneonta HS'                       => '4-8',
        'HS-Var G Saint Luke￿s HS'                  => '4-1',
        'HS-Var G Providence Christian HS'          => '4-2',
        'HS-Var G Prattville Christian HS'          => '4-3',
        'HS-Var G Montgomery Catholic HS'           => '4-4',
        'HS-Var G Tallassee HS'                     => '4-5',
        'HS-Var G Holy Spirit HS'                   => '4-6',
        'HS-Var G Indian Springs HS'                => '4-7',
        'HS-Var G Saint Clair County HS'            => '4-8',
        'HS-Var G UMS-Wright HS'                    => '4-1',
        'HS-Var G Trinity Presbyterian HS'          => '4-3',
        'HS-Var G Saint James HS'                   => '4-4',
        'HS-Var G Parkway Christian HS'             => '4-7',
        'HS-Var G Susan Moore HS'                   => '4-8',
        'HS-Var G Anniston HS'                      => '4-9',
        'HS-Var G Faith Christian HS'               => '4-10',
        'HS-Var G Collinsville HS'                  => '4-11',
        'HS-Var G Athens Bible HS'                  => '4-12',
        'HS-Var G Ardmore HS'                       => '4-13',
        'HS-Var G Brindlee Mountain HS'             => '4-14',
        'HS-Var G Danville HS'                      => '4-15',
        'HS-Var G Madison Academy HS'               => '4-16',
        'HS-Var G Jacksonville HS'                  => '4-9',
        'HS-Var G Donoho HS'                        => '4-10',
        'HS-Var G Coosa Christian HS'               => '4-11',
        'HS-Var G Mars Hill Bible HS'               => '4-12',
        'HS-Var G West Limestone HS'                => '4-13',
        'HS-Var G Madison County HS'                => '4-14',
        'HS-Var G Pope John Paul II HS'             => '4-15',
        'HS-Var G New Hope HS'                      => '4-16',
        'HS-Var G Saks HS'                          => '4-9',
        'HS-Var G Sacred Heart HS'                  => '4-10',
        'HS-Var G Crossville HS'                    => '4-11',
        'HS-Var G Priceville HS'                    => '4-12',
        'HS-Var G West Morgan HS'                   => '4-13',
        'HS-Var G Randolph HS'                      => '4-14',
        'HS-Var G Saint Bernard HS'                 => '4-15',
        'HS-Var G Tanner HS'                        => '4-16',
        'HS-Var G Guntersville HS'                  => '4-11',
        'HS-Var G Westminster Christian HS'         => '4-16',
        'HS-Var G B.C. Rain HS'                     => '5-1',
        'HS-Var G Citronelle HS'                    => '5-2',
        'HS-Var G Benjamin Russell HS'              => '5-3',
        'HS-Var G Briarwood Christian HS'           => '5-4',
        'HS-Var G Central-Tuscaloosa HS'            => '5-5',
        'HS-Var G Moody HS'                         => '5-6',
        'HS-Var G Arab HS'                          => '5-7',
        'HS-Var G Athens HS'                        => '5-8',
        'HS-Var G Gulf Shores HS'                   => '5-1',
        'HS-Var G Faith Academy HS'                 => '5-2',
        'HS-Var G Carroll HS'                       => '5-3',
        'HS-Var G Homewood HS'                      => '5-4',
        'HS-Var G Demopolis HS'                     => '5-5',
        'HS-Var G Pinson Valley HS'                 => '5-6',
        'HS-Var G Brewer HS'                        => '5-7',
        'HS-Var G Cullman HS'                       => '5-8',
        'HS-Var G Leflore HS'                       => '5-1',
        'HS-Var G Saraland HS'                      => '5-2',
        'HS-Var G Marbury HS'                       => '5-3',
        'HS-Var G John Carroll HS'                  => '5-4',
        'HS-Var G McAdory HS'                       => '5-5',
        'HS-Var G Springville HS'                   => '5-6',
        'HS-Var G Columbia HS'                      => '5-7',
        'HS-Var G East Limestone HS'                => '5-8',
        'HS-Var G Spanish Fort HS'                  => '5-1',
        'HS-Var G Satsuma HS'                       => '5-2',
        'HS-Var G Valley HS'                        => '5-3',
        'HS-Var G Ramsay HS'                        => '5-4',
        'HS-Var G Parker HS'                        => '5-5',
        'HS-Var G Walker HS'                        => '5-6',
        'HS-Var G Fort Payne HS'                    => '5-7',
        'HS-Var G Hartselle HS'                     => '5-8',
        'HS-Var G Saint Paul￿s HS'                  => '5-1',
        'HS-Var G Vigor HS'                         => '5-2',
        'HS-Var G Sylacauga HS'                     => '5-4',
        'HS-Var G Paul Bryant HS'                   => '5-5',
        'HS-Var G Southside-Gadsden HS'             => '5-7',
        'HS-Var G Lawrence County HS'               => '5-8',
        'HS-Var G Talladega HS'                     => '5-4',
        'HS-Var G Pleasant Grove HS'                => '5-5',
        'HS-Var G Alma Bryant HS'                   => '6-1',
        'HS-Var G Baldwin County HS'                => '6-2',
        'HS-Var G Daphne HS'                        => '6-3',
        'HS-Var G Dothan HS'                        => '6-4',
        'HS-Var G Auburn HS'                        => '6-5',
        'HS-Var G Lee-Montgomery HS'                => '6-6',
        'HS-Var G Hillcrest-Tuscaloosa HS'          => '6-7',
        'HS-Var G Chelsea HS'                       => '6-8',
        'HS-Var G Baker HS'                         => '6-1',
        'HS-Var G Mary G. Montgomery HS'            => '6-2',
        'HS-Var G Fairhope HS'                      => '6-3',
        'HS-Var G Enterprise HS'                    => '6-4',
        'HS-Var G Central-Phenix City HS'           => '6-5',
        'HS-Var G Prattville HS'                    => '6-6',
        'HS-Var G Northridge HS'                    => '6-7',
        'HS-Var G Oak Mountain HS'                  => '6-8',
        'HS-Var G Davidson HS'                      => '6-1',
        'HS-Var G McGill-Toolen HS'                 => '6-2',
        'HS-Var G Foley HS'                         => '6-3',
        'HS-Var G Northview HS'                     => '6-4',
        'HS-Var G Opelika HS'                       => '6-5',
        'HS-Var G Stanhope Elmore HS'               => '6-6',
        'HS-Var G Tuscaloosa County HS'             => '6-7',
        'HS-Var G Pelham HS'                        => '6-8',
        'HS-Var G Theodore HS'                      => '6-1',
        'HS-Var G Murphy HS'                        => '6-2',
        'HS-Var G Robertsdale HS'                   => '6-3',
        'HS-Var G Smiths Station HS'                => '6-5',
        'HS-Var G Wetumpka HS'                      => '6-6',
        'HS-Var G Thompson HS'                      => '6-8',
        'HS-Var G Bessemer City HS'                 => '6-9',
        'HS-Var G Mountain Brook HS'                => '6-10',
        'HS-Var G Clay-Chalkville HS'               => '6-11',
        'HS-Var G Gadsden City HS'                  => '6-12',
        'HS-Var G Albertville HS'                   => '6-13',
        'HS-Var G Bob Jones HS'                     => '6-14',
        'HS-Var G Buckhorn HS'                      => '6-15',
        'HS-Var G Austin HS'                        => '6-16',
        'HS-Var G Hoover HS'                        => '6-9',
        'HS-Var G Shades Valley HS'                 => '6-10',
        'HS-Var G Gardendale HS'                    => '6-11',
        'HS-Var G Pell City HS'                     => '6-12',
        'HS-Var G Grissom HS'                       => '6-13',
        'HS-Var G James Clemens HS'                 => '6-14',
        'HS-Var G Hazel Green HS'                   => '6-15',
        'HS-Var G Decatur HS'                       => '6-16',
        'HS-Var G Minor HS'                         => '6-9',
        'HS-Var G Vestavia Hills HS'                => '6-10',
        'HS-Var G Hewitt-Trussville HS'             => '6-11',
        'HS-Var G Oxford HS'                        => '6-12',
        'HS-Var G Huntsville HS'                    => '6-13',
        'HS-Var G Lee-Huntsville HS'                => '6-14',
        'HS-Var G Sparkman HS'                      => '6-15',
        'HS-Var G Florence HS'                      => '6-16',
        'HS-Var G Spain Park HS'                    => '6-9',
        'HS-Var G Huffman HS'                       => '6-11',       
    );
}
?>
