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

namespace Shopware\Bundle\CartBundle\Domain\Error;

use Shopware\Bundle\CartBundle\Domain\Collection;

class ErrorCollection extends Collection
{
    /**
     * @var Error[]
     */
    protected $elements = [];

    public function add(Error $error): void
    {
        parent::doAdd($error);
    }

    public function has($errorClass): bool
    {
        foreach ($this->elements as $element) {
            if ($element instanceof $errorClass) {
                return true;
            }
        }

        return false;
    }

    public function hasLevel(int $errorLevel)
    {
        foreach ($this->elements as $element) {
            if ($element->getLevel() === $errorLevel) {
                return true;
            }
        }

        return false;
    }
}
