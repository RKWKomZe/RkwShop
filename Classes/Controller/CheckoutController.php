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
            'checkoutPid'   => (int)$this->settings['checkoutPid']
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
     * @param integer $terms
     * @param integer $privacy
     * @return void
     */
    public function confirmCartAction($terms = null, $privacy = null)
    {

        //  if current user is not logged in yet, take him to mein.rkw
        //  @todo: and if he is logged in, the cart has to be set to his frontend user id and the hash has to be deleted
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
            'termsPid'        => (int)$this->settings['termsPid'],
            'terms'           => $terms,
            'privacy'         => $privacy,
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
     * @param integer $privacy
     * @return void
     * @todo fix validation
     * @ignorevalidation $order
//     * @validate $order \RKW\RkwShop\Validation\Validator\ShippingAddressValidator
     */
    public function reviewOrderAction(\RKW\RkwShop\Domain\Model\Order $order, $privacy = null)
    {
        $this->orderService->checkShippingAddress($order);

        // check privacy flag
        //  @todo: müsste hier über die Validierung abgefangen werden, nicht über die Exception!?
//        if (! $privacy) {
//            throw new Exception('orderService.error.acceptPrivacy');
//        }

        //  show order review page
        $this->view->assignMultiple([
            'frontendUser'    => $this->getFrontendUser(),
            'order'           => $order,
            'privacy'         => $privacy
        ]);
    }

    /**
     * action orderCart
     *
     * @return void
     */
    public function orderCartAction()
    {

        $cart = $this->cartService->getCart();

        //  get billing address and update frontend user, too

        //  don't do any implicit sign up through create order, a user has to be registered in an isolated process, so that ordering can be isolated too

        try {

            $message = $this->cartService->orderCart($cart);

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    $message, 'rkw_shop'
                )
            );

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

        $this->redirect('finishOrder');

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

}