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

namespace Shopware\Bundle\StoreFrontBundle\Product;

use Shopware\Category\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Category\CategoryServiceInterface;
use Shopware\Context\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Media\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Price\CheapestPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Price\GraduatedPricesServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Price\PriceCalculationServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Vote\VoteServiceInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ListProductService implements ListProductServiceInterface
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Product\ProductGateway
     */
    private $productGateway;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Media\MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Price\CheapestPriceServiceInterface
     */
    private $cheapestPriceService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Price\GraduatedPricesServiceInterface
     */
    private $graduatedPricesService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Price\PriceCalculationServiceInterface
     */
    private $priceCalculationService;

    /**
     * @var VoteServiceInterface
     */
    private $voteService;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Category\CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(
        ProductGateway $productGateway,
        GraduatedPricesServiceInterface $graduatedPricesService,
        CheapestPriceServiceInterface $cheapestPriceService,
        PriceCalculationServiceInterface $priceCalculationService,
        MediaServiceInterface $mediaService,
        VoteServiceInterface $voteService,
        CategoryServiceInterface $categoryService,
        \Shopware_Components_Config $config
    ) {
        $this->productGateway = $productGateway;
        $this->graduatedPricesService = $graduatedPricesService;
        $this->cheapestPriceService = $cheapestPriceService;
        $this->priceCalculationService = $priceCalculationService;
        $this->mediaService = $mediaService;
        $this->voteService = $voteService;
        $this->categoryService = $categoryService;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, ShopContext $context)
    {
        // faster replacement for array_unique()
        // see http://stackoverflow.com/questions/8321620/array-unique-vs-array-flip
        $numbers = array_keys(array_flip($numbers));

        $products = $this->productGateway->getList($numbers, $context);

        $covers = $this->mediaService->getCovers($products, $context);

        $graduatedPrices = $this->graduatedPricesService->getList($products, $context);

        $cheapestPrices = $this->cheapestPriceService->getList($products, $context);

        $voteAverages = $this->voteService->getAverages($products, $context);

        $categories = $this->categoryService->getProductsCategories($products, $context);

        $result = [];
        foreach ($numbers as $number) {
            if (!array_key_exists($number, $products)) {
                continue;
            }
            $product = $products[$number];

            if (isset($covers[$number])) {
                $product->setCover($covers[$number]);
            }

            if (isset($graduatedPrices[$number])) {
                $product->setPriceRules($graduatedPrices[$number]);
            }

            if (isset($cheapestPrices[$number])) {
                $product->setCheapestPriceRule($cheapestPrices[$number]);
            }

            if (isset($voteAverages[$number])) {
                $product->setVoteAverage($voteAverages[$number]);
            }

            if (isset($categories[$number])) {
                $product->setCategories($categories[$number]);
            }

            $this->priceCalculationService->calculateProduct($product, $context);

            if (!$this->isProductValid($product, $context)) {
                continue;
            }

            $product->setListingPrice($product->getCheapestUnitPrice());
            $product->setDisplayFromPrice((count($product->getPrices()) > 1 || $product->hasDifferentPrices()));
            $product->setAllowBuyInListing($this->allowBuyInListing($product));
            if ($this->config->get('calculateCheapestPriceWithMinPurchase')) {
                $product->setListingPrice($product->getCheapestPrice());
            }
            $result[$number] = $product;
        }

        return $result;
    }

    /**
     * Checks if the provided product is allowed to display in the store front for
     * the provided context.
     *
     * @param ListProduct          $product
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return bool
     */
    private function isProductValid(ListProduct $product, ShopContext $context)
    {
        if (in_array($context->getCurrentCustomerGroup()->getId(), $product->getBlockedCustomerGroupUuids())) {
            return false;
        }

        $prices = $product->getPrices();
        if (empty($prices)) {
            return false;
        }

        if ($this->config->get('hideNoInStock') && $product->isCloseouts() && !$product->hasAvailableVariant()) {
            return false;
        }

        $ids = array_map(function (Category $category) {
            return $category->getId();
        }, $product->getCategories());

        return in_array($context->getShop()->getCategory()->getId(), $ids);
    }

    /**
     * @param ListProduct $product
     *
     * @return bool
     */
    private function allowBuyInListing(ListProduct $product)
    {
        return !$product->hasConfigurator()
            && $product->isAvailable()
            && $product->getUnit()->getMinPurchase() <= 1
            && !$product->displayFromPrice();
    }
}
