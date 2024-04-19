<?php
return [
	'ctrl' => [
        'hideTable' => true,
        'title'	=> 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem',
        'label' => 'product',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
        'delete' => 'deleted',
        'searchFields' => 'uid, order, product, amount, is_pre_order',
		'iconfile' => 'EXT:rkw_shop/Resources/Public/Icons/tx_rkwshop_domain_model_orderitem.gif'
	],
	'types' => [
		'1' => ['showitem' => 'order, status, product, amount, is_pre_order'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

        'ext_order' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'status' => [
            'exclude' => 0,
            //'displayCond' => 'EXT:rkw_soap:LOADED:FALSE',
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.status',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.status.new', 0],
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.status.exported', 90],
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.status.sent', 100],
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.status.closed', 200]
                ]
            ],
        ],

        'product' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.product',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_rkwshop_domain_model_product',
                'foreign_table_where' => ' AND ((\'###PAGE_TSCONFIG_IDLIST###\' <> \'0\' AND FIND_IN_SET(tx_rkwshop_domain_model_product.pid,\'###PAGE_TSCONFIG_IDLIST###\')) OR (\'###PAGE_TSCONFIG_IDLIST###\' = \'0\')) AND tx_rkwshop_domain_model_product.hidden = 0 AND tx_rkwshop_domain_model_product.deleted = 0',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],

        'amount' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.amount',
			'config' => [
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			]
		],

        'is_pre_order' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_orderitem.isPreOrder',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
            ],
        ],
	],
];
