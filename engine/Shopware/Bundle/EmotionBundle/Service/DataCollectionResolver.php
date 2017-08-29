<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\EmotionBundle\Service;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\SearchBundle\BatchProductSearch;
use Shopware\Context\Struct\ShopContext;

class DataCollectionResolver implements DataCollectionResolverInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Media\MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var BatchProductSearch
     */
    private $batchProductSearch;

    /**
     * @param BatchProductSearch                                            $batchProductSearch
     * @param Connection                                                    $connection
     * @param \Shopware\Bundle\StoreFrontBundle\Media\MediaServiceInterface $mediaService
     */
    public function __construct(
        BatchProductSearch $batchProductSearch,
        Connection $connection,
        \Shopware\Bundle\StoreFrontBundle\Media\MediaServiceInterface $mediaService
    ) {
        $this->batchProductSearch = $batchProductSearch;
        $this->connection = $connection;
        $this->mediaService = $mediaService;
    }

    /**
     * @param PrepareDataCollection                                          $prepareDataCollection
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return ResolvedDataCollection
     */
    public function resolve(PrepareDataCollection $prepareDataCollection, ShopContext $context)
    {
        // resolve prepared data
        $batchResult = $this->resolveBatchRequest($prepareDataCollection, $context);
        $mediaList = $this->resolveMedia($prepareDataCollection, $context);

        $resolvedDataCollection = new ResolvedDataCollection();
        $resolvedDataCollection->setBatchResult($batchResult);
        $resolvedDataCollection->setMediaList($mediaList);

        return $resolvedDataCollection;
    }

    /**
     * @param PrepareDataCollection                                          $prepareDataCollection
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return array
     */
    private function resolveMedia(PrepareDataCollection $prepareDataCollection, ShopContext $context)
    {
        $mediaIds = $this->convertMediaPathsToIds($prepareDataCollection->getMediaPathList());
        $mediaIds = array_merge($prepareDataCollection->getMediaIdList(), $mediaIds);

        if (count($mediaIds) === 0) {
            return [];
        }

        $mediaIds = array_keys(array_flip($mediaIds));
        $mediaIds = array_map('intval', $mediaIds);

        $mediaList = $this->mediaService->getList($mediaIds, $context);

        $medias = [];
        foreach ($mediaList as $media) {
            $medias[$media->getId()] = $media;
            $medias[$media->getPath()] = $media;
        }

        return $medias;
    }

    /**
     * @param string[] $mediaPaths
     *
     * @return int[]
     */
    private function convertMediaPathsToIds(array $mediaPaths = [])
    {
        return $this->connection->createQueryBuilder()
            ->select(['id'])
            ->from('s_media')
            ->where('path in (:paths)')
            ->setParameter('paths', $mediaPaths, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param PrepareDataCollection                                          $prepareDataCollection
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return \Shopware\Bundle\SearchBundle\BatchProductNumberSearchResult
     */
    private function resolveBatchRequest(PrepareDataCollection $prepareDataCollection, ShopContext $context)
    {
        $request = $prepareDataCollection->getBatchRequest();

        return $this->batchProductSearch->search($request, $context);
    }
}
