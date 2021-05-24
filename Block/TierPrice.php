<?php

namespace AHT\TierPrice\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;

class TierPrice extends Template
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
    protected $product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */ 
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    protected $configurable;

    protected $productRepository;
    /**
     * TierPrice constructor.
     * @param Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \AHT\TierPrice\Model\CsvImportHandler $csv,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        ManagerInterface $messageManager,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->resource = $resource;
        $this->csv = $csv;
        $this->connection = $resource->getConnection();
        $this->product = $product;
		$this->productCollectionFactory = $productCollectionFactory;
        $this->configurable = $configurable;
        parent::__construct($context, $data);
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

    public function arrayTier($arrTier,$qty,$value,$id) {
        $arrTier[] = [
            'entity_id' => $id,
            'qty'  => $qty,
            'value'  => $value
        ];
        return $arrTier;
    }

    /**     
     * Add tier price 
    */
    public function addTierPrice()
    {
        $filename = 'app/code/AHT/TierPrice/File/Sample/catalog_product_update_tier_price.csv';
        $tableName = 'catalog_product_entity_tier_price';
        $data = $this->showData($filename);
        $collection = $this->productCollectionFactory->create();
        $arrTier = [];

        foreach($collection as $item) { // foreach all product collection
            if(array_key_exists($item->getSku(),$data)) { //check sku exists
                $sku = $item->getSku();
                foreach($data[$sku] as $val) { // foreach in sku array
                    $id = $item->getId();
                    $arrTier = $this->arrayTier($arrTier,$val['qty'],$val['price'],$id); // add into array
                }
            }
        }
        $this->updateMultiple($tableName,$arrTier); 
        $this->insertMultiple($tableName,$arrTier);
    }

    public function removeTierPrice()
    {
        $filename = 'app/code/AHT/TierPrice/File/Sample/catalog_product_remove_tier_price.csv';
        $tableName = 'catalog_product_entity_tier_price';
        $collection = $this->productCollectionFactory->create();
        $data = $this->showData($filename);
        unset($data[0]); 
        $id = 0;
        foreach($collection as $item) { // foreach all product collection
            if(array_key_exists($item->getSku(),$data)) { //check sku exists
                $id = $item->getIdBySku($item['sku']); // entity_id need to insert or update data
                $this->remove($tableName,$id);
            }
        }
    }
}


