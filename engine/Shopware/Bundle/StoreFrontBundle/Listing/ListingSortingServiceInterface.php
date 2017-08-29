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

namespace Shopware\Bundle\StoreFrontBundle\Listing;

use Shopware\Context\Struct\ShopContext;

interface ListingSortingServiceInterface
{
    /**
     * @param int[]                                                          $ids
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return ListingSorting[] indexed by id, sorted by provided id array
     */
    public function getList(array $ids, ShopContext $context);

    /**
     * @param int[]                                                          $categoryIds
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return array[] indexed by category id, sorted by category mapping or position
     */
    public function getSortingsOfCategories(array $categoryIds, ShopContext $context);

    /**
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return ListingSorting[] indexed by id, sorted by position
     */
    public function getAllCategorySortings(ShopContext $context);
}
