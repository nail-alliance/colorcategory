<?php
/**
 * Custom API
 * Select product by category for a single store id.
 * 
 * @author Fabian Nino <fabian@nailalliance.com>
 * @copyright Copyright (c) 2022, Nail Alliance
*/

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Nailalliance_Colorcategory',
    __DIR__
);
