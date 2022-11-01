<?php

namespace Nailalliance\Colorcategory\Model\Api;

use Exception;
use Psr\Log\LoggerInterface;

class Custom {
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getData($value)
    {
        $response = ['success' => false];

        try {
            $response = ['success' => true, 'message' => $value];
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }

        return json_encode($response); 
    }
}
