<?php

namespace RKW\RkwShop\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use RKW\RkwBasics\Helper\Common;

/**
 * Class CheckoutController
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CheckoutController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * CartService
     *
     * @var \RKW\RkwShop\Service\Checkout\CartService
     * @inject
     */
    protected $cartService;

    /**
     * OrderService
     *
     * @var \RKW\RkwShop\Service\Checkout\OrderService
     * @inject
     */
    protected $orderService;

    /**
     * logged in FrontendUser
     *
     * @var \RKW\RkwShop\Domain\Model\FrontendUser
     */
    protected $frontendUser = null;

    /**
     * action show cart
     *
     * @return void
     */
    public function showCartAction()
    {

        $cart = $this->cartService->getCart(); //  liefert bereits den Warenkorb zurück

        $listItemsPerView = (int)$this->settings['itemsPerPage'] ? (int)$this->settings['itemsPerPage'] : 10;

        //        $productList = DivUtility::prepareResultsList($queryResult, $listItemsPerView);

        $this->view->assignMultiple([
            'cart'   => $cart,
            'checkoutPid'   => (int)$this->settings['checkoutPid'],
            'listPid'   => (int)$this->settings['listPid']
        ]);

    }

    /**
     * action show mini cart
     *
     * @return void
     */
    public function showMiniCartAction()
    {

        $cart = $this->cartService->getCart(); //  liefert bereits die Order zurück

        $listItemsPerView = (int)$this->settings['itemsPerPage'] ? (int)$this->settings['itemsPerPage'] : 10;

//        $productList = DivUtility::prepareResultsList($queryResult, $listItemsPerView);

        $this->view->assignMultiple([
            'cart' => $cart,
            'cartPid' => (int)$this->settings['cartPid']
        ]);

    }

    /**
     * action confirm order
     *
     * @return void
     */
    public function confirmCartAction()
    {

        //  if current user is not logged in yet, take him to mein.rkw
        //  @todo: how can I do this kind of redirect back to his cart and next controller action
        if (! $this->getFrontendUser()) {
            $uri = $this->uriBuilder
                ->setTargetPageUid((int)$this->settings['accountPid'])
                ->build();
            //  see redirectToLogin in rkw_registration
            $this->redirectToUri($uri);
        }

        $cart = $this->cartService->getCart();

        $order = $this->cartService->convertCart($cart);

        $this->view->assignMultiple([
            'frontendUser'    => $this->getFrontendUser(),
            'order'           => $order,
        ]);

    }

    /**
     * see OrderController->newAction()
     */
    public function newOrderAction()
    {

    }

    /**
     * action reviewOrder
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @return void
     * @validate $order \RKW\RkwShop\Validation\Validator\AddressValidator
     */
    public function reviewOrderAction(\RKW\RkwShop\Domain\Model\Order $order)
    {
        //  @todo: Terms und Privacy werden nicht benötigt, da diese beim Registrieren bestätigt wurden! Und wir erlauben nur Bestellungen durch registrierte Nutzer!
        //  @todo: Hier nochmals auf die AGB verweisen (siehe rosebikes.de)

        $cart = $this->cartService->getCart();

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        foreach ($cart->getOrderItem() as $orderItem) {
            $order->addOrderItem($orderItem);
        }

        $order = $this->orderService->checkShippingAddress($order);

        //  @todo: müsste hier über die Validierung abgefangen werden, nicht über die Exception!?

        //  show order review page
        $this->view->assignMultiple([
            'frontendUser'    => $this->getFrontendUser(),
            'order'           => $order,
        ]);
    }

    /**
     * action orderCart
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     *
     * @validate $order \RKW\RkwShop\Validation\Validator\AddressValidator
     * @return void
     */
    public function orderCartAction(\RKW\RkwShop\Domain\Model\Order $order)
    {
        //  don't do any implicit sign up through create order, a user has to be registered in an isolated process, so that ordering can be isolated too

        try {

            $message = $this->orderService->createOrder($order, $this->request, $this->getFrontendUser());

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    $message, 'rkw_shop'
                )
            );

            $this->redirect('finishOrder');

        } catch (\RKW\RkwShop\Exception $exception) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    $exception->getMessage(), 'rkw_shop'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            //  @todo: Wohin im Fehlerfalle?
            //  $this->forward();

        }

    }

    /**
     *
     */
    public function finishOrderAction()
    {
        //  show success page
    }

    /**
     * Returns current logged in user object
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     */
    protected function getFrontendUser()
    {

        if (!$this->frontendUser) {

            $frontendUser = $this->frontendUserRepository->findByUidNoAnonymous($this->getFrontendUserId());
            if ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser) {
                $this->frontendUser = $frontendUser;
            }
        }

        return $this->frontendUser;
        //===
    }



    /**
     * Id of logged User
     *
     * @return integer|null
     */
    protected function getFrontendUserId()
    {
        // is $GLOBALS set?
        if (
            ($GLOBALS['TSFE'])
            && ($GLOBALS['TSFE']->loginUser)
            && ($GLOBALS['TSFE']->fe_user->user['uid'])
        ) {
            return intval($GLOBALS['TSFE']->fe_user->user['uid']);
            //===
        }

        return null;
        //===
    }

    /**
     * Remove ErrorFlashMessage
     *
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::getErrorFlashMessage()
     */
    protected function getErrorFlashMessage()
    {
        return false;
        //===
    }

    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('Rkwshop', $which);
        //===
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }


}