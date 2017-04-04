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
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Data\ProductOfCategoriesRuleData;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\ProductOfCategoriesRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class ProductOfCategoriesRuleTest extends TestCase
{
    public function testSingleProductAndSingleCategory()
    {
        $rule = new ProductOfCategoriesRule([1]);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $collection = new RuleDataCollection([
            new ProductOfCategoriesRuleData([
                1 => ['SW1'],
            ]),
        ]);

        $this->assertTrue($rule->match($cart, $context, $collection));
    }

    public function testMultipleProductsWithSingleCategory()
    {
        $rule = new ProductOfCategoriesRule([1]);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $collection = new RuleDataCollection([
            new ProductOfCategoriesRuleData([
                1 => ['SW1', 'SW2'],
                2 => ['SW3'],
            ]),
        ]);

        $this->assertTrue($rule->match($cart, $context, $collection));
    }

    public function testMultipleCategories()
    {
        $rule = new ProductOfCategoriesRule([2, 3]);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $collection = new RuleDataCollection([
            new ProductOfCategoriesRuleData([
                1 => ['SW1', 'SW2'],
                2 => ['SW3'],
            ]),
        ]);

        $this->assertTrue($rule->match($cart, $context, $collection));
    }

    public function testNotMatch()
    {
        $rule = new ProductOfCategoriesRule([2, 3]);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $collection = new RuleDataCollection([
            new ProductOfCategoriesRuleData([
                4 => ['SW1', 'SW2'],
                5 => ['SW3'],
            ]),
        ]);

        $this->assertFalse($rule->match($cart, $context, $collection));
    }

    public function testMissingDataObject()
    {
        $rule = new ProductOfCategoriesRule([2, 3]);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse($rule->match($cart, $context, new RuleDataCollection([])));
    }
}
