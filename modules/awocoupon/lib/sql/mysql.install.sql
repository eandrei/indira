CREATE TABLE IF NOT EXISTS #__awocoupon (
	`id` int(16) NOT NULL auto_increment,
	`coupon_code` varchar(32) BINARY NOT NULL default '',
	`passcode` varchar(10),
	`coupon_value_type` enum('percent','amount') DEFAULT NULL,
	`coupon_value` decimal(12,5),
	`coupon_value_def` TEXT,
	`function_type` enum('coupon','giftcert','shipping','parent','buy_x_get_y') NOT NULL DEFAULT 'coupon',
	`num_of_uses_total` INT,
	`num_of_uses_percustomer` INT,
	`min_value` decimal(12,5),
	`discount_type` enum('specific','overall'),
	`user_type` enum('user','usergroup'),
	`startdate` DATETIME,
	`expiration` DATETIME,
	`order_id` int(11),
	`template_id` int(11),
	`published` TINYINT NOT NULL DEFAULT 1,
	`description` VARCHAR(255),
	`note` TEXT,
	`params` TEXT,
	PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_license (
	`id` VARCHAR(100) NOT NULL,
	`value` TEXT,
	PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_cart (
	`id_cart` int(16),
	`new_ids` varchar(255),
	`coupon_data` TEXT,
	PRIMARY KEY  (`id_cart`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_config (
	`id` int(16) NOT NULL auto_increment,
	`name` VARCHAR(255) NOT NULL,
	`value` TEXT,
	PRIMARY KEY  (`id`),
	UNIQUE (`name`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_asset1 (
	`id` int(16) NOT NULL auto_increment,
	`coupon_id` varchar(32) NOT NULL default '',
	`asset_type` enum('coupon','product','category','manufacturer','vendor','shipping','country','countrystate') NOT NULL,
	`asset_id` INT NOT NULL,
	`order_by` INT NULL,
	PRIMARY KEY  (`id`)
);
CREATE TABLE IF NOT EXISTS #__awocoupon_asset2 (
	`id` int(16) NOT NULL auto_increment,
	`coupon_id` varchar(32) NOT NULL default '',
	`asset_type` enum('coupon','product','category','manufacturer','vendor','shipping') NOT NULL,
	`asset_id` INT NOT NULL,
	`order_by` INT NULL,
	PRIMARY KEY  (`id`)
);


CREATE TABLE IF NOT EXISTS #__awocoupon_user (
	`id` int(16) NOT NULL auto_increment,
	`coupon_id` varchar(32) NOT NULL default '',
	`user_id` INT NOT NULL,
	PRIMARY KEY  (`id`)
);
CREATE TABLE IF NOT EXISTS #__awocoupon_usergroup (
	`id` int(16) NOT NULL auto_increment,
	`coupon_id` varchar(32) NOT NULL default '',
	`shopper_group_id` INT NOT NULL,
	PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_history (
	`id` INT NOT NULL auto_increment,
	`coupon_id` varchar(32) NOT NULL default '',
	`coupon_entered_id` varchar(32),
	`user_id` INT NOT NULL,
	`user_email` varchar(255),
	`coupon_discount` DECIMAL(12,5) DEFAULT 0 NOT NULL,
	`shipping_discount` DECIMAL(12,5) DEFAULT 0 NOT NULL,
	`order_id` INT,
	`details` TEXT,
	`productids` TEXT,
	`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_profile (
	`id` int(16) NOT NULL auto_increment,
	`title` VARCHAR(255) NOT NULL,
	`is_default` TINYINT(1),
	`from_name` VARCHAR(255),
	`from_email` VARCHAR(255),
	`bcc_admin` TINYINT(1),
	`email_subject` VARCHAR(255),
	`message_type` ENUM ('text','html') NOT NULL DEFAULT 'text',
	`image` VARCHAR(255),
	`coupon_code_config` TEXT,
	`coupon_value_config` TEXT,
	`expiration_config` TEXT,
	`freetext1_config` TEXT,
	`freetext2_config` TEXT,
	`freetext3_config` TEXT,
	`is_pdf` TINYINT(1),
	`email_body` TEXT,
	PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_profile_lang (
	`profile_id` int(16) NOT NULL,
	`id_lang` int(16) NOT NULL,
	`title` VARCHAR(255),
	`email_subject` VARCHAR(255),
	`email_body` TEXT,
	`pdf_header` TEXT,
	`pdf_body` TEXT,
	`pdf_footer` TEXT,
	PRIMARY KEY  (`profile_id`,`id_lang`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_giftcert_product (
	`id` int(16) NOT NULL auto_increment,
	`product_id` INT NOT NULL,
	`coupon_template_id` INT NOT NULL,
	`profile_id` INT,
	`expiration_number` INT,
	`expiration_type` ENUM('day','month','year'),
	`recipient_email_id` INT,
	`recipient_name_id` INT,
	`recipient_mesg_id` INT,
	`vendor_name` VARCHAR(255),
	`vendor_email` VARCHAR(255),
	`published` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_giftcert_code (
	`id` int(16) NOT NULL auto_increment,
	`product_id` INT NOT NULL,
	`code` VARCHAR(255) BINARY NOT NULL default '',
	`status` ENUM('active','inactive','used') NOT NULL DEFAULT 'active',
	`note` TEXT,
	PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_giftcert_order (
	`id` int(16) NOT NULL auto_increment,
	`order_id` int(11) NOT NULL,
	`user_id` INT NOT NULL,
	`email_sent` tinyint(1) NOT NULL DEFAULT '0',
	`codes` text,
	PRIMARY KEY (`id`),
	UNIQUE (order_id)
);



CREATE TABLE IF NOT EXISTS #__awocoupon_giftcert_order_code (
	`id` int(16) NOT NULL auto_increment,
	`giftcert_order_id` int(16) NOT NULL,
	`order_item_id` int(16) NOT NULL,
	`product_id` int(16) NOT NULL,
	`coupon_id` int(16) NOT NULL,
	`code` VARCHAR(255) NOT NULL,
	`recipient_user_id` INT,
	PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_image (
	`coupon_id` INT NOT NULL,
	`user_id` INT NOT NULL,
	`filename` varchar(255),
	PRIMARY KEY  (`coupon_id`)
);



CREATE TABLE IF NOT EXISTS #__awocoupon_shop (
	`coupon_id` int(10) unsigned NOT NULL,
	`id_shop` int(10) unsigned NOT NULL,
	PRIMARY KEY (`coupon_id`,`id_shop`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_lang (
	`id` int(16) NOT NULL auto_increment,
	`elem_id` int(16) NOT NULL,
	`id_lang` int(16) NOT NULL,
	`text` TEXT,
	PRIMARY KEY  (`id`),
	UNIQUE KEY  (`elem_id`,`id_lang`)
);

CREATE TABLE IF NOT EXISTS #__awocoupon_tag (
	`coupon_id` int(16) NOT NULL,
	`tag` VARCHAR(255) NOT NULL,
	PRIMARY KEY  (`coupon_id`,`tag`)
);
 
CREATE TABLE IF NOT EXISTS #__awocoupon_auto (
	`id` int(16) NOT NULL auto_increment,
	`coupon_id` varchar(32) NOT NULL default '',
	`ordering` INT NULL,
	`published` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY  (`id`)
);










DELETE FROM #__awocoupon_profile WHERE id IN (1,2,3,4,5);
DELETE FROM #__awocoupon_profile_lang WHERE profile_id IN (1,2,3,4,5);

INSERT INTO #__awocoupon_profile (id,title,is_default,from_name,from_email,email_subject,message_type,email_body)
VALUES (1,"Profile 1",1,"","",
		"Ordered Gift Certificate(s)",
		"text",
		"Included is your gift certificate valid towards all products at {siteurl}.\r\nSimply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!\r\n\r\n{vouchers}\r\n\r\nThank you,\r\n{store_name}"
);
INSERT INTO #__awocoupon_profile_lang(profile_id,id_lang,title,email_subject,email_body)
VALUES (1,1,"Profile 1","Ordered Gift Certificate(s)","Included is your gift certificate valid towards all products at {siteurl}.\r\nSimply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!\r\n\r\n{vouchers}\r\n\r\nThank you,\r\n{store_name}"
);


INSERT INTO #__awocoupon_profile (id,title,email_subject,message_type,image,coupon_code_config,coupon_value_config,expiration_config,freetext1_config,freetext2_config,email_body)
VALUES (2,"Christmas","Ordered Gift Certificate(s)","html","christmas.png",
		'a:6:{s:5:"align";s:1:"R";s:3:"pad";s:2:"10";s:1:"y";s:2:"72";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"16";s:5:"color";s:7:"#FFFF00";}',
		'a:6:{s:5:"align";s:1:"L";s:3:"pad";s:2:"50";s:1:"y";s:3:"110";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"25";s:5:"color";s:7:"#FFFF00";}',
		'a:7:{s:4:"text";s:5:"F j Y";s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:3:"270";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"14";s:5:"color";s:7:"#FFA500";}',
		'a:7:{s:4:"text";s:5:"CODE:";s:5:"align";s:1:"R";s:3:"pad";s:2:"75";s:1:"y";s:2:"50";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"14";s:5:"color";s:7:"#FFA500";}',
		'a:7:{s:4:"text";s:19:"www.yourwebsite.com";s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:3:"200";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"25";s:5:"color";s:7:"#FFFAFA";}',
		"Included is your gift certificate valid towards all products at {siteurl}.<br />Simply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!<br /><br />Thank you,<br />{store_name}"
);
INSERT INTO #__awocoupon_profile_lang(profile_id,id_lang,title,email_subject,email_body)
VALUES (2,1,"Christmas","Ordered Gift Certificate(s)",		"Included is your gift certificate valid towards all products at {siteurl}.<br />Simply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!<br /><br />Thank you,<br />{store_name}"
);


INSERT INTO #__awocoupon_profile (id,title,email_subject,message_type,image,coupon_code_config,coupon_value_config,expiration_config,freetext1_config,freetext2_config,email_body)
VALUES (3,"Flower","Ordered Gift Certificate(s)","html","flower.png",
		'a:6:{s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:3:"280";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"14";s:5:"color";s:7:"#000000";}',
		'a:6:{s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:3:"250";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"25";s:5:"color";s:7:"#000000";}',
		NULL,
		'a:7:{s:4:"text";s:19:"www.yourwebsite.com";s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:2:"30";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"25";s:5:"color";s:7:"#FFD700";}',
		'a:7:{s:4:"text";s:10:"Thank you!";s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:2:"70";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"20";s:5:"color";s:7:"#FF69B4";}',
		"Included is your gift certificate valid towards all products at {siteurl}.<br />Simply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!<br /><br />Thank you,<br />{store_name}"
);
INSERT INTO #__awocoupon_profile_lang(profile_id,id_lang,title,email_subject,email_body)
VALUES (3,1,"Flower","Ordered Gift Certificate(s)",		"Included is your gift certificate valid towards all products at {siteurl}.<br />Simply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!<br /><br />Thank you,<br />{store_name}"
);


INSERT INTO #__awocoupon_profile (id,title,email_subject,message_type,image,coupon_code_config,coupon_value_config,expiration_config,freetext1_config,freetext2_config,email_body)
VALUES (4,"Brown","Ordered Gift Certificate(s)","html","brown.png",
		'a:6:{s:5:"align";s:1:"R";s:3:"pad";s:2:"20";s:1:"y";s:2:"50";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"18";s:5:"color";s:7:"#FFFFFF";}',
		'a:6:{s:5:"align";s:1:"L";s:3:"pad";s:2:"20";s:1:"y";s:2:"50";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"25";s:5:"color";s:7:"#FFFFFF";}',
		'a:7:{s:4:"text";s:5:"j F Y";s:5:"align";s:1:"R";s:3:"pad";s:2:"50";s:1:"y";s:2:"80";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"15";s:5:"color";s:7:"#F0F8FF";}',
		'a:7:{s:4:"text";s:9:"GIFT CARD";s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:3:"260";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"30";s:5:"color";s:7:"#000000";}',
		'a:7:{s:4:"text";s:19:"www.yourwebsite.com";s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:3:"180";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"30";s:5:"color";s:7:"#8B0000";}',
		"Included is your gift certificate valid towards all products at {siteurl}.<br />Simply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!<br /><br />Thank you,<br />{store_name}"
);
INSERT INTO #__awocoupon_profile_lang(profile_id,id_lang,title,email_subject,email_body)
VALUES (4,1,"Brown","Ordered Gift Certificate(s)",		"Included is your gift certificate valid towards all products at {siteurl}.<br />Simply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!<br /><br />Thank you,<br />{store_name}"
);

INSERT INTO #__awocoupon_profile (id,title,email_subject,message_type,image,coupon_code_config,coupon_value_config,expiration_config,freetext1_config,freetext2_config,email_body)
VALUES (5,"Snowman","Ordered Gift Certificate(s)","html","snowman.png",
		'a:6:{s:5:"align";s:1:"R";s:3:"pad";s:2:"30";s:1:"y";s:2:"60";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"14";s:5:"color";s:7:"#000000";}',
		'a:6:{s:5:"align";s:1:"L";s:3:"pad";s:2:"30";s:1:"y";s:2:"60";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"22";s:5:"color";s:7:"#000000";}',
		'a:7:{s:4:"text";s:5:"j M Y";s:5:"align";s:1:"R";s:3:"pad";s:2:"30";s:1:"y";s:2:"90";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"14";s:5:"color";s:7:"#000000";}',
		'a:7:{s:4:"text";s:19:"www.yourwebsite.com";s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:3:"170";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"32";s:5:"color";s:7:"#0000FF";}',
		'a:7:{s:4:"text";s:28:"Enjoy your shopping with us!";s:5:"align";s:1:"C";s:3:"pad";s:0:"";s:1:"y";s:3:"260";s:4:"font";s:11:"arialbd.ttf";s:4:"size";s:2:"20";s:5:"color";s:7:"#B22222";}',
		"Included is your gift certificate valid towards all products at {siteurl}.<br />Simply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!<br /><br />Thank you,<br />{store_name}"
);
INSERT INTO #__awocoupon_profile_lang(profile_id,id_lang,title,email_subject,email_body)
VALUES (5,1,"Snowman","Ordered Gift Certificate(s)",		"Included is your gift certificate valid towards all products at {siteurl}.<br />Simply enter the code from your gift certificate in the coupon code entry form during checkout. Enjoy shopping!<br /><br />Thank you,<br />{store_name}"
);



