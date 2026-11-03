<?php
declare(strict_types=1);

namespace Mirasvit\SearchExtended\Test\Unit\Index\Magento\Review;

use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Review\Model\Review;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Model\Index\Context;
use Mirasvit\Search\Model\Index\Indexer;
use Mirasvit\Search\Model\Index\Searcher;
use Mirasvit\SearchExtended\Index\Magento\Review\Index;
use PHPUnit\Framework\TestCase;

/**
 * The review search index is rebuilt only on a full reindex, so the storefront search
 * collection must itself keep out reviews that are no longer approved or no longer
 * assigned to the current store.
 */
class IndexTest extends TestCase
{
    public function testSearchCollectionShowsOnlyApprovedReviewsOfCurrentStore(): void
    {
        $collection = $this->createMock(ReviewCollection::class);
        $collection->expects($this->once())
            ->method('addStatusFilter')
            ->with(Review::STATUS_APPROVED)
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('addStoreFilter')
            ->with(3)
            ->willReturnSelf();

        $collectionFactory = $this->createStub(ReviewCollectionFactory::class);
        $collectionFactory->method('create')->willReturn($collection);

        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn('3');
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $searcher = $this->createMock(Searcher::class);
        $searcher->expects($this->once())
            ->method('joinMatches')
            ->with($collection, 'main_table.review_id');
        $indexer = $this->createStub(Indexer::class);

        $context = $this->createStub(Context::class);
        $context->method('getInstance')->willReturnSelf();
        $context->method('getSearcher')->willReturn($searcher);
        $context->method('getIndexer')->willReturn($indexer);
        $context->method('getStoreManager')->willReturn($storeManager);

        $index = new Index($collectionFactory, $context);

        $this->assertSame($collection, $index->buildSearchCollection());
    }
}
