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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Infrastructure\Validator\Rule;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\Data\ProductAttributeRuleData;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\ProductAttributeRule;
use Shopware\Framework\Struct\StructCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;

class ProductAttributeRuleTest extends TestCase
{
    public function testSingleAttribute(): void
    {
        $rule = new ProductAttributeRule('attr1', 1);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new StructCollection([
                ProductAttributeRuleData::class => new ProductAttributeRuleData([
                    'attr1' => [2, 3, 1],
                ]),
            ]))->matches()
        );
    }

    public function testMultipleAttributeData(): void
    {
        $rule = new ProductAttributeRule('attr1', 1);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new StructCollection([
                ProductAttributeRuleData::class => new ProductAttributeRuleData([
                    'attr2' => [2, 3, 1],
                    'attr3' => [2, 3, 1],
                    'attr1' => [2, 3, 1],
                ]),
            ]))->matches()
        );
    }

    public function testNotMatch(): void
    {
        $rule = new ProductAttributeRule('attr1', 10);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new StructCollection([
                ProductAttributeRuleData::class => new ProductAttributeRuleData([
                    'attr2' => [2, 3, 1],
                    'attr3' => [2, 3, 1],
                    'attr1' => [2, 3, 1],
                ]),
            ]))->matches()
        );
    }

    public function testWithoutMappedAttribute(): void
    {
        $rule = new ProductAttributeRule('attr2', 10);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new StructCollection([
                ProductAttributeRuleData::class => new ProductAttributeRuleData([
                    'attr3' => [2, 3, 1],
                    'attr1' => [2, 3, 1],
                ]),
            ]))->matches()
        );
    }

    public function testWithoutDataObject(): void
    {
        $rule = new ProductAttributeRule('attr1', 10);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new StructCollection())->matches()
        );
    }
}
