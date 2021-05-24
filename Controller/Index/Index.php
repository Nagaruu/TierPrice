<?php
namespace AHT\TierPrice\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
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

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
	) {
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context);
	}

	public function execute()
	{	
		$resultPage = $this->resultPageFactory->create();
		return $resultPage;
	}
}