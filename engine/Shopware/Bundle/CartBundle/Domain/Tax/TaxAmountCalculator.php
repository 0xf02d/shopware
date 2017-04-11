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

namespace Shopware\Bundle\CartBundle\Domain\Tax;

use Shopware\Bundle\CartBundle\Domain\Price\PriceCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class TaxAmountCalculator implements TaxAmountCalculatorInterface
{
    const CALCULATION_HORIZONTAL = 'horizontal';
    const CALCULATION_VERTICAL = 'vertical';

    /**
     * @var PercentageTaxRuleBuilder
     */
    private $percentageTaxRuleBuilder;

    /**
     * @var TaxCalculator
     */
    private $taxCalculator;

    /**
     * @var TaxDetector
     */
    private $taxDetector;

    public function __construct(
        PercentageTaxRuleBuilder $percentageTaxRuleBuilder,
        TaxCalculator $taxCalculator,
        TaxDetector $taxDetector
    ) {
        $this->percentageTaxRuleBuilder = $percentageTaxRuleBuilder;
        $this->taxCalculator = $taxCalculator;
        $this->taxDetector = $taxDetector;
    }

    public function calculate(PriceCollection $priceCollection, ShopContextInterface $context): CalculatedTaxCollection
    {
        if ($this->taxDetector->isNetDelivery($context)) {
            return new CalculatedTaxCollection([]);
        }

        if ($context->getShop()->getTaxCalculation() === self::CALCULATION_VERTICAL) {
            return $priceCollection->getCalculatedTaxes();
        }

        $price = $priceCollection->getTotalPrice();

        $rules = $this->percentageTaxRuleBuilder->buildRules($price);

        switch (true) {
            case $this->taxDetector->useGross($context):
                return $this->taxCalculator->calculateGrossTaxes($price->getTotalPrice(), $rules);

            default:
                return $this->taxCalculator->calculateNetTaxes($price->getTotalPrice(), $rules);
        }
    }
}
