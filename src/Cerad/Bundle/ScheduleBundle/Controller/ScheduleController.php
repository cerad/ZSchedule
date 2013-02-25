<?php
namespace Cerad\Bundle\ScheduleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ScheduleController extends Controller
{
    public function indexAction(Request $request)
    {
        $manager = $this->get('cerad_schedule.schedule.manager');
        
        // Build the search parameter information
        $searchData = array();
        
        $searchData['domains']    = array('NASOA','ALYS');
        $searchData['domainSubs'] = array();
        
        $searchData['levels']  = array();
        $searchData['teams' ]  = array();
        $searchData['fields']  = array();
        
        $searchData['seasons']  = array('SP2013');
        $searchData['sports']   = array('Soccer');
        $searchData['statuses'] = array();
        
        $searchData['date1'] = '2013-01-01';
        $searchData['date2'] = '2013-02-15';
        
        $searchData['date1On'] = false;
        $searchData['date2On'] = false;
        
        $searchData['date1After' ] = false;
        $searchData['date2Before'] = false;
        
        $searchData['password'] = null;
        
        // Pull from session if nothing was passed
        $sessionSearchData = $request->getSession()->get('ScheduleSearchData');
        
        if ($sessionSearchData) $searchData = array_merge($searchData,json_decode($sessionSearchData,true));
         
        // Build the form
        $searchFormType = $this->get('cerad_schedule.schedule_search.formtype');
        
        // The form itself
        $searchForm = $this->createForm($searchFormType,$searchData);
        
        if ($request->getMethod() == 'POST')
        {
            $searchForm->bindRequest($request);

            if ($searchForm->isValid())
            {
                $searchData = $searchForm->getData(); // print_r($searchData); die( 'POSTED');
                
                $request->getSession()->set('ScheduleSearchData',json_encode($searchData));
                
                return $this->redirect($this->generateUrl('cerad_schedule.schedule'));
            }
            // $searchData = $searchForm->getData(); print_r($searchData); die( 'NOT VALID');
       }
        $games = $manager->loadGames($searchData);
      //$games = array();
        
        if ($searchData['password'] == 'nasoa') $isAdmin = true;
        else                                    $isAdmin = false;
        
        $tplData = array();
        $tplData['games']      = $games;
        $tplData['isAdmin']    = $isAdmin;
        $tplData['searchForm'] = $searchForm->createView();
        
        return $this->render('@schedule/index.html.twig',$tplData);
   }
}
?>
