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

namespace Shopware\Bundle\StoreFrontBundle\ProductLink;

use Doctrine\DBAL\Connection;
use Shopware\Framework\Struct\FieldHelper;
use Shopware\Context\TranslationContext;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductLinkGateway
{
    /**
     * @var ProductLinkHydrator
     */
    private $linkHydrator;

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
     * @param Connection          $connection
     * @param \Shopware\Framework\Struct\FieldHelper         $fieldHelper
     * @param ProductLinkHydrator $linkHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        ProductLinkHydrator $linkHydrator
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->linkHydrator = $linkHydrator;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Product\BaseProduct[] $products
     * @param \Shopware\Context\TranslationContext                                      $context
     *
     * @return array Indexed by the product order number. Each element contains a \Shopware\Bundle\StoreFrontBundle\ProductLink\Link array
     */
    public function getList($products, TranslationContext $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getLinkFields());

        $query->from('s_articles_information', 'link')
            ->leftJoin('link', 's_articles_information_attributes', 'linkAttribute', 'linkAttribute.informationID = link.id')
            ->where('link.articleID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addLinkTranslation($query, $context);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $links = [];
        foreach ($data as $row) {
            $key = $row['__link_articleID'];
            $link = $this->linkHydrator->hydrate($row);
            $links[$key][] = $link;
        }

        $result = [];
        foreach ($products as $product) {
            if (isset($links[$product->getId()])) {
                $result[$product->getNumber()] = $links[$product->getId()];
            }
        }

        return $result;
    }
}
