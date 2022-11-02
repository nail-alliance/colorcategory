<?php
/**
 * Custom API
 * Select product by category for a single store id.
 * 
 * @author Fabian Nino <fabian@nailalliance.com>
 * @copyright Copyright (c) 2022, Nail Alliance
*/

namespace Nailalliance\Colorcategory\Api;

interface CustomInterface
{
    /**
     * GET for Post api
     * @return array
     */
    public function getData(string $value, string $store_id);
}
