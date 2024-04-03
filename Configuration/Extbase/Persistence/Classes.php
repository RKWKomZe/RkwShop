<?php
declare(strict_types = 1);

return [
    \RKW\RkwShop\Domain\Model\Pages ::class => [
        'tableName' => 'pages',
        'properties' => [
            'uid' => [
                'fieldName' => 'uid'
            ],
            'pid' => [
                'fieldName' => 'pid'
            ],
            'sysLanguageUid' => [
                'fieldName' => 'sys_language_uid'
            ],
            'title' => [
                'fieldName' => 'title'
            ],
            'subtitle' => [
                'fieldName' => 'subtitle'
            ],
        ],
    ],
    \RKW\RkwShop\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    \RKW\RkwShop\Domain\Model\BackendUser::class => [
        'tableName' => 'be_users',
        'properties' => [
            'backendUserGroups' => [
                'fieldName' => 'usergroup'
            ],
        ],
    ],
    \RKW\RkwShop\Domain\Model\ShippingAddress::class => [
        'tableName' => 'tx_feregister_domain_model_shippingaddress',
    ],
    \RKW\RkwShop\Domain\Model\Order::class => [
        'properties' => [
            'tstamp' => [
                'fieldName' => 'tstamp'
            ],
            'crdate' => [
                'fieldName' => 'crdate'
            ],
            'hidden' => [
                'fieldName' => 'hidden'
            ],
            'deleted' => [
                'fieldName' => 'deleted'
            ],
        ],
    ],
    \RKW\RkwShop\Domain\Model\OrderItem::class => [
        'properties' => [
            'tstamp' => [
                'fieldName' => 'tstamp'
            ],
            'crdate' => [
                'fieldName' => 'crdate'
            ],
            'hidden' => [
                'fieldName' => 'hidden'
            ],
            'deleted' => [
                'fieldName' => 'deleted'
            ],
            'order' => [
                'fieldName' => 'ext_order'
            ]
        ],
    ],
    \RKW\RkwShop\Domain\Model\Product::class => [
        'properties' => [
            'tstamp' => [
                'fieldName' => 'tstamp'
            ],
            'crdate' => [
                'fieldName' => 'crdate'
            ],
            'hidden' => [
                'fieldName' => 'hidden'
            ],
            'deleted' => [
                'fieldName' => 'deleted'
            ],
        ],
        'recordType' => '0',
        'subclasses' => [
            '\RKW\RkwShop\Domain\Model\ProductBundle' => \RKW\RkwShop\Domain\Model\ProductBundle::class,
            '\RKW\RkwShop\Domain\Model\ProductSubscription' => \RKW\RkwShop\Domain\Model\ProductSubscription::class
        ]
    ],
    \RKW\RkwShop\Domain\Model\ProductBundle::class => [
        'tableName' => 'tx_rkwshop_domain_model_product',
        'recordType' => '\RKW\RkwShop\Domain\Model\ProductBundle'
    ],
    \RKW\RkwShop\Domain\Model\ProductSubscription::class => [
        'tableName' => 'tx_rkwshop_domain_model_product',
        'recordType' => '\RKW\RkwShop\Domain\Model\ProductSubscription'
    ],
    \RKW\RkwShop\Domain\Model\Category::class => [
        'tableName' => 'sys_category',
    ],
];
