<?php
namespace Mirasvit\SearchExtended\Model\Index\Magento\Review\Review;

use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\IndexerFactory;
use Mirasvit\Search\Model\Index\SearcherFactory;

class Index extends AbstractIndex
{
    /**
     * @var ReviewCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param ReviewCollectionFactory $collectionFactory
     * @param IndexerFactory          $indexer
     * @param SearcherFactory         $searcher
     */
    public function __construct(
        ReviewCollectionFactory $collectionFactory,
        IndexerFactory $indexer,
        SearcherFactory $searcher
    ) {
        $this->collectionFactory = $collectionFactory;

        parent::__construct($indexer, $searcher);
    }

    /**
     * Index name (for backend)
     *
     * @return string
     */
    public function getName()
    {
        return __('Review')->__toString();
    }

    /**
     * Index scope name (for backend)
     *
     * @return string
     */
    public function getGroup()
    {
        return __('Magento')->__toString();
    }

    /**
     * Unique index code (used in layout files and di.xml)
     *
     * @return string
     */
    public function getCode()
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
    protected function buildSearchCollection()
    {
        $collection = $this->collectionFactory->create();

        $this->searcher->joinMatches($collection, 'main_table.review_id');

        return $collection;
    }

    /**
     * Collection of searchable entities for indexation
     *
     * @param int   $storeId
     * @param array $entityIds
     * @param int   $lastEntityId
     * @param int   $limit
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
