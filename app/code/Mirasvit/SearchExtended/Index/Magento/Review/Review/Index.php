<?php

namespace Mirasvit\SearchExtended\Index\Magento\Review\Review;

use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Mirasvit\Search\Model\Index\IndexerFactory;
use Mirasvit\Search\Model\Index\SearcherFactory;

class Index extends AbstractIndex
{
    /**
     * @var ReviewCollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        ReviewCollectionFactory $collectionFactory,
        Context $context,
        $dataMappers = []
    ) {
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $dataMappers);
    }

    /**
     * Index name (for backend)
     *
     * @return string
     */
    public function getName()
    {
        return 'Magento / Review';
    }

    /**
     * Unique index code (used in layout files and di.xml)
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'magento_review_review';
    }

    /**
     * Possible searchable attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            'title'    => __('Summary'),
            'detail'   => __('Review'),
            'nickname' => __('Nickname'),
        ];
    }

    /**
     * Primary key (database table column) for searchable content entities table
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return 'review_id';
    }

    /**
     * Collection of founded entities
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection()
    {
        $collection = $this->collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.review_id');

        return $collection;
    }

    /**
     * Collection of searchable entities for indexation
     *
     * @param int $storeId
     * @param array $entityIds
     * @param int $lastEntityId
     * @param int $limit
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
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

        return $collection;
    }
}
