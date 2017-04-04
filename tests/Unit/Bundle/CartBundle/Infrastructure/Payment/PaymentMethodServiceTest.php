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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Infrastructure\Payment;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\Error\ErrorCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Price\CartPrice;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Payment\PaymentMethodGateway;
use Shopware\Bundle\CartBundle\Infrastructure\Payment\PaymentMethodHydrator;
use Shopware\Bundle\CartBundle\Infrastructure\Payment\PaymentMethodService;
use Shopware\Bundle\CartBundle\Infrastructure\Payment\RiskManagementPaymentFilter;
use Shopware\Bundle\StoreFrontBundle\Gateway\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway\Hydrator\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Service\CacheInterface;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\Generator;

class PaymentMethodServiceTest extends TestCase
{
    /**
     * @dataProvider dataSets
     *
     * @param array          $database
     * @param array          $expected
     * @param CalculatedCart $cart
     */
    public function testPaymentSets(array $database, array $expected, CalculatedCart $cart)
    {
        $fieldSelection = $this->createMock(Connection::class);
        $fieldSelection->expects(static::any())
            ->method('fetchAll')
            ->will(static::returnValue([]));

        $fieldHelper = new FieldHelper(
            $fieldSelection,
            $this->createMock(CacheInterface::class)
        );

        $hydrator = new PaymentMethodHydrator(
            new AttributeHydrator($fieldHelper)
        );

        $filter = $this->createMock(RiskManagementPaymentFilter::class);
        $filter->expects($this->any())
            ->method('filter')
            ->will($this->returnCallback([$this, 'riskFilter']));

        $service = new PaymentMethodService(
            new PaymentMethodGateway(
                $fieldHelper,
                $hydrator,
                $this->createDatabaseMock($database)
            ),
            $filter
        );

        $payments = $service->getAvailable($cart, Generator::createContext());
        $this->assertEquals($expected, $payments);
    }

    public function riskFilter($actives)
    {
        return $actives;
    }

    public function dataSets()
    {
        $hydrator = new PaymentMethodHydrator(
            new AttributeHydrator(
                $this->createMock(FieldHelper::class)
            )
        );

        return [
            //no database entries, empty cart
            [
                [],
                [],
                new CalculatedCart(
                    CartContainer::createNew('test'),
                    new CalculatedLineItemCollection(),
                    new CartPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection()),
                    new DeliveryCollection(),
                    new ErrorCollection()
                ),
            ],

            //two payment data rows, with empty cart
            [
                //database state
                [
                    self::createRow(1, 'cash1', 'Cash', 'CashPayment'),
                    self::createRow(2, 'cash2', 'Cash - 2', 'CashPayment - 2'),
                ],

                //expected payments
                [
                    $hydrator->hydrate(self::createRow(1, 'cash1', 'Cash', 'CashPayment')),
                    $hydrator->hydrate(self::createRow(2, 'cash2', 'Cash - 2', 'CashPayment - 2')),
                ],

                //cart state
                new CalculatedCart(
                    CartContainer::createNew('test'),
                    new CalculatedLineItemCollection(),
                    new CartPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection()),
                    new DeliveryCollection(),
                    new ErrorCollection()
                ),
            ],
        ];
    }

    private static function createRow(
        int $id,
        string $name,
        string $label,
        string $class,
        $additionalDescription = '',
        $table = '',
        $template = '',
        $hide = '0',
        $debit_percent = '1',
        $surcharge = '1',
        $position = '1',
        $active = '1',
        $esdActive = '0',
        $iFrameUrl = '',
        $action = '',
        $mobileInactive = '0',
        $pluginId = '1',
        $source = '1'
    ) {
        return [
              '__paymentMethod_id' => $id,
              '__paymentMethod_name' => $name,
              '__paymentMethod_description' => $label,
              '__paymentMethod_class' => $class,
              '__paymentMethod_additionaldescription' => $additionalDescription,
              '__paymentMethod_table' => $table,
              '__paymentMethod_template' => $template,
              '__paymentMethod_hide' => $hide,
              '__paymentMethod_debit_percent' => $debit_percent,
              '__paymentMethod_surcharge' => $surcharge,
              '__paymentMethod_position' => $position,
              '__paymentMethod_active' => $active,
              '__paymentMethod_esdactive' => $esdActive,
              '__paymentMethod_embediframe' => $iFrameUrl,
              '__paymentMethod_action' => $action,
              '__paymentMethod_mobile_inactive' => $mobileInactive,
              '__paymentMethod_source' => $source,
              '__paymentMethod_pluginId' => $pluginId,
              '__paymentMethod_rules' => null,
          ];
    }

    /**
     * @param array[] $queryResult
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private function createDatabaseMock($queryResult)
    {
        $statement = $this->createMock(Statement::class);
        $statement->expects(static::any())
            ->method('fetchAll')
            ->will(static::returnValue($queryResult));

        $query = $this->createMock(QueryBuilder::class);
        $query->expects(static::any())
            ->method('execute')
            ->will(static::returnValue($statement));

        $connection = $this->createMock(Connection::class);
        $connection->expects(static::any())
            ->method('createQueryBuilder')
            ->will(static::returnValue($query));

        return $connection;
    }
}
