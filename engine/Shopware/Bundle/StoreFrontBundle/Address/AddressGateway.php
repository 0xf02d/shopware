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

namespace Shopware\Bundle\StoreFrontBundle\Address;

use Doctrine\DBAL\Connection;
use Shopware\Framework\Struct\FieldHelper;
use Shopware\Framework\Struct\SortArrayByKeysTrait;
use Shopware\Context\TranslationContext;

class AddressGateway
{
    use Shopware\Framework\Struct\SortArrayByKeysTrait;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var AddressHydrator
     */
    private $hydrator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param \Shopware\Framework\Struct\FieldHelper $fieldHelper
     * @param AddressHydrator                                      $hydrator
     * @param Connection                                           $connection
     */
    public function __construct(
        FieldHelper $fieldHelper,
        AddressHydrator $hydrator,
        Connection $connection
    ) {
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
        $this->connection = $connection;
    }

    /**
     * @param int[]              $ids
     * @param TranslationContext $context
     *
     * @return Address[] indexed by id
     */
    public function getList(array $ids, TranslationContext $context): array
    {
        if (0 === count($ids)) {
            return [];
        }
        $query = $this->connection->createQueryBuilder();
        $query->select('address.id as arrayKey');
        $query->addSelect($this->fieldHelper->getAddressFields());
        $query->addSelect($this->fieldHelper->getAreaFields());
        $query->addSelect($this->fieldHelper->getCountryFields());
        $query->addSelect($this->fieldHelper->getStateFields());

        $query->from('s_user_addresses', 'address');
        $query->innerJoin('address', 's_core_countries', 'country', 'country.id = address.country_id');
        $query->leftJoin('country', 's_core_countries_areas', 'countryArea', 'countryArea.id = country.areaID');
        $query->leftJoin('address', 's_user_addresses_attributes', 'addressAttribute', 'address.id = addressAttribute.address_id');
        $query->leftJoin('address', 's_core_countries_states', 'countryState', 'countryState.id = address.state_id');
        $query->leftJoin('country', 's_core_countries_attributes', 'countryAttribute', 'countryAttribute.countryID = country.id');
        $query->leftJoin('countryState', 's_core_countries_states_attributes', 'countryStateAttribute', 'countryStateAttribute.stateID = countryState.id');

        $query->where('address.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addAddressTranslation($query, $context);
        $this->fieldHelper->addCountryTranslation($query, $context);
        $this->fieldHelper->addCountryStateTranslation($query, $context);

        $data = $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

        $addresses = [];
        foreach ($data as $id => $row) {
            $addresses[$id] = $this->hydrator->hydrate($row);
        }

        return $this->sortIndexedArrayByKeys($ids, $addresses);
    }
}
