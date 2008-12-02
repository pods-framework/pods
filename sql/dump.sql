/* Load core tables */

CREATE TABLE IF NOT EXISTS wp_pod (
    id INT unsigned auto_increment primary key,
    row_id INT unsigned,
    post_id INT unsigned,
    datatype TINYINT unsigned
);

CREATE TABLE IF NOT EXISTS wp_pod_types (
    id INT unsigned auto_increment primary key,
    name VARCHAR(32),
    label VARCHAR(32),
    list_filters TEXT,
    tpl_detail TEXT,
    tpl_list TEXT
);

CREATE TABLE IF NOT EXISTS wp_pod_fields (
    id INT unsigned auto_increment primary key,
    datatype TINYINT unsigned,
    name VARCHAR(32),
    coltype VARCHAR(4),
    pickval VARCHAR(32),
    sister_field_id INT unsigned,
    required BOOL default 0,
    weight TINYINT
);

CREATE TABLE IF NOT EXISTS wp_pod_rel (
    id INT unsigned auto_increment primary key,
    post_id INT unsigned,
    sister_post_id INT unsigned,
    field_id INT unsigned,
    term_id INT unsigned
);

CREATE TABLE IF NOT EXISTS wp_pod_pages (
    id INT unsigned auto_increment primary key,
    uri VARCHAR(128),
    phpcode TEXT
);

CREATE TABLE IF NOT EXISTS wp_pod_widgets (
    id INT unsigned auto_increment primary key,
    name VARCHAR(32),
    phpcode TEXT
);

/* Load some default pods */

CREATE TABLE IF NOT EXISTS tbl_country (
    id INT unsigned auto_increment primary key,
    name VARCHAR(64)
);

CREATE TABLE IF NOT EXISTS tbl_state (
    id INT unsigned auto_increment primary key,
    name VARCHAR(64)
);

CREATE TABLE IF NOT EXISTS tbl_person (
    id INT unsigned auto_increment primary key,
    name VARCHAR(128),
    body TEXT,
    photo VARCHAR(128),
    job_title VARCHAR(128),
    employer VARCHAR(128),
    phone VARCHAR(128),
    email VARCHAR(128)
);

CREATE TABLE IF NOT EXISTS tbl_event (
    id INT unsigned auto_increment primary key,
    name VARCHAR(128),
    body TEXT,
    start_date DATETIME,
    end_date DATETIME,
    address TEXT,
    contact_phone VARCHAR(128)
);

TRUNCATE TABLE tbl_state;
TRUNCATE TABLE tbl_country;
INSERT INTO tbl_country (name) VALUES ('Afghanistan'),('Aland Islands'),('Albania'),('Algeria'),('American Samoa'),('Andorra'),('Angola'),('Anguilla'),('Antarctica'),('Antigua And Barbuda'),('Argentina'),('Armenia'),('Aruba'),('Australia'),('Austria'),('Azerbaijan'),('Bahamas'),('Bahrain'),('Bangladesh'),('Barbados'),('Belarus'),('Belgium'),('Belize'),('Benin'),('Bermuda'),('Bhutan'),('Bolivia'),('Bosnia and Herzegowina'),('Botswana'),('Bouvet Island'),('Brazil'),('British Indian Ocean Territory'),('Brunei Darussalam'),('Bulgaria'),('Burkina Faso'),('Burundi'),('Cambodia'),('Cameroon'),('Canada'),('Cape Verde'),('Cayman Islands'),('Central African Republic'),('Chad'),('Chile'),('China'),('Christmas Island'),('Cocos (Keeling) Islands'),('Colombia'),('Comoros'),('Congo'),('Congo, the Democratic Republic of the'),('Cook Islands'),('Costa Rica'),('Cote d\'Ivoire'),('Croatia'),('Cuba'),('Cyprus'),('Czech Republic'),('Denmark'),('Djibouti'),('Dominica'),('Dominican Republic'),('Ecuador'),('Egypt'),('El Salvador'),('Equatorial Guinea'),('Eritrea'),('Estonia'),('Ethiopia'),('Falkland Islands (Malvinas)'),('Faroe Islands'),('Fiji'),('Finland'),('France'),('French Guiana'),('French Polynesia'),('French Southern Territories'),('Gabon'),('Gambia'),('Georgia'),('Germany'),('Ghana'),('Gibraltar'),('Great Britain'),('Greece'),('Greenland'),('Grenada'),('Guadeloupe'),('Guam'),('Guatemala'),('Guernsey'),('Guinea'),('Guinea-Bissau'),('Guyana'),('Haiti'),('Heard and McDonald Islands'),('Holy See (Vatican City State)'),('Honduras'),('Hong Kong'),('Hungary'),('Iceland'),('India'),('Indonesia'),('Iran, Islamic Republic of'),('Iraq'),('Ireland'),('Isle of Man'),('Israel'),('Italy'),('Jamaica'),('Japan'),('Jersey'),('Jordan'),('Kazakhstan'),('Kenya'),('Kiribati'),('Korea, Democratic People\'s Republic of'),('Korea, Republic of'),('Kuwait'),('Kyrgyzstan'),('Lao People\'s Democratic Republic'),('Latvia'),('Lebanon'),('Lesotho'),('Liberia'),('Libyan Arab Jamahiriya'),('Liechtenstein'),('Lithuania'),('Luxembourg'),('Macao'),('Macedonia, The Former Yugoslav Republic Of'),('Madagascar'),('Malawi'),('Malaysia'),('Maldives'),('Mali'),('Malta'),('Marshall Islands'),('Martinique'),('Mauritania'),('Mauritius'),('Mayotte'),('Mexico'),('Micronesia, Federated States of'),('Moldova, Republic of'),('Monaco'),('Mongolia'),('Montenegro'),('Montserrat'),('Morocco'),('Mozambique'),('Myanmar'),('Namibia'),('Nauru'),('Nepal'),('Netherlands'),('Netherlands Antilles'),('New Caledonia'),('New Zealand'),('Nicaragua'),('Niger'),('Nigeria'),('Niue'),('Norfolk Island'),('Northern Mariana Islands'),('Norway'),('Oman'),('Pakistan'),('Palau'),('Palestinian Territory, Occupied'),('Panama'),('Papua New Guinea'),('Paraguay'),('Peru'),('Philippines'),('Pitcairn'),('Poland'),('Portugal'),('Puerto Rico'),('Qatar'),('Reunion'),('Romania'),('Russian Federation'),('Rwanda'),('Saint Barthelemy'),('Saint Helena'),('Saint Kitts and Nevis'),('Saint Lucia'),('Saint Pierre and Miquelon'),('Saint Vincent and the Grenadines'),('Samoa'),('San Marino'),('Sao Tome and Principe'),('Saudi Arabia'),('Senegal'),('Serbia'),('Seychelles'),('Sierra Leone'),('Singapore'),('Slovakia'),('Slovenia'),('Solomon Islands'),('Somalia'),('South Africa'),('South Georgia and the South Sandwich Islands'),('Spain'),('Sri Lanka'),('Sudan'),('Suriname'),('Svalbard and Jan Mayen'),('Swaziland'),('Sweden'),('Switzerland'),('Syrian Arab Republic'),('Taiwan, Province of China'),('Tajikistan'),('Tanzania, United Republic of'),('Thailand'),('Timor-Leste'),('Togo'),('Tokelau'),('Tonga'),('Trinidad and Tobago'),('Tunisia'),('Turkey'),('Turkmenistan'),('Turks and Caicos Islands'),('Tuvalu'),('Uganda'),('Ukraine'),('United Arab Emirates'),('United Kingdom'),('United States'),('United States Minor Outlying Islands'),('Uruguay'),('Uzbekistan'),('Vanuatu'),('Venezuela'),('Viet Nam'),('Virgin Islands, British'),('Virgin Islands, U.S.'),('Wallis and Futuna'),('Western Sahara'),('Yemen'),('Zambia'),('Zimbabwe');
INSERT INTO tbl_state (name) VALUES ('Alabama'),('Alaska'),('Arizona'),('Arkansas'),('California'),('Colorado'),('Connecticut'),('Delaware'),('District of Columbia'),('Florida'),('Georgia'),('Hawaii'),('Idaho'),('Illinois'),('Indiana'),('Iowa'),('Kansas'),('Kentucky'),('Louisiana'),('Maine'),('Maryland'),('Massachussetts'),('Michigan'),('Minnesota'),('Mississippi'),('Missouri'),('Montana'),('Nebraska'),('Nevada'),('New Hampshire'),('New Mexico'),('New Jersey'),('New York'),('North Carolina'),('North Dakota'),('Ohio'),('Oklahoma'),('Oregon'),('Pennsylvania'),('Rhode Island'),('South Carolina'),('South Dakota'),('Tennessee'),('Texas'),('Utah'),('Virginia'),('Vermont'),('Washington'),('West Virginia'),('Wisconsin'),('Wyoming');
INSERT INTO wp_pod_types VALUES (1,'event',NULL,'','<h2>{@name}</h2>\n<p><b>Start Date:</b> {@start_date,format_date}</p>\n<p><b>End Date:</b> {@end_date,format_date}</p>\n<p><b>Contact Name:</b> {@contact_name}</p>\n<p>{@body}</p>','<p><a href=\"{@detail_url}\">{@name}</a> - {@start_date,format_date}</p>');
INSERT INTO wp_pod_types VALUES (2,'person',NULL,'','<h2>{@name}</h2>\n<img src=\"{@photo}\" alt=\"{@name}\" />\n<p>{@job_title}</p>\n<p>{@body}</p>','<p><a href=\"{@detail_url}\">{@name}</a></p>');
INSERT INTO wp_pod_fields VALUES (1,1,'name',NULL,'txt',NULL,NULL,0,0);
INSERT INTO wp_pod_fields VALUES (2,1,'body',NULL,'desc',NULL,NULL,0,1);
INSERT INTO wp_pod_fields VALUES (3,2,'name',NULL,'txt',NULL,NULL,0,0);
INSERT INTO wp_pod_fields VALUES (4,2,'body',NULL,'desc',NULL,NULL,0,1);
INSERT INTO wp_pod_fields VALUES (5,1,'start_date','Start Date','date','',0,1,2);
INSERT INTO wp_pod_fields VALUES (6,1,'end_date','End Date','date','',0,1,3);
INSERT INTO wp_pod_fields VALUES (7,1,'address','Address','desc','',0,0,4);
INSERT INTO wp_pod_fields VALUES (8,1,'country','Country','pick','country',0,0,5);
INSERT INTO wp_pod_fields VALUES (9,1,'contact_name','Contact Name','pick','person',0,0,6);
INSERT INTO wp_pod_fields VALUES (10,1,'contact_phone','Contact Phone','txt','',0,0,7);
INSERT INTO wp_pod_fields VALUES (11,2,'photo','Photo','file','',0,0,2);
INSERT INTO wp_pod_fields VALUES (12,2,'job_title','Job Title','txt','',0,0,3);
INSERT INTO wp_pod_fields VALUES (13,2,'employer','Employer','txt','',0,0,4);
INSERT INTO wp_pod_fields VALUES (14,2,'phone','phone','txt','',0,0,5);
INSERT INTO wp_pod_fields VALUES (15,2,'email','email','txt','',0,0,6);
INSERT INTO wp_pod_pages VALUES (1,'/list/','$type = empty($type) ? \'news\' : $type;\n\n$Record = new Pod($type);\n$Record->findRecords(\'id DESC\');\n?>\n\n<h2><?php echo ucwords($type); ?> Listing</h2>\n\n<?php\necho $Record->getFilters();\necho $Record->getPagination();\necho $Record->showTemplate(\'list\');');
INSERT INTO wp_pod_pages VALUES (2,'/detail/','if (ctype_digit($id))\n{\n    $type = empty($type) ? \'news\' : $type;\n    $Record = new Pod($type, $id);\n    echo $Record->showTemplate(\'detail\');\n}');
INSERT INTO wp_pod_widgets VALUES (1,'format_date','echo date(\"m/d/Y\", strtotime($value));');
INSERT INTO wp_pod_widgets VALUES (2,'mp3_player','?>\n<object type=\"application/x-shockwave-flash\" data=\"http://flash-mp3-player.net/medias/player_mp3_maxi.swf\" width=\"25\" height=\"20\"><param name=\"movie\" value=\"http://flash-mp3-player.net/medias/player_mp3_maxi.swf\" /><param name=\"FlashVars\" value=\"mp3=<?php echo $value; ?>&width=25&showslider=0\" /></object>');

