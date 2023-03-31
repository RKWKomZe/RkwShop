<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order',
        'label' => 'status',
        'label_alt' => 'email',
        'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
		],
        'default_sortby' => 'ORDER BY status ASC, email ASC',
        'searchFields' => 'uid, amount,first_name,last_name,address,zip,city,email,frontend_user,pages,remark,',
		'iconfile' => 'EXT:rkw_shop/Resources/Public/Icons/tx_rkwshop_domain_model_order.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'hidden, status, email, target_group, frontend_user, shipping_address, order_item, remark, target_category',
	],
	'types' => [
		'1' => ['showitem' => 'hidden,--palette--;;1, status, email, target_group, frontend_user, shipping_address, order_item, remark, target_category'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

		'hidden' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'status' => [
			'exclude' => 0,
            //'displayCond' => 'EXT:rkw_soap:LOADED:FALSE',
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.status',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
				'items' => [
					['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.status.new', 0],
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.status.exported', 90],
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.status.sent', 100],
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.status.closed', 200]
				]
			],
		],
		'frontend_user' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.frontendUser',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'fe_users',
				'foreign_table_where' => 'AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY username ASC',
				'minitems' => 1,
				'maxitems' => 1,
                'readOnly' => true
			],
		],
        'target_group' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_events/Resources/Private/Language/locallang_db.xlf:tx_rkwevents_domain_model_event.target_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_rkwbasics_domain_model_targetgroup',
                'foreign_table_where' => 'AND tx_rkwbasics_domain_model_targetgroup.deleted = 0 ORDER BY name ASC',
                'minitems' => 1,
                'maxitems' => 1,
                'readOnly' => true
            ],
        ],
        'email' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,email,required',
                'readOnly' => true
            ],
        ],
        'order_item' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.orderItem',
            'config' => [
                'type' => 'inline',
                'internal_type' => 'db',
                'foreign_table' => 'tx_rkwshop_domain_model_orderitem',
                'foreign_field' => 'ext_order',
                'minitems' => 1,
                'maxitems' => 99,
                'size'  => 5,
                'show_thumbs' =>  true,
                'appearance' => [
                    'elementBrowserType' => 'db',
                    'useSortable' => false,
                    'showPossibleLocalizationRecords' => false,
                    'showRemovedLocalizationRecords' => false,
                    'showSynchronizationLink' => false,
                    'showAllLocalizationLink' => false,
                    'enabledControls' => [
                        'info' => true,
                        'new' => true,
                        'dragdrop' => false,
                        'sort' => false,
                        'hide' => false,
                        'delete' => true,
                        'localize' => false,
                    ],
                ],
                'readOnly' => true
            ],
        ],
        'shipping_address' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.shippingAddress',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_rkwregistration_domain_model_shippingaddress',
                'foreign_table_where' => 'AND tx_rkwregistration_domain_model_shippingaddress.deleted = 0 AND tx_rkwregistration_domain_model_shippingaddress.hidden = 0 ORDER BY tx_rkwregistration_domain_model_shippingaddress.address ASC',
                'minitems' => 1,
                'maxitems' => 1,
                'readOnly' => true
            ],
        ],
		'remark' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_order.remark',
			'config' => [
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim',
                'readOnly' => true
			]
		],
        'shipped_tstamp' => [
            'exclude'     => 0,
            'label'       => 'LLL:EXT:rkw_newsletter/Resources/Private/Language/locallang_db.xlf:pages.tx_rkwnewsletter_include_tstamp',
            'config'      => [
                'type'       => 'input',
                'renderType' => 'inputDateTime',
                'eval'       => 'datetime,int',
                'default'    => 0,
            ],
        ],
	],
];
