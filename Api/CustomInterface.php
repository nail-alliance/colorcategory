<?php
/**
 * Custom API
 * Select product by category for a single store id.
 * * @author Fabian Nino <fabian@nailalliance.com>
 * @copyright Copyright (c) 2022, Nail Alliance
 */

namespace Nailalliance\Colorcategory\Api;

interface CustomInterface
{
    /**
     * GET for Post api
     *
     * @param string $value
     * @param string $store_id
     * @return array
     */
    public function getData(string $value, string $store_id);
}