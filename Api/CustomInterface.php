<?php

namespace Nailalliance\Colorcategory\Api;

interface CustomInterface
{
    /**
     * GET for Post api
     * @return string
     */
    public function getData(string $value, string $store_id);
}
