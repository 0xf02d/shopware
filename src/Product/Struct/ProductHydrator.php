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

namespace Shopware\Product\Struct;

use Shopware\Framework\Struct\Hydrator;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductHydrator extends Hydrator
{
//    /**
//     * @var ManufacturerHydrator
//     */
//    private $manufacturerHydrator;
//
//    /**
//     * @var TaxHydrator
//     */
//    private $taxHydrator;
//
//    /**
//     * @var \Shopware\Bundle\StoreFrontBundle\Unit\UnitHydrator
//     */
//    private $unitHydrator;
//
//    /**
//     * @var EsdHydrator
//     */
//    private $esdHydrator;
//
//    /**
//     * @var \Shopware_Components_Config
//     */
//    private $config;
//
//    public function __construct(
//        ManufacturerHydrator $manufacturerHydrator,
//        TaxHydrator $taxHydrator,
//        UnitHydrator $unitHydrator,
//        EsdHydrator $esdHydrator,
//        \Shopware_Components_Config $config
//    ) {
//        $this->manufacturerHydrator = $manufacturerHydrator;
//        $this->taxHydrator = $taxHydrator;
//        $this->unitHydrator = $unitHydrator;
//        $this->esdHydrator = $esdHydrator;
//        $this->config = $config;
//    }

    public function hydrate(array $data): Product
    {
        $product = new Product();
        $product->setId((int) $data['__product_id']);
        $product->setVariantId((int) $data['__variant_id']);
        $product->setNumber($data['__variant_ordernumber']);

        return $this->assignData($product, $data);
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getProductTranslation(array $data): array
    {
        $translation = $this->getTranslation($data, '__product', [], null, false);
        $variant = $this->getTranslation($data, '__variant', [], null, false);
        $translation = array_merge($translation, $variant);

        if (empty($translation)) {
            return $translation;
        }

        $result = $this->convertArrayKeys($translation, [
            'metaTitle' => '__product_metaTitle',
            'txtArtikel' => '__product_name',
            'txtshortdescription' => '__product_description',
            'txtlangbeschreibung' => '__product_description_long',
            'txtzusatztxt' => '__variant_additionaltext',
            'txtkeywords' => '__product_keywords',
            'txtpackunit' => '__unit_packunit',
        ]);

        return $result;
    }

    private function assignData(Product $product, array $data): Product
    {
        $translation = $this->getProductTranslation($data);
        $data = array_merge($data, $translation);

        $this->assignProductData($product, $data);

//        $product->setTax(
//            $this->taxHydrator->hydrate($data)
//        );

//        $this->assignPriceGroupData($product, $data);

//        if ($data['__product_supplierID']) {
//            $product->setManufacturer(
//                $this->manufacturerHydrator->hydrate($data)
//            );
//        }
//
//        if ($data['__esd_id']) {
//            $product->setEsd(
//                $this->esdHydrator->hydrate($data)
//            );
//        }

//        $product->setUnit(
//            $this->unitHydrator->hydrate($data)
//        );

//        if (!empty($data['__productAttribute_id'])) {
//            $this->assignAttributeData($product, $data);
//        }

        $today = new \DateTime();
        $diff = $today->diff($product->getCreatedAt());
//        $marker = (int) $this->config->get('markAsNew');
        $marker = 10;

        $product->setIsNew(
            $diff->days <= $marker || $product->getCreatedAt() > $today
        );

        $product->setComingSoon(
            $product->getReleaseDate() && $product->getReleaseDate() > $today
        );

//        $marker = $this->config->get('markAsTopSeller');
        $marker = 10;
        $product->setIsTopSeller($product->getSales() >= $marker);

        return $product;
    }

//    /**
//     * @param ListProduct $product
//     * @param array       $data
//     */
//    private function assignPriceGroupData(ListProduct $product, array $data)
//    {
//        if (!empty($data['__priceGroup_id'])) {
//            $product->setPriceGroup(new PriceGroup());
//            $product->getPriceGroup()->setId((int) $data['__priceGroup_id']);
//            $product->getPriceGroup()->setName($data['__priceGroup_description']);
//        }
//    }

    private function assignProductData(Product $product, array $data): void
    {
        $product->setName($data['__product_name']);
        $product->setShortDescription($data['__product_description']);
        $product->setLongDescription($data['__product_description_long']);
        $product->setCloseouts((bool) ($data['__product_laststock']));
        $product->setMetaTitle($data['__product_metaTitle']);
        $product->setHasProperties($data['__product_filtergroupID'] > 0);
        $product->setHighlight((bool) ($data['__product_topseller']));
        $product->setAllowsNotification((bool) ($data['__product_notification']));
        $product->setKeywords($data['__product_keywords']);
        $product->setTemplate($data['__product_template']);
        $product->setHasConfigurator(($data['__product_configurator_set_id'] > 0));
        $product->setHasEsd((bool) $data['__product_has_esd']);
        $product->setIsPriceGroupActive((bool) $data['__product_pricegroupActive']);
        $product->setSales((int) $data['__topSeller_sales']);
        $product->setShippingFree((bool) ($data['__variant_shippingfree']));
        $product->setStock((int) $data['__variant_instock']);
        $product->setManufacturerNumber($data['__variant_suppliernumber']);
        $product->setMainVariantId((int) $data['__product_main_detail_id']);

        if ($data['__variant_shippingtime']) {
            $product->setShippingTime($data['__variant_shippingtime']);
        } elseif ($data['__product_shippingtime']) {
            $product->setShippingTime($data['__product_shippingtime']);
        }

        if ($data['__variant_releasedate']) {
            $product->setReleaseDate(
                new \DateTime($data['__variant_releasedate'])
            );
        }
        if ($data['__product_datum']) {
            $product->setCreatedAt(
                new \DateTime($data['__product_datum'])
            );
        }

        $product->setAdditional($data['__variant_additionaltext']);
        $product->setEan($data['__variant_ean']);
        $product->setHeight((float) $data['__variant_height']);
        $product->setLength((float) $data['__variant_length']);
        $product->setMinStock((int) $data['__variant_stockmin']);
        $product->setWeight((float) $data['__variant_weight']);
        $product->setWidth((float) $data['__variant_width']);

        $customerGroups = explode('|', $data['__product_blocked_customer_groups']);
        $customerGroups = array_filter($customerGroups);
        $product->setBlockedCustomerGroupIds($customerGroups);
        $product->setHasAvailableVariant($data['__product_has_available_variants'] > 0);

//        $product->setFallbackPriceCount($data['__product_fallback_price_count']);
//        if (array_key_exists('__product_custom_price_count', $data)) {
//            $product->setCustomerPriceCount($data['__product_custom_price_count']);
//        } else {
//            $product->setCustomerPriceCount($data['__product_fallback_price_count']);
//        }
    }

//    /**
//     * Iterates the attribute data and assigns the attribute struct to the product.
//     *
//     * @param ListProduct $product
//     * @param $data
//     */
//    private function assignAttributeData(ListProduct $product, array $data)
//    {
//        $translation = $this->getProductTranslation($data);
//        $translation = $this->extractFields('__attribute_', $translation);
//        $attributeData = $this->extractFields('__productAttribute_', $data);
//        $attributeData = array_merge($attributeData, $translation);
//        $attribute = new Attribute($attributeData);
//        $product->addAttribute('core', $attribute);
//    }

}
