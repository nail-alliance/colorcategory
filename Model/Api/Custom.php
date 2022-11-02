<?php

namespace Nailalliance\Colorcategory\Model\Api;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Interceptor as ProductInterceptor;

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
    public function getData(string $value, string $store_id)
    {
        $response = ['success' => false];

        try {
            $ids = explode(",", $value);
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addCategoriesFilter(['in' => $ids]);
            $collection->addStoreFilter(intval($store_id));
            $collection->addAttributeToSort("position", "asc");

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
        /** @var ProductInterceptor */
        foreach($collection as $product) {
            $product_ = $this->filterProductResult($product, [
               'entity_id',
               'sku',
               'status',
               'quantity_and_stock_status',
               'name',
               'url_key',
               'rgb',
            ]);
            if (isset($product_['rgb'])) {
                list ($red, $green, $blue) = explode(',', $product_['rgb']);
                $product_['red'] = $red;
                $product_['green'] = $green;
                $product_['blue'] = $blue;
            }
            // $product_['class_name'] = $product::class;
            $product_['product_swatch_image'] = $this->_productImageHelper->init($product, 'product_swatch_image')
                ->setImageFile($product->getSwatchImage())
                ->resize(160)
                ->getUrl();
            $product_['product_swatch_image_2x'] = $this->_productImageHelper->init($product, 'product_swatch_image_2x')
                ->setImageFile($product->getSwatchImage())
                ->resize(420)
                ->getUrl();
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

    private function filterProductResult(ProductInterceptor $product, array $allowedKeys): array
    {
        $productData = $product->getData();
        if (empty($allowedKeys)) {
            return $productData;
        }
        $product_ = [];
        foreach($allowedKeys as $allowed) {
            if (isset($product[$allowed])) {
                $product_[$allowed] = $product[$allowed];
            }
        }
        return $product_;
    }
}
