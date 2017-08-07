<?php

namespace Shopware\Product\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class ProductExtension extends Extension
{


    /**
     * Loads a specific configuration.
     *
     * @param array $configs              An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__));

        $loader->load('services.xml');


        $loader->load('../Writer/Field/product-fields.xml');
//        $loader->load('../Writer/Field/product-also-bought-ro-fields.xml');
//        $loader->load('../Writer/Field/product-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-avoid-customergroup-fields.xml');
//        $loader->load('../Writer/Field/product-category-fields.xml');
//        $loader->load('../Writer/Field/product-category-ro-fields.xml');
//        $loader->load('../Writer/Field/product-category-seo-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-dependency-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-group-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-group-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-option-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-option-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-option-relation-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-price-variation-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-set-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-set-group-relation-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-set-option-relation-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-template-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-template-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-template-price-fields.xml');
//        $loader->load('../Writer/Field/product-configurator-template-price-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-detail-fields.xml');
//        $loader->load('../Writer/Field/product-download-fields.xml');
//        $loader->load('../Writer/Field/product-download-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-esd-fields.xml');
//        $loader->load('../Writer/Field/product-esd-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-esd-serial-fields.xml');
//        $loader->load('../Writer/Field/product-img-fields.xml');
//        $loader->load('../Writer/Field/product-img-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-img-mapping-fields.xml');
//        $loader->load('../Writer/Field/product-img-mapping-rule-fields.xml');
//        $loader->load('../Writer/Field/product-information-fields.xml');
//        $loader->load('../Writer/Field/product-information-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-notification-fields.xml');
//        $loader->load('../Writer/Field/product-price-fields.xml');
//        $loader->load('../Writer/Field/product-price-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-relationship-fields.xml');
//        $loader->load('../Writer/Field/product-similar-fields.xml');
//        $loader->load('../Writer/Field/product-similar-shown-ro-fields.xml');
//        $loader->load('../Writer/Field/product-supplier-fields.xml');
//        $loader->load('../Writer/Field/product-supplier-attribute-fields.xml');
//        $loader->load('../Writer/Field/product-top-seller-ro-fields.xml');
//        $loader->load('../Writer/Field/product-translation-fields.xml');
//        $loader->load('../Writer/Field/product-vote-fields.xml');
    }
}