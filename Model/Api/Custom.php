<?php

namespace Nailalliance\Colorcategory\Model\Api;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Catalog\Helper\Image;

class Custom {
    /** @var LoggerInterface */
    protected $logger;

    /** @var CollectionFactory */
    protected $_productCollectionFactory;

    /** @var StockItemRepository */
    protected $_stockItemRepository;

    /** @var Image */
    protected $_productImageHelper;

    public function __construct(
        LoggerInterface $logger, 
        CollectionFactory $productCollectionFactory,
        StockItemRepository $stockItemRepository,
        Image $image
    ) {
        $this->logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_productImageHelper = $image;
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

        return $response; 
    }

    private function parseCategoryProducts($collection): array
    {
        $products = [];
        foreach($collection as $product) {
            $product_ = $product->getData();
            $product_['product_thumbnail_image'] = $this->_productImageHelper->init($product, 'product_thumbnail_image')->getUrl();
            // $product_['stock_qty'] = ($this->_stockItemRepository->get($product->getId()))->getData();
            $product_['in_stock'] = $product->isInStock();
            $products[] = $product_;
            
            // $products[] = [
            //     'name' => $product->getName(),
            //     'url' => $product->getProductUrl()
            // ];
        }
        return $products;
    }
}
