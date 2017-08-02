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

namespace Shopware\Category\Struct;

use Shopware\Framework\Struct\Collection;

class CategoryCollection extends Collection
{
    /**
     * @var Category[]
     */
    protected $elements = [];

    public function add(Category $category): void
    {
        $key = $this->getKey($category);
        $this->elements[$key] = $category;
    }

    public function remove(int $id): void
    {
        parent::doRemoveByKey($id);
    }

    public function removeElement(Category $category): void
    {
        parent::doRemoveByKey($this->getKey($category));
    }

    public function exists(Category $category): bool
    {
        return parent::has($this->getKey($category));
    }

    public function get(int $id): ? Category
    {
        if ($this->has($id)) {
            return $this->elements[$id];
        }

        return null;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->map(function (Category $category) {
            return $category->getId();
        });
    }

    public function getPaths(): array
    {
        return $this->map(function (Category $category) {
            return $category->getPath();
        });
    }

    public function getIdsIncludingPaths(): array
    {
        $ids = [];
        foreach ($this->elements as $category) {
            $ids[] = $category->getId();
            foreach ($category->getPath() as $id) {
                $ids[] = $id;
            }
        }

        return array_keys(array_flip($ids));
    }

    /**
     * @param int|null $parentId
     *
     * @return Category[]
     */
    public function getTree(?int $parentId): array
    {
        $result = [];
        foreach ($this->elements as $category) {
            if ($category->getParentId() != $parentId) {
                continue;
            }
            $category->setChildren(
                $this->getTree((int) $category->getId())
            );
            $result[] = $category;
        }

        return $result;
    }

    /**
     * @param Category $element
     *
     * @return int
     */
    protected function getKey(Category $element): int
    {
        return $element->getId();
    }
}
