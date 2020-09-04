<?php
declare(strict_types=1);

namespace Mirasvit\SearchExtended\Index\Magento\Review;

use Mirasvit\Search\Index\AbstractInstantProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Review\Model\Review;

class InstantProvider extends AbstractInstantProvider
{
    public function getItems(int $storeId, int $limit): array
    {
        $items = [];

        foreach ($this->getCollection($limit) as $model) {
            $items[] = $this->mapItem($model, $storeId);
        }

        return $items;
    }

    /**
     * @param object $model
     */
    private function mapItem($model, int $storeId): array
    {
        return [
            'title'     => $model->getTitle(),
            'detail'    => $model->getDetail(),
            'url'       => $model->getProductUrl($model->getEntityPkValue(), $model->getStoreId()),
        ];
    }

    public function getSize(int $storeId): int
    {
        return $this->getCollection(0)->getSize();
    }

    public function map(array $documentData, int $storeId): array
    {
        foreach ($documentData as $entityId => $itm) {
            $om = ObjectManager::getInstance();
            $entity = $om->create(Review::class)->load($entityId);
            $map = $this->mapItem($entity, $storeId);
            $documentData[$entityId][self::INSTANT_KEY] = $map;
        }

        return $documentData;
    }
}
