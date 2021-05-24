<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AHT\TierPrice\Console\Command;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class ProductAttributesCleanUp
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveTierPrice extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \AHT\TierPrice\Block\TierPrice $tierPrice
     */
    protected $tierPrice;


    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource,
        \AHT\TierPrice\Block\TierPrice $tierPrice
    ) {
        $this->tierPrice = $tierPrice;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('tierprice:remove');
        $this->setDescription('Remove tier price from csv file ...');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Remove tier price from csv ...");
        $this->tierPrice->removeTierPrice();
    }
}
