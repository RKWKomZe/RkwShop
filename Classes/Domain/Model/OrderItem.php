<?php

namespace RKW\RkwShop\Domain\Model;

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
 * Class OrderItem
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderItem extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * order
     *
     * @var \RKW\RkwShop\Domain\Model\Order
     */
    protected $order = null;


    /**
     * product
     *
     * @var \RKW\RkwShop\Domain\Model\Product
     */
    protected $product = null;

    /**
     * amount
     *
     * @var int
     */
    protected $amount = 0;


    /**
     * isPreOrder
     *
     * @var bool
     */
    protected $isPreOrder = false;


    /**
     * Returns the order
     *
     * @return \RKW\RkwShop\Domain\Model\Order $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets the order
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @return void
     */
    public function setOrder(\RKW\RkwShop\Domain\Model\Order $order)
    {
        $this->order = $order;
    }


    /**
     * Returns the product
     *
     * @return \RKW\RkwShop\Domain\Model\Product $product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Sets the product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return void
     */
    public function setProduct(\RKW\RkwShop\Domain\Model\Product $product)
    {
        $this->product = $product;
    }


    /**
     * Returns the amount
     *
     * @return int $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets the amount
     *
     * @param int $amount
     * @return void
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }


    /**
     * Returns the isPreOrder
     *
     * @return bool $isPreOrder
     */
    public function getIsPreOrder()
    {
        return $this->isPreOrder;
    }

    /**
     * Sets the isPreOrder
     *
     * @param bool $isPreOrder
     * @return void
     */
    public function setIsPreOrder($isPreOrder)
    {
        $this->isPreOrder = $isPreOrder;
    }

}