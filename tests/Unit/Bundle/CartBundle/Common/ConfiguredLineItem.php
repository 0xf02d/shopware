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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Common;

use Shopware\Bundle\CartBundle\Domain\Delivery\Delivery;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryInformation;
use Shopware\Bundle\CartBundle\Domain\LineItem\DeliverableLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\StoreFrontBundle\Common\Struct;

class ConfiguredLineItem extends Struct implements DeliverableLineItemInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var Price
     */
    private $price;

    /**
     * @var LineItemInterface
     */
    private $lineItem;

    /**
     * @var DeliveryInformation
     */
    private $deliveryInformation;

    /**
     * @var Delivery|null
     */
    private $delivery;

    /**
     * @param string              $identifier
     * @param float               $quantity
     * @param Price               $price
     * @param LineItemInterface   $lineItem
     * @param DeliveryInformation $deliveryInformation
     */
    public function __construct(
        $identifier,
        $quantity = null,
        Price $price = null,
        LineItemInterface $lineItem = null,
        DeliveryInformation $deliveryInformation = null
    ) {
        $this->identifier = $identifier;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->lineItem = $lineItem;
        if ($deliveryInformation === null) {
            $deliveryInformation = new DeliveryInformation(
                0,
                0,
                0,
                0,
                0,
                new DeliveryDate(new \DateTime(), new \DateTime()),
                new DeliveryDate(new \DateTime(), new \DateTime())
            );
        }
        $this->deliveryInformation = $deliveryInformation;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->deliveryInformation->getStock();
    }

    public function getInStockDeliveryDate(): DeliveryDate
    {
        return $this->deliveryInformation->getInStockDeliveryDate();
    }

    public function getOutOfStockDeliveryDate(): DeliveryDate
    {
        return $this->deliveryInformation->getOutOfStockDeliveryDate();
    }

    public function getWeight(): float
    {
        return $this->deliveryInformation->getWeight();
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getLineItem(): LineItemInterface
    {
        return $this->lineItem;
    }

    public function getDelivery(): ? Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): void
    {
        $this->delivery = $delivery;
    }
}
