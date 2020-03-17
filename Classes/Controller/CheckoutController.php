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

use RKW\RkwShop\Helper\DivUtility;
use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Model\OrderItem;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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
    /*
    public function showCartAction()
    {

        $order = $this->cart->get(); //  liefert bereits die Order zurück

        $listItemsPerView = (int)$this->settings['itemsPerPage'] ? (int)$this->settings['itemsPerPage'] : 10;

    //        $productList = DivUtility::prepareResultsList($queryResult, $listItemsPerView);

        $this->view->assignMultiple([
            'order'   => $order,
            'checkoutPid'   => (int)$this->settings['checkoutPid']
        ]);

    }
    */

    /**
     * action create
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param integer $terms
     * @param integer $privacy
     * @return void
     */
    public function createAction(\RKW\RkwShop\Domain\Model\Order $order = null, $terms = null, $privacy = null)
    {

        //  if current user is not logged in yet, take him to mein.rkw
        if (! $this->getFrontendUser()) {
            $uri = $this->uriBuilder
                ->setTargetPageUid((int)$this->settings['accountPid'])
                ->build();
            $this->redirectToUri($uri);
        }

        //  update the order information like shipping address, if there is a logged in user

        //  don't do any implicit sign up through create order, a user has to be registered in an isolated process, so that ordering can be isolated too

        //  show his cart somewhere in the header or the menu to give him access to, when he returns

        $this->view->assignMultiple([
            'frontendUser'    => $this->getFrontendUser(),
            'order'           => $order,
            'termsPid'        => (int)$this->settings['termsPid'],
            'terms'           => $terms,
            'privacy'         => $privacy
        ]);

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