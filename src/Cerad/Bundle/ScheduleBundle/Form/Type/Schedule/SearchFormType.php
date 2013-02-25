<?php
namespace Cerad\Bundle\ScheduleBundle\Form\Type\Schedule;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchFormTypeSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $manager;
    private $params;
    
    public function __construct(FormFactoryInterface $factory, $manager, $params)
    {
        $this->factory = $factory;
        $this->manager = $manager;
        $this->params  = $params;
    }

    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();

        if ($data === null) return;
        
        $form = $event->getForm();
        
        // Generate field pick list
        $fields = $this->manager->loadFieldChoices($data);
        array_unshift($fields,'All Fields');
        $form->add($this->factory->createNamed('fields', 'choice', null, array(
            'label'         => 'Fields',
            'required'      => false,
            'choices'       => $fields,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        // Generate team pick list
        $teams = $this->manager->loadTeamChoices($data);
        array_unshift($teams,'All Teams');
        $form->add($this->factory->createNamed('teams', 'choice', null, array(
            'label'         => 'Teams',
            'required'      => false,
            'choices'       => $teams,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        // Generate levels pick list
        $levels  = $this->manager->loadLevelChoices($data);
        array_unshift($levels,'All Levels');
        $form->add($this->factory->createNamed('levels', 'choice', null, array(
            'label'         => 'Levels',
            'required'      => false,
            'choices'       => $levels,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        // Generate sports pick list
        $names  = $this->manager->loadDomainSubChoices($data);
        array_unshift($names,'All Sub Groups');
        $form->add($this->factory->createNamed('domainSubs', 'choice', null, array(
            'label'         => 'Sub Groups',
            'required'      => false,
            'choices'       => $names,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        // Generate groups pick list
        $names  = $this->manager->loadDomainChoices($data);
        array_unshift($names,'All Groups');
        $form->add($this->factory->createNamed('domains', 'choice', null, array(
            'label'         => 'Groups',
            'required'      => false,
            'choices'       => $names,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 10),
        )));
        
        // Generate seasons pick list
        $names = $this->manager->loadSeasonChoices($data);
        array_unshift($names,'All Seasons');
        $form->add($this->factory->createNamed('seasons', 'choice', null, array(
            'label'         => 'Seasons',
            'required'      => false,
            'choices'       => $names,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 4),
        )));
        // Generate sports pick list
        $names = $this->manager->loadSportChoices($data);
        array_unshift($names,'All Sports');
        $form->add($this->factory->createNamed('sports', 'choice', null, array(
            'label'         => 'Sports',
            'required'      => false,
            'choices'       => $names,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 4),
        )));
    }
}

class SearchFormType extends AbstractType
{
    public function getName() { return 'schedule_search'; }
    
    protected $manager;
    protected $params;

    public function __construct($manager,$params)
    {
        $this->manager = $manager;
        $this->params  = $params;        
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // For dynamic fields
        $subscriber = new SearchFormTypeSubscriber($builder->getFormFactory(),$this->manager,$this->params);
        $builder->addEventSubscriber($subscriber);
        
        $builder->add('date1', 'date', array(
            'label'         => 'From Date',
            'input'         => 'string',
            'widget'        => 'choice',
            'format'        => 'yyyy-MMM-dd',
            'required'      => true,
        ));
        $builder->add('date2', 'date', array(
            'label'         => 'To Date',
            'input'         => 'string',
            'widget'        => 'choice',
            'format'        => 'yyyy-MMM-dd',
            'required'      => true,
        ));
        $builder->add('date1On','checkbox', array('label' => 'ON', 'required' => false));
        $builder->add('date2On','checkbox', array('label' => 'ON', 'required' => false));
        
        $builder->add('date1After', 'checkbox', array('label' => 'After',  'required' => false));
        $builder->add('date2Before','checkbox', array('label' => 'Before', 'required' => false));
        
        // Game Statuses
        $statuses = $this->params['game_statuses'];
        array_unshift($statuses,'All Status');
        
        $builder->add('statuses', 'choice', array(
            'label'         => 'Game Status',
            'required'      => false,
            'choices'       => $statuses,
            'expanded'      => false,
            'multiple'      => true,
            'disabled'      => false,
            'attr' => array('size' => 4),
        ));
        $builder->add('password','text',array(
            'label'    => 'Password',
            'required' => false,
            'attr'     => array('size' => 10),
        ));       
    }
}
?>
