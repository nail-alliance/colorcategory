<?php

namespace Nailalliance\Colorcategory\Model\Api;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Custom {
    /** @var LoggerInterface */
    protected $logger;

    /** @var CollectionFactory */
    protected $_productCollectionFactory;

    public function __construct(
        LoggerInterface $logger, 
        CollectionFactory $productCollectionFactory
    ) {
        $this->logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getData($value)
    {
        $response = ['success' => false];

        try {
            $ids = explode(",", $value);
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addCategoriesFilter(['in' => $ids]);

            // $response = ['success' => true, 'message' => $value];
            $response = $this->parseCategoryProducts($collection);
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }

        return json_encode($response); 
    }

    private function parseCategoryProducts($collection): array
    {
        $products = [];
        foreach($collection as $product) {
            $products[] = [
                'name' => $product->getName(),
                'url' => $product->getProductUrl()
            ];
        }
        return $products;
    }
}
