<?php

namespace Mirasvit\SearchExtended\Index\Magento\Review;

use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Mirasvit\Search\Model\Index\IndexerFactory;
use Mirasvit\Search\Model\Index\SearcherFactory;
use Magento\Framework\Data\Collection;

class Index extends AbstractIndex
{
    private $collectionFactory;

    public function __construct(
        ReviewCollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    /**
     * Index name (for backend)
     */
    public function getName(): string
    {
        return 'Magento / Review';
    }

    /**
     * Unique index code (used in layout files and di.xml)
     */
    public function getIdentifier(): string
    {
        return 'magento_review';
    }

    /**
     * Possible searchable attributes
     */
    public function getAttributes(): array
    {
        return [
            'title'    => __('Summary'),
            'detail'   => __('Review'),
            'nickname' => __('Nickname'),
        ];
    }

    /**
     * Primary key (database table column) for searchable content entities table
     */
    public function getPrimaryKey(): string
    {
        return 'review_id';
    }

    /**
     * Collection of founded entities
     */
    public function buildSearchCollection(): Collection
    {
        $collection = $this->collectionFactory->create();
        $this->context->getSearcher()->joinMatches($collection, 'main_table.review_id');

        return $collection;
    }

    /**
     * Collection of searchable entities for indexation
     */
    public function getIndexableDocuments(int $storeId, array $entityIds = null, int $lastEntityId = null, int $limit = 100): array
    {
        $collection = $this->collectionFactory->create()
            ->addStatusFilter(Review::STATUS_APPROVED)
            ->addStoreFilter($storeId);

        if ($entityIds) {
            $collection->addFieldToFilter('main_table.review_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('main_table.review_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('main_table.review_id');

        return $collection->toArray()['items'];
    }
}
