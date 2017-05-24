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

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\StoreFrontBundle\Common\Collection;

class ViewDeliveryPositionCollection extends Collection
{
    /**
     * @var ViewDeliveryPosition[]
     */
    protected $elements = [];

    public function add(ViewDeliveryPosition $deliveryPosition): void
    {
        $key = $this->getKey($deliveryPosition);
        $this->elements[$key] = $deliveryPosition;
    }

    public function removeElement(ViewDeliveryPosition $deliveryPosition): void
    {
        parent::doRemoveByKey($this->getKey($deliveryPosition));
    }

    public function exists(ViewDeliveryPosition $deliveryPosition): bool
    {
        return parent::has($this->getKey($deliveryPosition));
    }

    public function get(string $identifier): ? ViewDeliveryPosition
    {
        if ($this->has($identifier)) {
            return $this->elements[$identifier];
        }

        return null;
    }

    protected function getKey(ViewDeliveryPosition $element): string
    {
        return $element->getViewLineItem()->getCalculatedLineItem()->getIdentifier();
    }
}
