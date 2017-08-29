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

namespace Shopware\Bundle\StoreFrontBundle\Country;

use Doctrine\DBAL\Connection;
use Shopware\Framework\Struct\FieldHelper;
use Shopware\Framework\Struct\SortArrayByKeysTrait;
use Shopware\Context\Struct\TranslationContext;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CountryGateway
{
    use Shopware\Framework\Struct\SortArrayByKeysTrait;

    /**
     * @var CountryHydrator
     */
    private $countryHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var \Shopware\Framework\Struct\FieldHelper
     */
    private $fieldHelper;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection      $connection
     * @param \Shopware\Framework\Struct\FieldHelper     $fieldHelper
     * @param CountryHydrator $countryHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        CountryHydrator $countryHydrator
    ) {
        $this->connection = $connection;
        $this->countryHydrator = $countryHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @param int[]              $ids
     * @param TranslationContext $context
     *
     * @return \Shopware\CountryArea\Struct\CountryArea[]
     */
    public function getAreas(array $ids, TranslationContext $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getAreaFields());

        $query->from('s_core_countries_areas', 'countryArea')
            ->where('countryArea.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $areas = [];
        foreach ($data as $row) {
            $area = $this->countryHydrator->hydrateArea($row);
            $areas[$area->getId()] = $area;
        }

        return $this->sortIndexedArrayByKeys($ids, $areas);
    }

    /**
     * @param int[]              $ids
     * @param TranslationContext $context
     *
     * @return \Shopware\Country\Struct\Country[]
     */
    public function getCountries(array $ids, TranslationContext $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getCountryFields());
        $query->from('s_core_countries', 'country')
            ->innerJoin('country', 's_core_countries_areas', 'countryArea', 'countryArea.id = country.areaID')
            ->leftJoin('country', 's_core_countries_attributes', 'countryAttribute', 'countryAttribute.countryID = country.id')
            ->where('country.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addCountryTranslation($query, $context);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $countries = [];
        foreach ($data as $row) {
            $country = $this->countryHydrator->hydrateCountry($row);
            $countries[$country->getId()] = $country;
        }

        return $this->sortIndexedArrayByKeys($ids, $countries);
    }

    /**
     * @param int[]              $ids
     * @param TranslationContext $context
     *
     * @return \Shopware\CountryState\Struct\CountryState[]
     */
    public function getStates(array $ids, TranslationContext $context)
    {
        $query = $this->createStateQuery($context);

        $query->where('countryState.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $states = [];
        foreach ($data as $row) {
            $state = $this->countryHydrator->hydrateState($row);
            $states[$state->getId()] = $state;
        }

        return $this->sortIndexedArrayByKeys($ids, $states);
    }

    /**
     * @param int[]              $countryIds
     * @param TranslationContext $context
     *
     * @return array indexed by country id contains an array of Struct\Country\CountryState
     */
    public function getCountryStates($countryIds, TranslationContext $context)
    {
        $query = $this->createStateQuery($context);

        $query->where('countryState.countryID IN (:ids)')
            ->setParameter(':ids', $countryIds, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $states = [];
        foreach ($data as $row) {
            $countryId = (int) $row['__countryState_countryID'];
            $state = $this->countryHydrator->hydrateState($row);
            $states[$countryId][$state->getId()] = $state;
        }

        return $states;
    }

    /**
     * @param \Shopware\Context\Struct\TranslationContext $context
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createStateQuery(TranslationContext $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getStateFields());

        $query->from('s_core_countries_states', 'countryState')
            ->leftJoin('countryState', 's_core_countries_states_attributes', 'countryStateAttribute', 'countryStateAttribute.stateID = countryState.id');

        $this->fieldHelper->addCountryStateTranslation($query, $context);

        return $query;
    }
}
