<?php

namespace Symfony\ActivityBundle\Charts\Gallery;

use Leg\GoogleChartsBundle\Charts\BaseChart;
use Leg\GoogleChartsBundle\Drivers\DriverInterface;

class MyChart extends BaseChart
{
    public function __construct()
    {
        parent::__construct();


    }

    public function getDefaultOptions() 
    { 
        return array( 'width' => 200, 'height' => 200, 'datas' => array(100, 75, 45) ); 
    }
}
?>
