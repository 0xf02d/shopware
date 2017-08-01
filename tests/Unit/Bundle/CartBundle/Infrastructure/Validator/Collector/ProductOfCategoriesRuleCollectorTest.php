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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Infrastructure\Validator\Collector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Product\ProductFetchDefinition;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\Collector\ProductOfCategoriesRuleCollector;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\Data\ProductOfCategoriesRuleData;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\ProductOfCategoriesRule;
use Shopware\Framework\Struct\StructCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\ValidatableDefinition;

class ProductOfCategoriesRuleCollectorTest extends TestCase
{
    public function testWithoutRule(): void
    {
        $context = $this->createMock(ShopContext::class);

        $connection = $this->createMock(Connection::class);

        $collector = new ProductOfCategoriesRuleCollector($connection);

        $dataCollection = new StructCollection();

        $collector->fetch($dataCollection, new StructCollection(), $context);

        $this->assertSame(0, $dataCollection->count());
    }

    public function testWithEmptyCart(): void
    {
        $context = $this->createMock(ShopContext::class);

        $connection = $this->createConnection([]);

        $collector = new ProductOfCategoriesRuleCollector($connection);

        $dataCollection = new StructCollection([
            new ValidatableDefinition(new ProductOfCategoriesRule([1])),
        ]);

        $collector->fetch($dataCollection, new StructCollection(), $context);

        $this->assertSame(1, $dataCollection->count());
    }

    public function testWithSingleRule(): void
    {
        $context = $this->createMock(ShopContext::class);

        $connection = $this->createConnection([
            1 => ['SW1', 'SW2'],
            2 => ['SW1', 'SW2'],
        ]);

        $collector = new ProductOfCategoriesRuleCollector($connection);

        $dataCollection = new StructCollection([
            new ValidatableDefinition(new ProductOfCategoriesRule([1])),
        ]);

        $collector->fetch($dataCollection, new StructCollection([
            new ProductFetchDefinition(['SW1', 'SW2']),
        ]), $context);

        $this->assertSame(2, $dataCollection->count());

        /** @var ProductOfCategoriesRuleData $data */
        $data = $dataCollection->get(ProductOfCategoriesRuleData::class);

        $this->assertTrue($data->hasCategory([1]));
    }

    public function testWithMultipleRules(): void
    {
        $context = $this->createMock(ShopContext::class);

        $connection = $this->createConnection(
            [
                1 => ['SW1', 'SW2'],
                2 => ['SW1', 'SW2'],
            ],
            [1, 2, 3, 4],
            ['SW1', 'SW2']
        );

        $collector = new ProductOfCategoriesRuleCollector($connection);

        $dataCollection = new StructCollection([
            new ValidatableDefinition(new ProductOfCategoriesRule([1, 2])),
            new ValidatableDefinition(new ProductOfCategoriesRule([3, 4])),
        ]);

        $fetchDefinition = new StructCollection([
            new ProductFetchDefinition(['SW1', 'SW2']),
        ]);

        $collector->fetch($dataCollection, $fetchDefinition, $context);

        $this->assertSame(3, $dataCollection->count());

        /** @var ProductOfCategoriesRuleData $data */
        $data = $dataCollection->get(ProductOfCategoriesRuleData::class);

        $this->assertTrue($data->hasCategory([1]));
        $this->assertTrue($data->hasCategory([2]));
    }

    private function createConnection(?array $result, array $categoryIds = [], array $numbers = []): \PHPUnit_Framework_MockObject_MockObject
    {
        $statement = $this->createMock(Statement::class);
        $statement->expects(static::any())
            ->method('fetchAll')
            ->will(static::returnValue($result));

        $query = $this->createMock(QueryBuilder::class);
        $query->expects(static::any())
            ->method('execute')
            ->will(static::returnValue($statement));

        if (!empty($categoryIds)) {
            $query->expects(static::exactly(2))
                ->method('setParameter')
                ->withConsecutive(
                    [':numbers', static::equalTo($numbers), Connection::PARAM_STR_ARRAY],
                    [':categoryIds', static::equalTo($categoryIds), Connection::PARAM_INT_ARRAY]
                );
        }

        $connection = $this->createMock(Connection::class);
        $connection->expects(static::any())
            ->method('createQueryBuilder')
            ->will(static::returnValue($query));

        return $connection;
    }
}
