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

use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Helper\DivUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class ManageOrdersController
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ManageOrdersController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * orderRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     * @inject
     */
    protected $orderRepository = null;

    /**
     * action list products
     *
     * @return void
     */
    public function listAction()
    {

        $orders = $this->orderRepository->findAll();

        $this->view->assign('orders', $orders);

    }

    /**
     * action show
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     *
     * @ignorevalidation $order
     */
    public function showAction(\RKW\RkwShop\Domain\Model\Order $order = null)
    {
        if (empty($order)) {
            $this->forward('list');
        }

        //  $this->view->assign('user', $GLOBALS['TSFE']->fe_user->user);
        $this->view->assign('order', $order);

    }

}