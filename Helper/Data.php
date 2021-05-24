<?php
namespace AHT\TierPrice\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \AHT\TierPrice\Model\CsvImportHandler $csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\App\ResourceConnection $resource
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\ProductRepository $productRepository
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */ 
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    protected $configurable;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \AHT\TierPrice\Model\CsvImportHandler $csv,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        ManagerInterface $messageManager,
        ResourceConnection $resource
    ) {
        $this->messageManager = $messageManager;
        $this->resource = $resource;
        $this->csv = $csv;
        $this->connection = $resource->getConnection();
        $this->productFactory = $productFactory;
		$this->productCollectionFactory = $productCollectionFactory;
        $this->configurable = $configurable;
        parent::__construct($context);
    }
    public function showData($filename) 
    {
        $data = $this->csv->getDataCsv($filename);
        return $data;
    }

    public function insertMultiple($table, $data)
    {
        try {
            $tableName = $this->resource->getTableName($table);
            return $this->connection->insertMultiple($tableName, $data);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot insert data.'));
        }
    }

    public function updateMultiple($table, $data)
    {
        try {
            $tableName = $this->resource->getTableName($table);
            return $this->connection->insertOnDuplicate($tableName, $data);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot update data.'));
        }
    }

    public function remove($table, $id) 
    {
        try {
            $tableName = $this->resource->getTableName($table);
            return $this->connection->delete($tableName, ["entity_id = $id"]);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot remove data.'));
        }

    }

    public function addTierPrice()
    {
        $filename = 'app/code/AHT/TierPrice/File/Sample/catalog_product_update_tier_price.csv';
        $tableName = 'catalog_product_entity_tier_price';
        
        $data = $this->showData($filename);
        unset($data[0]); 
        $arrTier = [];

        foreach($data as $item) {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addFieldToFilter('sku',$item[0]);
            if($productCollection->getSize()) {
                $product = $this->productFactory->create();
                $id = $product->getIdBySku($item[0]);
                if($product->getTypeId() == 'configurable') {
                    $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                    foreach ($childProducts as $childProduct) {
                        $id = $childProduct->getId();
                        $arrTier[] = [
                            'entity_id' => $id,
                            'qty'  => $item[1],
                            'value'  => $item[2]
                        ];
                    }
                }
                $arrTier[] = [
                    'entity_id' => $id,
                    'qty'  => $item[1],
                    'value'  => $item[2]
                ];
            }
        }
        $this->updateMultiple($tableName,$arrTier); 

        $this->insertMultiple($tableName,$arrTier);
    }

    public function removeTierPrice()
    {
        $filename = 'app/code/AHT/TierPrice/File/Sample/catalog_product_remove_tier_price.csv';
        $tableName = 'catalog_product_entity_tier_price';
        
        $data = $this->showData($filename);
        unset($data[0]); 
        $id = 0;
        foreach($data as $item) {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addFieldToFilter('sku',$item[0]);
            if($productCollection->getSize()) {
                $product = $this->productFactory->create();
                $id = $product->getIdBySku($item[0]);
                if($product->getTypeId() == 'configurable') {
                    $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                    foreach ($childProducts as $childProduct) {
                        $id = $childProduct->getId();
                    }
                }
            }
        }
        $this->remove($tableName,$id);
    }
}