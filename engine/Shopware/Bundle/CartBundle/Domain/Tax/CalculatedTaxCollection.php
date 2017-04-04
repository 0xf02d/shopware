<?php
declare(strict_types=1);
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

use Shopware\Bundle\CartBundle\Domain\KeyCollection;

class CalculatedTaxCollection extends KeyCollection
{
    /**
     * @var CalculatedTax[]
     */
    protected $elements = [];

    public function add(CalculatedTax $calculatedTax): void
    {
        parent::doAdd($calculatedTax);
    }

    public function remove(float $taxRate): void
    {
        parent::doRemoveByKey((string) $taxRate);
    }

    public function removeElement(CalculatedTax $calculatedTax): void
    {
        parent::doRemoveByKey($this->getKey($calculatedTax));
    }

    public function exists(CalculatedTax $calculatedTax): bool
    {
        return parent::has($this->getKey($calculatedTax));
    }

    public function get(float $taxRate): ? CalculatedTax
    {
        $key = (string) $taxRate;

        if ($this->has($key)) {
            return $this->elements[$key];
        }

        return null;
    }

    /**
     * Returns the total calculated tax for this item
     *
     * @return float
     */
    public function getAmount(): float
    {
        $amounts = $this->map(
            function (CalculatedTax $calculatedTax) {
                return $calculatedTax->getTax();
            }
        );

        return array_sum($amounts);
    }

    public function merge(CalculatedTaxCollection $taxCollection): CalculatedTaxCollection
    {
        $new = new self($this->elements);

        /** @var CalculatedTax $calculatedTax */
        foreach ($taxCollection as $calculatedTax) {
            if (!$new->exists($calculatedTax)) {
                $new->add(clone $calculatedTax);
                continue;
            }

            $new->get($calculatedTax->getTaxRate())
                ->increment($calculatedTax);
        }

        return $new;
    }

    /**
     * @param CalculatedTax $element
     *
     * @return string
     */
    protected function getKey($element): string
    {
        return (string) $element->getTaxRate();
    }
}
