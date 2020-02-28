#
# Table structure for table "tx_rkwshop_domain_model_producttype"
#
DROP TABLE IF EXISTS tx_rkwshop_domain_model_producttype;
CREATE TABLE tx_rkwshop_domain_model_producttype (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
    description text,
	has_article_number tinyint(4) unsigned DEFAULT '0' NOT NULL,
	allow_single_order tinyint(4) unsigned DEFAULT '0' NOT NULL,

	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid)

);

INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('1', '0', 'Typ #1', 'Description Typ #1', '0', '1', '0', '0');
INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('2', '0', 'Typ #2', 'Description Typ #2', '0', '1', '0', '0');
INSERT INTO tx_rkwshop_domain_model_producttype VALUES ('3', '0', 'Typ #3', 'Description Typ #3', '0', '1', '0', '0');

