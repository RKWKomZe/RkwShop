<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Configure Plugin
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'ItemList',
            array(
                'Order' => 'newInit, newAjax, new, create, optIn',
            ),
            // non-cacheable actions
            array(
                'Order' => 'newAjax, new, create, optIn',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'MyOrderList',
            array(
                'MyOrder' => 'index, cancel',
            ),
            // non-cacheable actions
            array(
                'MyOrder' => 'index, cancel',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'ProductList',
            array(
                'Product' => 'index',
            ),
            // non-cacheable actions
            array(
                'Product' => 'index',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'ProductDetail',
            array(
                'Product' => 'show',
                'CartItem' => 'addCartItem',
            ),
            // non-cacheable actions
            array(
                'Product' => 'show',
                'CartItem' => 'addCartItem',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Cart',
            array(
                'Checkout' => 'showCart, confirmCart, orderCart',
                'CartItem' => 'removeCartItem, changeCartItemQuantity',
            ),
            // non-cacheable actions
            array(
                'Checkout' => 'showCart, confirmCart, orderCart',
                'CartItem' => 'removeCartItem, changeCartItemQuantity',
            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'MiniCart',
            [
                'Checkout' => 'showMiniCart',
            ],
            // non-cacheable actions
            [
                'Checkout' => 'showMiniCart',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Checkout',
            array(
                'Checkout' => 'showCart, confirmCart, reviewOrder, orderCart, finishOrder',
                'Order' => 'create',
            ),
            // non-cacheable actions
            array(
                'Checkout' => 'showCart, confirmCart, reviewOrder, orderCart, finishOrder',
                'Order' => 'create',
            )
        );

        //=================================================================
        // Register CommandController
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'RKW\\RkwShop\\Controller\\OrderCommandController';


        //=================================================================
        // Register DataMapper
        //=================================================================
        /** @var \TYPO3\CMS\Extbase\Object\Container\Container $extbaseObjectContainer */
        $extbaseObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
        $extbaseObjectContainer->registerImplementation(
            \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class,
            \RKW\RkwShop\Persistence\Generic\Mapper\DataMapper::class
        );

        //=================================================================
        // Register SignalSlots
        //=================================================================
        /**
         * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER  . 'RkwShop',
            'RKW\\RkwShop\\Service\\RkwMailService',
            'handleOptInRequestEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_CREATING_OPTIN_USER  . 'RkwShop',
            'RKW\\RkwShop\\Service\\RkwMailService',
            'handleOptInRequestEvent'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_USER_REGISTER_GRANT . 'RkwShop',
            \RKW\RkwShop\Service\Checkout\OrderService::class,
            'saveOrderSignalSlot'
        );

        $signalSlotDispatcher->connect(
            \RKW\RkwShop\Service\Checkout\OrderService::class,
            \RKW\RkwShop\Service\Checkout\OrderService::SIGNAL_AFTER_ORDER_CREATED_USER,
            'RKW\\RkwShop\\Service\\RkwMailService',
            'confirmationOrderUser'
        );

        $signalSlotDispatcher->connect(
            \RKW\RkwShop\Service\Checkout\OrderService::class,
            \RKW\RkwShop\Service\Checkout\OrderService::SIGNAL_AFTER_ORDER_CREATED_ADMIN,
            'RKW\\RkwShop\\Service\\RkwMailService',
            'confirmationOrderAdmin'
        );

        $signalSlotDispatcher->connect(
            'RKW\\RkwRegistration\\Tools\\Registration',
            \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_DELETING_USER,
            \RKW\RkwShop\Service\Checkout\OrderService::class,
            'removeAllOrdersOfFrontendUserSignalSlot'
        );

        $signalSlotDispatcher->connect(
            \RKW\RkwShop\Service\Checkout\OrderService::class,
            \RKW\RkwShop\Service\Checkout\OrderService::SIGNAL_AFTER_ORDER_DELETED_USER,
            'RKW\\RkwShop\\Service\\RkwMailService',
            'deleteOrderUser'
        );

        $signalSlotDispatcher->connect(
            \RKW\RkwShop\Service\Checkout\OrderService::class,
            \RKW\RkwShop\Service\Checkout\OrderService::SIGNAL_AFTER_ORDER_DELETED_ADMIN,
            'RKW\\RkwShop\\Service\\RkwMailService',
            'deleteOrderAdmin'
        );

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extKey]
            = 'RKW\RkwShop\Hook\UpdateProductTypeHook';

        //=================================================================
        // Register update wizard
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\RKW\RkwShop\Updates\GenerateOrderNumberForExistingOrders::class] = \RKW\RkwShop\Updates\GenerateOrderNumberForExistingOrders::class;

        //=================================================================
        // Register Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwShop']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => array(
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
                    // configuration for the writer
                    'logFile' => 'typo3temp/logs/tx_rkwshop.log'
                )
            ),
        );
    },
    $_EXTKEY
);

