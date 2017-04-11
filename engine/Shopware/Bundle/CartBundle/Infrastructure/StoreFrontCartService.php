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

namespace Shopware\Bundle\CartBundle\Infrastructure;

use Psr\Log\LoggerInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\CartCalculator;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\CartPersisterInterface;
use Shopware\Bundle\CartBundle\Domain\Exception\LineItemNotFoundException;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Order\OrderPersisterInterface;
use Shopware\Bundle\CartBundle\Infrastructure\View\ViewCart;
use Shopware\Bundle\CartBundle\Infrastructure\View\ViewCartTransformer;
use Shopware\Bundle\StoreFrontBundle\Context\ContextServiceInterface;

class StoreFrontCartService
{
    const CART_NAME = 'shopware';

    const CART_TOKEN_KEY = 'cart_token_' . self::CART_NAME;

    /**
     * @var CartCalculator
     */
    private $calculation;

    /**
     * @var CartPersisterInterface
     */
    private $persister;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var ViewCartTransformer
     */
    private $viewCartTransformer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderPersisterInterface
     */
    private $orderPersister;

    public function __construct(
        CartCalculator $calculation,
        CartPersisterInterface $persister,
        ContextServiceInterface $contextService,
        \Enlight_Components_Session_Namespace $session,
        ViewCartTransformer $viewCartTransformer,
        LoggerInterface $logger,
        OrderPersisterInterface $orderPersister
    ) {
        $this->calculation = $calculation;
        $this->persister = $persister;
        $this->contextService = $contextService;
        $this->session = $session;
        $this->viewCartTransformer = $viewCartTransformer;
        $this->logger = $logger;
        $this->orderPersister = $orderPersister;
    }

    public function getCart(): ViewCart
    {
        return $this->viewCartTransformer->transform(
            $this->getCalculatedCart(),
            $this->contextService->getShopContext()
        );
    }

    public function add(LineItemInterface $item): void
    {
        $cart = $this->getCart()->getCalculatedCart()->getCartContainer();

        $cart->getLineItems()->add($item);

        $this->calculate($cart);
    }

    public function changeQuantity(string $identifier, int $quantity): void
    {
        $cart = $this->getCart()->getCalculatedCart()->getCartContainer();

        if (!$cart->getLineItems()->has($identifier)) {
            throw new LineItemNotFoundException($identifier);
        }

        $cart->getLineItems()->get($identifier)->setQuantity($quantity);

        $this->calculate($cart);
    }

    public function remove(string $identifier): void
    {
        $cartContainer = $this->getCart()->getCalculatedCart()->getCartContainer();
        $cartContainer->getLineItems()->remove($identifier);
        $this->calculate($cartContainer);
    }

    public function order()
    {
        $this->orderPersister->persist(
            $this->getCart()->getCalculatedCart(),
            $context = $this->contextService->getShopContext()
        );

        $this->createNewCart();
    }

    private function getCartContainer(): CartContainer
    {
        if ($this->getCartToken() === null) {
            //first access for frontend session
            return $this->createNewCart();
        }

        try {
            //try to access existing cartContainer, identified by session token
            return $this->persister->load($this->getCartToken());
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Cart with token %s can not be loaded with message: %s', $this->getCartToken(), $e->getMessage())
            );

            //token not found, create new cartContainer
            return $this->createNewCart();
        }
    }

    private function getCalculatedCart(): CalculatedCart
    {
        return $this->calculate(
            $this->getCartContainer()
        );
    }

    private function calculate(CartContainer $cartContainer): CalculatedCart
    {
        $context = $this->contextService->getShopContext();
        $calculated = $this->calculation->calculate($cartContainer, $context);
        $this->save($calculated->getCartContainer());

        return $calculated;
    }

    private function save(CartContainer $cartContainer): void
    {
        $this->persister->save($cartContainer);
        $this->session->offsetSet(self::CART_TOKEN_KEY, $cartContainer->getToken());
    }

    private function createNewCart(): CartContainer
    {
        $cartContainer = CartContainer::createNew(self::CART_NAME);
        $this->session->offsetSet(self::CART_TOKEN_KEY, $cartContainer->getToken());

        return $cartContainer;
    }

    private function getCartToken(): ? string
    {
        if ($this->session->offsetExists(self::CART_TOKEN_KEY)) {
            return $this->session->offsetGet(self::CART_TOKEN_KEY);
        }

        return null;
    }
}
