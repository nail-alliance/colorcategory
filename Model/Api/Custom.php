<?php
/**
 * Custom API
 * Select product by category for a single store id.
 * * @author Fabian Nino <fabian@nailalliance.com>
 * @copyright Copyright (c) 2022, Nail Alliance
 */

namespace Nailalliance\Colorcategory\Model\Api;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Nailalliance\Colorcategory\Api\CustomInterface;

class Custom implements CustomInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var CollectionFactory */
    protected $_productCollectionFactory;

    /** @var Image */
    protected $_productImageHelper;

    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $productCollectionFactory,
        Image $image
    ) {
        $this->logger = $logger;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productImageHelper = $image;
    }

    /**
     * @inheritdoc
     */
    public function getData(string $value, string $store_id)
    {
        try {
            $ids = explode(",", $value);
            $collection = $this->_productCollectionFactory->create();

            // 1. Select only what we need. Added 'swatch_image' to prevent lazy-loading.
            $collection->addAttributeToSelect(['sku', 'name', 'url_key', 'rgb', 'swatch_image', 'quantity_and_stock_status']);

            // 2. Filter by Enabled Status at the Database level
            $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);

            $collection->addCategoriesFilter(['in' => $ids]);
            $collection->addStoreFilter((int)$store_id);
            $collection->addAttributeToSort("position", "asc");

            // 3. Join Stock Data to prevent querying stock per-product in the loop
            $collection->joinField(
                'is_in_stock',
                'cataloginventory_stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );

            $pageSize = 200;
            $collection->setPageSize($pageSize);
            $lastPageNumber = $collection->getLastPageNumber();

            $products = [];

            for ($currentPage = 1; $currentPage <= $lastPageNumber; $currentPage++) {
                $collection->setCurPage($currentPage);

                /** @var Product $product */
                foreach ($collection as $product) {
                    $products[] = $this->parseProduct($product);
                }

                $collection->clear();
            }

            return $products;

        } catch (Exception $e) {
            $this->logger->error('Colorcategory API Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Extracts and formats product data efficiently
     */
    private function parseProduct(Product $product): array
    {
        $productData = [
            'entity_id' => $product->getId(),
            'sku'       => $product->getSku(),
            'status'    => $product->getStatus(),
            'name'      => $product->getName(),
            'url_key'   => $product->getUrlKey(),
            'rgb'       => $product->getData('rgb'),
            'in_stock'  => (bool)$product->getData('is_in_stock')
        ];

        // Format RGB string into array keys safely
        if (!empty($productData['rgb'])) {
            $rgbParts = explode(',', $productData['rgb']);
            if (count($rgbParts) === 3) {
                $productData['red']   = trim($rgbParts[0]);
                $productData['green'] = trim($rgbParts[1]);
                $productData['blue']  = trim($rgbParts[2]);
            }
        }

        // Process images only if a swatch image actually exists
        $swatchImage = $product->getData('swatch_image');
        if ($swatchImage && $swatchImage !== 'no_selection') {
            $productData['product_swatch_image'] = $this->_productImageHelper
                ->init($product, 'product_swatch_image')
                ->setImageFile($swatchImage)
                ->resize(200)
                ->getUrl();

            $productData['product_swatch_image_2x'] = $this->_productImageHelper
                ->init($product, 'product_swatch_image_2x')
                ->setImageFile($swatchImage)
                ->resize(400)
                ->getUrl();
        } else {
            $productData['product_swatch_image'] = null;
            $productData['product_swatch_image_2x'] = null;
        }

        return $productData;
    }
}