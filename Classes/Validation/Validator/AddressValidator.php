<?php

namespace RKW\RkwShop\Validation\Validator;

use RKW\RkwBasics\Helper\Common;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * Class AddressValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AddressValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * Validator
     *
     * @var \RKW\RkwShop\Domain\Model\Order $order
     * @return boolean|string
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function isValid($order)
    {
        $isValid = true;
        $settings = $this->getSettings();

        //  @todo: Settings für requiredFields werden nicht benötigt, denn die Adressangaben sind allgemein verbindlich und notwendig!!!
        $requiredFields = GeneralUtility::trimExplode(',', $settings['requiredFields']);

        if (
            ($order instanceof \RKW\RkwShop\Domain\Model\Order)
        ) {
            if (
                ($frontendUser = $order->getFrontendUser())
                && ($methods = get_class_methods($frontendUser))
            ) {
                  $this->checkFields($requiredFields, $methods, $isValid, $model = $frontendUser);
            }

            if (
                ($order->getShippingAddressSameAsBillingAddress() !== '1')
                && ($shippingAddress = $order->getShippingAddress())
                && ($methods = get_class_methods($shippingAddress))
            ) {
                $this->checkFields($requiredFields, $methods, $isValid, $model = $shippingAddress);
            }

        }

        return $isValid;
    }

    /**
     * @param array $requiredFields
     * @param array $methods
     * @param boolean $isValid
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|\RKW\RkwRegistration\Domain\Model\ShippingAddress $model
     *
     * @return boolean
     */
    public function checkFields($requiredFields, $methods, $isValid, $model)
    {
        foreach ($requiredFields as $requiredField) {

            $getter = 'get' . ucfirst($requiredField);

            if (in_array($getter, $methods)) {

                if (
                    (
                        ($requiredField === 'gender')
                        && ($model->$getter() == 99)
                    )
                    || (
                        ($requiredField !== 'gender')
                        && (empty($model->$getter()))
                    )
                ) {

                    $this->result->forProperty($requiredField)->addError(
                        new \TYPO3\CMS\Extbase\Error\Error(
                            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                'shippingAddressValidator.notFilled',
                                'rkw_shop',
                                [
                                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                                        'shippingAddressValidator.field.' . $requiredField,
                                        'rkw_shop'
                                    )
                                ]
                            ), 1541170767
                        )
                    );
                    $isValid = false;
                }
            }
        }

        return $isValid;

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
    }
}

