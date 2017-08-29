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

namespace Shopware\Bundle\StoreFrontBundle\Configurator;

use Doctrine\DBAL\Connection;
use Shopware\Framework\Struct\FieldHelper;
use Shopware\Context\Struct\TranslationContext;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductConfigurationGateway
{
    /**
     * @var ConfiguratorHydrator
     */
    private $configuratorHydrator;

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
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection           $connection
     * @param \Shopware\Framework\Struct\FieldHelper          $fieldHelper
     * @param ConfiguratorHydrator $configuratorHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        ConfiguratorHydrator $configuratorHydrator
    ) {
        $this->connection = $connection;
        $this->configuratorHydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Product\BaseProduct[] $products
     * @param TranslationContext                                      $context
     *
     * @return array indexed by the product order number, each array element contains a Struct\Configurator\PropertyGroup array
     */
    public function getList($products, TranslationContext $context)
    {
        if (empty($products)) {
            return [];
        }

        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }
        $ids = array_unique($ids);

        $query = $this->getQuery($ids, $context);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $result = [];
        foreach ($data as $key => $groups) {
            $result[$key] = $this->configuratorHydrator->hydrateGroups($groups);
        }

        return $result;
    }

    /**
     * @param $ids
     * @param TranslationContext $context
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQuery($ids, TranslationContext $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('variants.ordernumber as number')
            ->addSelect($this->fieldHelper->getConfiguratorGroupFields())
            ->addSelect($this->fieldHelper->getConfiguratorOptionFields());

        $query->from('s_article_configurator_option_relations', 'relations')
            ->innerJoin('relations', 's_articles_details', 'variants', 'variants.id = relations.article_id')
            ->innerJoin('relations', 's_article_configurator_options', 'configuratorOption', 'configuratorOption.id = relations.option_id')
            ->innerJoin('configuratorOption', 's_article_configurator_groups', 'configuratorGroup', 'configuratorGroup.id = configuratorOption.group_id')
            ->leftJoin('configuratorGroup', 's_article_configurator_groups_attributes', 'configuratorGroupAttribute', 'configuratorGroupAttribute.groupID = configuratorGroup.id')
            ->leftJoin('configuratorOption', 's_article_configurator_options_attributes', 'configuratorOptionAttribute', 'configuratorOptionAttribute.optionID = configuratorOption.id')
            ->where('relations.article_id IN (:ids)')
            ->addOrderBy('configuratorGroup.position')
            ->addOrderBy('configuratorGroup.id')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addConfiguratorGroupTranslation($query, $context);
        $this->fieldHelper->addConfiguratorOptionTranslation($query, $context);

        return $query;
    }
}
