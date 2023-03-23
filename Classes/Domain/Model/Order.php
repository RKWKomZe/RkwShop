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
 * Class Order
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Order extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var integer
     */
    protected $crdate;


    /**
     * @var integer
     */
    protected $tstamp;


    /**
     * @var integer
     */
    protected $hidden;


    /**
     * @var integer
     */
    protected $deleted;


    /**
     * Status
     *
     * @var integer
     */
    protected $status = 0;

    /**
     * email
     *
     * @var string
     */
    protected $email;

    /**
     * remark
     *
     * @var string
     */
    protected $remark = '';


    /**
     * frontendUser
     *
     * @var \RKW\RkwShop\Domain\Model\FrontendUser
     */
    protected $frontendUser = null;

    /**
     * targetGroup
     *
     * @var \RKW\RkwBasics\Domain\Model\TargetGroup
     */
    protected $targetGroup;

    /**
     * shippingAddress
     *
     * @var \RKW\RkwShop\Domain\Model\ShippingAddress
     */
    protected $shippingAddress = null;


    /**
     * orderItem
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\OrderItem>
     * @cascade remove
     */
    protected $orderItem = null;

    /**
     * shippedTstamp
     *
     * @var integer
     */
    protected $shippedTstamp;


    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->orderItem = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }


    /**
     * Returns the crdate value
     *
     * @return integer
     * @api
     */
    public function getCrdate()
    {
        return $this->crdate;
    }


    /**
     * Returns the tstamp value
     *
     * @return integer
     * @api
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Sets the hidden value
     *
     * @param integer $hidden
     * @api
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }


    /**
     * Returns the hidden value
     *
     * @return integer
     * @api
     */
    public function getHidden()
    {
        return $this->hidden;
        //===
    }

    /**
     * Sets the deleted value
     *
     * @param integer $deleted
     * @api
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }


    /**
     * Returns the deleted value
     *
     * @return integer
     * @api
     */
    public function getDeleted()
    {
        return $this->deleted;
        //===
    }


    /**
     * Sets the status value
     *
     * @param integer $status
     * @api
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }


    /**
     * Returns the status value
     *
     * @return integer
     * @api
     */
    public function getStatus()
    {
        return $this->status;
        //===
    }


    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    /**
     * Returns the remark
     *
     * @return string $remark
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * Sets the remark
     *
     * @param string $remark
     * @return void
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    /**
     * Returns the frontendUser
     *
     * @return \RKW\RkwShop\Domain\Model\FrontendUser $frontendUser
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }

    /**
     * Sets the frontendUser
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return void
     */
    public function setFrontendUser(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {
        $this->frontendUser = $frontendUser;
    }

    /**
     * Returns the targetGroup
     *
     * @return \RKW\RkwBasics\Domain\Model\TargetGroup
     */
    public function getTargetGroup()
    {
        return $this->targetGroup;
    }

    /**
     * Sets the targetGroup
     *
     * @param \RKW\RkwBasics\Domain\Model\TargetGroup $targetGroup
     * @return void
     */
    public function setTargetGroup(\RKW\RkwBasics\Domain\Model\TargetGroup $targetGroup)
    {
        $this->targetGroup = $targetGroup;
    }

    /**
     * Returns the frontendUser
     *
     * @return \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * Sets the frontendUser
     *
     * @param \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress
     * @return void
     */
    public function setShippingAddress(\RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }


    /**
     * Adds a orderItem
     *
     * @param \RKW\RkwShop\Domain\Model\OrderItem $orderItem
     * @return void
     */
    public function addOrderItem(\RKW\RkwShop\Domain\Model\OrderItem $orderItem)
    {
        $this->orderItem->attach($orderItem);
    }

    /**
     * Removes a orderItem
     *
     * @param \RKW\RkwShop\Domain\Model\OrderItem $orderItem
     * @return void
     */
    public function removeOrderItem(\RKW\RkwShop\Domain\Model\OrderItem $orderItem)
    {
        $this->orderItem->detach($orderItem);
    }

    /**
     * Returns the orderItem
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\OrderItem> $orderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * Sets the orderItem
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\OrderItem> $orderItem
     * @return void
     */
    public function setOrderItem(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * Returns the shippedTstamp
     *
     * @return int
     */
    public function getShippedTstamp(): int
    {
        return $this->shippedTstamp;
    }

    /**
     * Sets the shippedTstamp
     *
     * @param int $shippedTstamp
     */
    public function setShippedTstamp(int $shippedTstamp): void
    {
        $this->shippedTstamp = $shippedTstamp;
    }
}
