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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Cart;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCartGenerator;
use Shopware\Bundle\CartBundle\Domain\Cart\CartCalculator;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherProcessor;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;

class CartCalculatorTest extends TestCase
{
    public function testIterateAllProcessors(): void
    {
        $calculatedCart = $this->createMock(CalculatedCart::class);
        $generator = $this->createMock(CalculatedCartGenerator::class);
        $generator->expects($this->once())->method('create')->will($this->returnValue($calculatedCart));

        $productProcessor = $this->createMock(ProductProcessor::class);
        $productProcessor->expects($this->once())->method('process');

        $voucherProcessor = $this->createMock(VoucherProcessor::class);
        $voucherProcessor->expects($this->once())->method('process');

        $calculator = new CartCalculator(
            [$productProcessor, $voucherProcessor],
            [],
            [],
            $generator
        );

        $this->assertEquals(
            $calculatedCart,
            $calculator->calculate(CartContainer::createNew('test'), $this->createMock(ShopContext::class))
        );
    }
}
