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

