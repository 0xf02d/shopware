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

namespace Shopware\CartBridge\Voucher;

use Shopware\Cart\Cart\Struct\CalculatedCart;
use Shopware\Cart\Cart\Struct\CartContainer;
use Shopware\Cart\Cart\CartProcessorInterface;
use Shopware\Cart\Cart\ProcessorCart;
use Shopware\Cart\Error\VoucherNotFoundError;
use Shopware\Cart\LineItem\Discount;
use Shopware\Cart\LineItem\LineItemInterface;
use Shopware\Cart\Price\PercentagePriceCalculator;
use Shopware\Cart\Price\PriceCalculator;
use Shopware\Cart\Price\Struct\PriceDefinition;
use Shopware\Cart\Tax\PercentageTaxRuleBuilder;
use Shopware\CartBridge\Voucher\Struct\VoucherData;
use Shopware\Context\Struct\ShopContext;
use Shopware\Framework\Struct\StructCollection;

class VoucherProcessor implements CartProcessorInterface
{
    const TYPE_VOUCHER = 'voucher';

    /**
     * @var VoucherCalculator
     */
    private $calculator;

    public function __construct(VoucherCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function process(
        CartContainer $cartContainer,
        CalculatedCart $calculatedCart,
        StructCollection $dataCollection,
        ShopContext $context
    ): void {
        $lineItems = $cartContainer->getLineItems()->filterType(self::TYPE_VOUCHER);

        if ($lineItems->count() === 0) {
            return;
        }

        $prices = $calculatedCart->getCalculatedLineItems()->filterGoods()->getPrices();
        if ($prices->count() === 0) {
            return;
        }

        /** @var LineItemInterface $lineItem */
        foreach ($lineItems as $lineItem) {
            $code = $lineItem->getPayload()['code'];

            /** @var VoucherData $voucher */
            if (!$voucher = $dataCollection->get($code)) {
//                $cartContainer->getErrors()->add(new VoucherNotFoundError($code));
                $cartContainer->getLineItems()->remove($code);
                continue;
            }

            $calculatedVoucher = $this->calculator->calculate($calculatedCart, $context, $voucher, $lineItem);

            $calculatedCart->getCalculatedLineItems()->add($calculatedVoucher);
        }
    }
}
