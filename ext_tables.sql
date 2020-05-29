#
# Table structure for table 'tx_rkwshop_domain_model_cart'
#
CREATE TABLE tx_rkwshop_domain_model_cart (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	frontend_user int(11) unsigned DEFAULT '0',
    frontend_user_session_hash varchar(32) DEFAULT '' NOT NULL,

	order_item text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);

#
# Table structure for table 'tx_rkwshop_domain_model_order'
#
CREATE TABLE tx_rkwshop_domain_model_order (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	frontend_user int(11) unsigned DEFAULT '0',
    frontend_user_session_hash varchar(32) DEFAULT '' NOT NULL,

    order_number varchar(255) DEFAULT '' NOT NULL,

	order_item text NOT NULL,
	shipping_address int(11) DEFAULT '0' NOT NULL,
	remark text NOT NULL,

	shipping_address_same_as_billing_address tinyint(4) unsigned DEFAULT '1' NOT NULL,

	canceled_at int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	status tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);

#
# Table structure for table 'tx_rkwshop_domain_model_orderitem'
#
CREATE TABLE tx_rkwshop_domain_model_orderitem (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	ext_order int(11) unsigned DEFAULT '0',
	ext_cart int(11) unsigned DEFAULT '0',
	product int(11) unsigned DEFAULT '0',
	amount int(11) unsigned DEFAULT '0',
	is_pre_order tinyint(4) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	status tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);

#
# Table structure for table 'tx_rkwshop_domain_model_product'
#
CREATE TABLE tx_rkwshop_domain_model_product (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	record_type varchar(255) DEFAULT '0' NOT NULL,
    product_type int(11) unsigned DEFAULT '0',

	sku varchar(255) DEFAULT '' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	subtitle varchar(255) DEFAULT '' NOT NULL,

	edition varchar(255) DEFAULT '' NOT NULL,

    description text,
	publishing_date int(11) unsigned DEFAULT '0' NOT NULL,
	author varchar(255) DEFAULT '' NOT NULL,
	page varchar(255) DEFAULT '' NOT NULL,
	image int(11) unsigned NOT NULL default '0',
	download int(11) unsigned NOT NULL default '0',
	product_bundle int(11) unsigned DEFAULT '0',
	allow_single_order tinyint(4) unsigned DEFAULT '0' NOT NULL,

	parent_products int(11) unsigned DEFAULT '0' NOT NULL,
	child_products int(11) unsigned DEFAULT '0' NOT NULL,

	stock varchar(255) DEFAULT '' NOT NULL,
    ordered_external int(11) unsigned DEFAULT '0' NOT NULL,

	backend_user varchar(255) DEFAULT '' NOT NULL,
	admin_email varchar(255) DEFAULT '' NOT NULL,

    comment text,

    tags int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_rkwshop_domain_model_product_product_mm'
#
CREATE TABLE tx_rkwshop_domain_model_product_product_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_rkwshop_domain_model_producttype'
#
CREATE TABLE tx_rkwshop_domain_model_producttype (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
    description text,
	model varchar(255) DEFAULT '' NOT NULL,
	has_sku tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_collection tinyint(4) unsigned DEFAULT '0' NOT NULL,

	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid)

);

#
# Table structure for table 'tx_rkwshop_domain_model_stock'
#
CREATE TABLE tx_rkwshop_domain_model_stock (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	product int(11) DEFAULT '0' NOT NULL,
	amount int(11) unsigned DEFAULT '500' NOT NULL,
	delivery_start int(11) unsigned DEFAULT '0' NOT NULL,
	comment varchar(255) DEFAULT '' NOT NULL,
    is_external tinyint(4) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);
