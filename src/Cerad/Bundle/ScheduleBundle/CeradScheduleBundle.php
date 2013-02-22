<?php
namespace Cerad\Bundle\ScheduleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use Cerad\Bundle\ScheduleBundle\DependencyInjection\ScheduleExtension;

class CeradScheduleBundle extends Bundle
{   
    public function getContainerExtension()
    {
        return new ScheduleExtension();
    }
}   
?>
