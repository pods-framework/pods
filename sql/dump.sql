/* Load core tables */

DROP TABLE IF EXISTS wp_pod;
CREATE TABLE wp_pod (
    id INT unsigned auto_increment primary key,
    tbl_row_id INT unsigned,
    datatype SMALLINT unsigned,
    name VARCHAR(128),
    created DATETIME,
    modified TIMESTAMP,
    author_id INT unsigned,
    INDEX datatype_idx (datatype)
) DEFAULT CHARSET utf8;

DROP TABLE IF EXISTS wp_pod_types;
CREATE TABLE wp_pod_types (
    id INT unsigned auto_increment primary key,
    name VARCHAR(32),
    label VARCHAR(128),
    is_toplevel BOOL default 0,
    detail_page VARCHAR(128),
    list_filters TEXT,
    pre_save_helpers TEXT,
    pre_drop_helpers TEXT,
    post_save_helpers TEXT,
    post_drop_helpers TEXT
) DEFAULT CHARSET utf8;

DROP TABLE IF EXISTS wp_pod_fields;
CREATE TABLE wp_pod_fields (
    id INT unsigned auto_increment primary key,
    datatype SMALLINT unsigned,
    name VARCHAR(32),
    label VARCHAR(128),
    comment VARCHAR(128),
    coltype VARCHAR(4),
    pickval VARCHAR(32),
    sister_field_id INT unsigned,
    weight SMALLINT unsigned default 0,
    display_helper TEXT,
    input_helper TEXT,
    pick_filter TEXT,
    pick_orderby TEXT,
    `required` TINYINT,
    `unique` TINYINT,
    `multiple` TINYINT,
    INDEX datatype_idx (datatype)
) DEFAULT CHARSET utf8;

DROP TABLE IF EXISTS wp_pod_rel;
CREATE TABLE wp_pod_rel (
    id INT unsigned auto_increment primary key,
    pod_id INT unsigned,
    sister_pod_id INT unsigned,
    field_id INT unsigned,
    tbl_row_id INT unsigned,
    weight SMALLINT unsigned default 0,
    INDEX field_id_idx (field_id)
) DEFAULT CHARSET utf8;

DROP TABLE IF EXISTS wp_pod_templates;
CREATE TABLE wp_pod_templates (
    id INT unsigned auto_increment primary key,
    name VARCHAR(32),
    code LONGTEXT
) DEFAULT CHARSET utf8;

DROP TABLE IF EXISTS wp_pod_pages;
CREATE TABLE wp_pod_pages (
    id INT unsigned auto_increment primary key,
    uri VARCHAR(128),
    title VARCHAR(128),
    phpcode LONGTEXT,
    precode LONGTEXT,
    page_template VARCHAR(128)
) DEFAULT CHARSET utf8;

DROP TABLE IF EXISTS wp_pod_helpers;
CREATE TABLE wp_pod_helpers (
    id INT unsigned auto_increment primary key,
    name VARCHAR(32),
    helper_type VARCHAR(16) not null default 'display',
    phpcode LONGTEXT
) DEFAULT CHARSET utf8;

DROP TABLE IF EXISTS wp_pod_menu;
CREATE TABLE wp_pod_menu (
    id INT unsigned auto_increment primary key,
    uri VARCHAR(128),
    title VARCHAR(128),
    lft INT unsigned,
    rgt INT unsigned,
    weight SMALLINT unsigned default 0
) DEFAULT CHARSET utf8;

/* Load some default pods */

DROP TABLE IF EXISTS wp_pod_tbl_state;
CREATE TABLE wp_pod_tbl_state (
    id INT unsigned auto_increment primary key,
    name VARCHAR(64),
    abbrev CHAR(64)
) DEFAULT CHARSET utf8;

INSERT INTO wp_pod_menu (uri, title, lft, rgt) VALUES ('<root>', '<root>', 1, 2);

INSERT INTO wp_pod_types (name, label, detail_page) VALUES ('state','States','states/{@abbrev}');

INSERT INTO wp_pod_pages (uri, phpcode) VALUES
('states', "<?php\n$Record = new Pod('state');\n$Record->findRecords('name ASC', 10);\necho $Record->getPagination();\necho $Record->showTemplate('state_list');\n?>"),
('states/*', "<?php\n// Get the last URL variable\n$id = pods_url_variable('last');\n\n$Record = new Pod('state', $id);\necho $Record->showTemplate('state_detail');\n?>");

INSERT INTO wp_pod_templates (name, code) VALUES
('state_list', '<p><a href="{@detail_url}">{@name}</a></p>'),
('state_detail', '<h2>{@name}</h2>\n<div>Abbrev: {@abbrev}</div>');

INSERT INTO wp_pod_helpers (name, helper_type, phpcode) VALUES
('format_date', 'display', '<?php echo date("m/d/Y", strtotime($value)); ?>');

INSERT INTO wp_pod_tbl_state (name, abbrev) VALUES
('Alabama','al'),('Alaska','ak'),('Arizona','az'),('Arkansas','ar'),('California','ca'),('Colorado','co'),('Connecticut','ct'),('Delaware','de'),('District of Columbia','dc'),('Florida','fl'),('Georgia','ga'),('Hawaii','hi'),('Idaho','id'),('Illinois','il'),('Indiana','in'),('Iowa','ia'),('Kansas','ks'),('Kentucky','ky'),('Louisiana','la'),('Maine','me'),('Maryland','md'),('Massachussetts','ma'),('Michigan','mi'),('Minnesota','mn'),('Mississippi','ms'),('Missouri','mo'),('Montana','mt'),('Nebraska','ne'),('Nevada','nv'),('New Hampshire','nh'),('New Mexico','nm'),('New Jersey','nj'),('New York','ny'),('North Carolina','nc'),('North Dakota','nd'),('Ohio','oh'),('Oklahoma','ok'),('Oregon','or'),('Pennsylvania','pa'),('Rhode Island','ri'),('South Carolina','sc'),('South Dakota','sd'),('Tennessee','tn'),('Texas','tx'),('Utah','ut'),('Virginia','va'),('Vermont','vt'),('Washington','wa'),('West Virginia','wv'),('Wisconsin','wi'),('Wyoming','wy');

INSERT INTO wp_pod_fields (datatype, name, label, comment, coltype, pickval, sister_field_id, required, weight) VALUES
(1,'name',NULL,NULL,'txt',NULL,0,1,0),
(1,'abbrev',NULL,NULL,'slug',NULL,0,0,1);

INSERT INTO wp_pod (tbl_row_id, datatype, name, created) VALUES
(1,1,'Alabama',NOW()),
(2,1,'Alaska',NOW()),
(3,1,'Arizona',NOW()),
(4,1,'Arkansas',NOW()),
(5,1,'California',NOW()),
(6,1,'Colorado',NOW()),
(7,1,'Connecticut',NOW()),
(8,1,'Delaware',NOW()),
(9,1,'District of Columbia',NOW()),
(10,1,'Florida',NOW()),
(11,1,'Georgia',NOW()),
(12,1,'Hawaii',NOW()),
(13,1,'Idaho',NOW()),
(14,1,'Illinois',NOW()),
(15,1,'Indiana',NOW()),
(16,1,'Iowa',NOW()),
(17,1,'Kansas',NOW()),
(18,1,'Kentucky',NOW()),
(19,1,'Louisiana',NOW()),
(20,1,'Maine',NOW()),
(21,1,'Maryland',NOW()),
(22,1,'Massachussetts',NOW()),
(23,1,'Michigan',NOW()),
(24,1,'Minnesota',NOW()),
(25,1,'Mississippi',NOW()),
(26,1,'Missouri',NOW()),
(27,1,'Montana',NOW()),
(28,1,'Nebraska',NOW()),
(29,1,'Nevada',NOW()),
(30,1,'New Hampshire',NOW()),
(31,1,'New Mexico',NOW()),
(32,1,'New Jersey',NOW()),
(33,1,'New York',NOW()),
(34,1,'North Carolina',NOW()),
(35,1,'North Dakota',NOW()),
(36,1,'Ohio',NOW()),
(37,1,'Oklahoma',NOW()),
(38,1,'Oregon',NOW()),
(39,1,'Pennsylvania',NOW()),
(40,1,'Rhode Island',NOW()),
(41,1,'South Carolina',NOW()),
(42,1,'South Dakota',NOW()),
(43,1,'Tennessee',NOW()),
(44,1,'Texas',NOW()),
(45,1,'Utah',NOW()),
(46,1,'Virginia',NOW()),
(47,1,'Vermont',NOW()),
(48,1,'Washington',NOW()),
(49,1,'West Virginia',NOW()),
(50,1,'Wisconsin',NOW()),
(51,1,'Wyoming',NOW());