CREATE TABLE shopaholic_pictures (
    id SERIAL PRIMARY KEY NOT NULL,
    file VARCHAR(128) NOT NULL UNIQUE,
    description VARCHAR(512) DEFAULT NULL,
    thumbnail_id BIGINT DEFAULT NULL UNIQUE,
    INDEX (thumbnail_id)
) ENGINE=InnoDB;

CREATE TABLE shopaholic_pages (
    nice_name VARCHAR(256) NOT NULL PRIMARY KEY,
    name VARCHAR(256) NOT NULL,
    content TEXT,
    meta_keywords TEXT,
    meta_description TEXT,
    picture_id BIGINT,
    ref_id BIGINT,
    ref_type CHAR(16), -- P (product), C (category), M (manufacturer), A (actuality)
    INDEX USING HASH (nice_name),
    INDEX (picture_id),
    INDEX (ref_id, ref_type)
) ENGINE=InnoDB;

CREATE TABLE shopaholic_actualities (
    id SERIAL NOT NULL PRIMARY KEY,
    added_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE shopaholic_product_availabilities (
    id SERIAL NOT NULL PRIMARY KEY,
    name VARCHAR(128) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE shopaholic_products (
    id SERIAL NOT NULL PRIMARY KEY,
    price INTEGER NOT NULL,
    manufacturer_id BIGINT DEFAULT NULL,
    category_id BIGINT DEFAULT NULL,
    availability_id BIGINT DEFAULT NULL,
    code CHAR(32) DEFAULT NULL UNIQUE,
    INDEX (manufacturer_id),
    INDEX (category_id),
    INDEX (availability_id),
    INDEX (code)
) ENGINE=InnoDB;

CREATE TABLE shopaholic_categories (
    id SERIAL NOT NULL PRIMARY KEY,
    lft BIGINT NOT NULL,
    rgt BIGINT NOT NULL,
    INDEX (lft)
) ENGINE=InnoDB;

CREATE TABLE shopaholic_manufacturers (
    id SERIAL NOT NULL PRIMARY KEY
) ENGINE=InnoDB;

CREATE TABLE shopaholic_price_changes (
    product_id BIGINT NOT NULL,
    price INTEGER NOT NULL,
    changed_at DATETIME NOT NULL,
    PRIMARY KEY (product_id, changed_at)
) ENGINE=InnoDB;

CREATE TABLE shopaholic_orders (
    id SERIAL NOT NULL PRIMARY KEY,
    delivery_type_id BIGINT NOT NULL,
    payment_type_id BIGINT NOT NULL,
    status_id BIGINT NOT NULL,
    payer_name VARCHAR(128) NOT NULL,
    payer_lastname VARCHAR(128) NOT NULL,
    payer_company VARCHAR(128) NOT NULL,
    payer_street VARCHAR(128) NOT NULL,
    payer_city VARCHAR(128) NOT NULL,
    payer_postcode VARCHAR(128) NOT NULL,
    delivery_name VARCHAR(128),
    delivery_lastname VARCHAR(128),
    delivery_company VARCHAR(128),
    delivery_street VARCHAR(128),
    delivery_city VARCHAR(128),
    delivery_postcode VARCHAR(128),
    email VARCHAR(128) NOT NULL,
    phone VARCHAR(128) NOT NULL,
    comment TEXT,
    at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE shopaholic_orders_products (
    order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    price INTEGER NOT NULL,
    amount INTEGER NOT NULL,
    PRIMARY KEY (order_id, product_id)
) ENGINE=InnoDB;

CREATE TABLE shopaholic_order_payment_types (
    id SERIAL NOT NULL PRIMARY KEY,
    name VARCHAR(256) NOT NULL,
    price INTEGER NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE shopaholic_order_delivery_types (
    id SERIAL NOT NULL PRIMARY KEY,
    name VARCHAR(256) NOT NULL,
    price INTEGER NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE shopaholic_order_statuses (
    id SERIAL NOT NULL PRIMARY KEY,
    name VARCHAR(256) NOT NULL,
    initial BOOLEAN NOT NULL DEFAULT FALSE,
    INDEX (initial)
) ENGINE=InnoDB;

CREATE TABLE shopaholic_order_emails (
    order_id BIGINT NOT NULL,
    sent_at DATETIME NOT NULL,
    subject VARCHAR(512) NOT NULL,
    body TEXT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE shopaholic_order_visited_products (
    order_id BIGINT NOT NULL,
    visited_at DATETIME NOT NULL,
    product_id BIGINT NOT NULL
) ENGINE=InnoDB;

/* FOR FUTURE CASES :-)
CREATE TABLE shopaholic_product_parameters (
    id SERIAL NOT NULL PRIMARY KEY,
    name VARCHAR(256) NOT NULL,
    title VARCHAR(512) NOT NULL,
    format VARCHAR(64) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE shopaholic_products_parameters (
    product_id BIGINT NOT NULL,
    parameter_id BIGINT NOT NULL,
    value VARCHAR(256) NOT NULL,
    PRIMARY KEY (product_id, parameter_id)
) ENGINE=InnoDB;

CREATE TABLE shopaholic_orders_parameters (
    order_id BIGINT NOT NULL,
    name CHAR(64) NOT NULL,
    value VARCHAR(256) NOT NULL,
    PRIMARY KEY (order_id, name)
);

CREATE TABLE shopaholic_orders_products (
    order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    price INTEGER NOT NULL,
    amount INTEGER NOT NULL,
    PRIMARY KEY (order_id, product_id)
);
*/
