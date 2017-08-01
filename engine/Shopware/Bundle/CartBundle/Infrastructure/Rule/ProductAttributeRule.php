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

namespace Shopware\Bundle\CartBundle\Infrastructure\Rule;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Rule\Match;
use Shopware\Bundle\CartBundle\Domain\Rule\Rule;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\Data\ProductAttributeRuleData;
use Shopware\Framework\Struct\StructCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class ProductAttributeRule extends Rule
{
    /**
     * @var string
     */
    protected $attribute;

    /**
     * @var mixed
     */
    protected $value;

    public function __construct(string $attribute, $value)
    {
        $this->attribute = $attribute;
        $this->value = $value;
    }

    public function match(
        CalculatedCart $calculatedCart,
        ShopContextInterface $context,
        StructCollection $collection
    ): Match {
        if (!$collection->has(ProductAttributeRuleData::class)) {
            return new Match(
                false,
                ['Product attribute data not loaded']
            );
        }

        /** @var ProductAttributeRuleData $data */
        $data = $collection->get(ProductAttributeRuleData::class);

        return new Match(
            $data->hasAttributeValue($this->attribute, $this->value),
            ['Product attribute value not matched']
        );
    }
}
