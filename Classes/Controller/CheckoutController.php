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
     * action create
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param integer $terms
     * @param integer $privacy
     * @return void
     */
    public function createAction(\RKW\RkwShop\Domain\Model\Order $order = null, $terms = null, $privacy = null)
    {

        $this->view->assignMultiple([
            'frontendUser'    => null,
            'order'           => $order,
            'termsPid'        => (int)$this->settings['termsPid'],
            'terms'           => $terms,
            'privacy'         => $privacy
        ]);

    }

}