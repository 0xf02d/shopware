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

namespace Shopware\Bundle\StoreFrontBundle\Manufacturer;

use Shopware\Context\Struct\ShopContext;
use Shopware\Components\Routing\RouterInterface;
use Shopware\ProductManufacturer\Struct\ProductManufacturer;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ManufacturerService implements ManufacturerServiceInterface
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Manufacturer\ManufacturerGateway
     */
    private $manufacturerGateway;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Manufacturer\ManufacturerGateway $manufacturerGateway
     * @param RouterInterface                                                    $router
     */
    public function __construct(
        ManufacturerGateway $manufacturerGateway,
        RouterInterface $router
    ) {
        $this->manufacturerGateway = $manufacturerGateway;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContext $context)
    {
        $manufacturers = $this->manufacturerGateway->getList($ids, $context->getTranslationContext());

        //fetch all manufacturer links instead of calling {url ...} smarty function which executes a query for each link
        $links = $this->collectLinks($manufacturers);
        $urls = $this->router->generateList($links);
        foreach ($manufacturers as $manufacturer) {
            if (array_key_exists($manufacturer->getId(), $urls)) {
                $manufacturer->setLink($urls[$manufacturer->getId()]);
            }
        }

        return $manufacturers;
    }

    /**
     * @param ProductManufacturer[] $manufacturers
     *
     * @return array[]
     */
    private function collectLinks(array $manufacturers)
    {
        $links = [];
        foreach ($manufacturers as $manufacturer) {
            $links[$manufacturer->getId()] = [
                'controller' => 'listing',
                'action' => 'manufacturer',
                'sSupplier' => $manufacturer->getId(),
            ];
        }

        return $links;
    }
}
