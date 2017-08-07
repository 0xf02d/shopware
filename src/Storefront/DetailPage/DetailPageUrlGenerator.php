<?php

namespace Shopware\Storefront\DetailPage;

use Cocur\Slugify\SlugifyInterface;
use Shopware\Context\TranslationContext;
use Shopware\Framework\Routing\Router;
use Shopware\Product\Struct\ProductIdentity;
use Shopware\Search\Condition\CanonicalCondition;
use Shopware\Search\Condition\ForeignKeyCondition;
use Shopware\Search\Condition\MainVariantCondition;
use Shopware\Search\Condition\NameCondition;
use Shopware\SeoUrl\Gateway\SeoUrlRepository;
use Shopware\SeoUrl\Generator\SeoUrlGeneratorInterface;
use Shopware\SeoUrl\Struct\SeoUrl;
use Shopware\Product\Gateway\ProductRepository;
use Shopware\Search\Condition\ActiveCondition;
use Shopware\Search\Condition\ShopCondition;
use Shopware\Search\Criteria;

class DetailPageUrlGenerator implements SeoUrlGeneratorInterface
{
    const ROUTE_NAME = 'detail_page';

    /**
     * @var ProductRepository
     */
    private $repository;

    /**
     * @var SlugifyInterface
     */
    private $slugify;

    /**
     * @var Router
     */
    private $generator;

    /**
     * @var SeoUrlRepository
     */
    private $seoUrlRepository;

    public function __construct(
        ProductRepository $repository,
        SlugifyInterface $slugify,
        Router $generator,
        SeoUrlRepository $seoUrlRepository
    ) {
        $this->repository = $repository;
        $this->slugify = $slugify;
        $this->generator = $generator;
        $this->seoUrlRepository = $seoUrlRepository;
    }

    public function fetch(int $shopId, TranslationContext $context, int $offset, int $limit): array
    {
        $criteria = new Criteria();
        $criteria->offset($offset);
        $criteria->limit($limit);

        $criteria->addCondition(new ShopCondition([$shopId]));
        $criteria->addCondition(new ActiveCondition(true));
        $criteria->addCondition(new MainVariantCondition());

        $result = $this->repository->search($criteria, $context);

        $products = $this->repository->read($result->getNumbers(), $context, ProductRepository::FETCH_MINIMAL);

        $criteria = new Criteria();
        $criteria->addCondition(new CanonicalCondition(true));
        $criteria->addCondition(new ForeignKeyCondition($products->getProductIds()));
        $criteria->addCondition(new NameCondition([self::ROUTE_NAME]));
        $criteria->addCondition(new ShopCondition([$shopId]));
        $existingCanonicals = $this->seoUrlRepository->search($criteria, $context);

        $routes = [];
        /** @var ProductIdentity $identity */
        foreach ($result as $identity) {
            if (!$product = $products->get($identity->getNumber())) {
                continue;
            }

            $pathInfo = $this->generator->generate(self::ROUTE_NAME, ['number' => $identity->getNumber()]);
            $url = $this->slugify->slugify($product->getName()) . '/' . $this->slugify->slugify($product->getNumber());

            if (!$url || !$pathInfo) {
                continue;
            }

            $routes[] = new SeoUrl(
                null,
                $shopId,
                self::ROUTE_NAME,
                $identity->getId(),
                $pathInfo,
                $url,
                new \DateTime(),
                !$existingCanonicals->hasPathInfo($pathInfo)
            );
        }

        return $routes;
    }

    public function getName(): string
    {
        return self::ROUTE_NAME;
    }
}