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
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\CurrencyRule;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Currency\Currency;

class CurrencyRuleTest extends TestCase
{
    public function testMatch()
    {
        $rule = new CurrencyRule([1]);

        $cart = $this->createMock(CalculatedCart::class);

        $currency = new Currency();
        $currency->setId(1);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getCurrency')
            ->will($this->returnValue($currency));

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testNotMatch()
    {
        $rule = new CurrencyRule([2, 3, 1]);

        $cart = $this->createMock(CalculatedCart::class);

        $currency = new Currency();
        $currency->setId(5);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getCurrency')
            ->will($this->returnValue($currency));

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testMultipleCurrencies()
    {
        $rule = new CurrencyRule([2, 3, 1]);

        $cart = $this->createMock(CalculatedCart::class);

        $currency = new Currency();
        $currency->setId(3);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getCurrency')
            ->will($this->returnValue($currency));

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }
}
