
-- Acme Project Database Schema
-- @file database.sql
-- @author Emanuel Fiuza de Oliveira
-- @email efiuza@me.com
-- @date Wed, 6 Feb 2013 16:27 -0300
-- @syntax MySQL


CREATE TABLE users (
	id       INTEGER NOT NULL AUTO_INCREMENT,
	username VARCHAR (20) NOT NULL,
	password VARCHAR (32) NOT NULL, -- user's md5 password
	email    VARCHAR (50) NOT NULL,
	status   SMALLINT NOT NULL,
	created  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	CONSTRAINT users_username_uk
		UNIQUE (username)
);


CREATE TABLE sessions (
	id      INTEGER NOT NULL AUTO_INCREMENT,
	hash    VARCHAR (64) NOT NULL, -- it is like a session passowrd
	user_id INTEGER NOT NULL,
	created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	expires TIMESTAMP NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT sessions_user_id_fk
		FOREIGN KEY (user_id)
			REFERENCES users (id)
);


CREATE TABLE products (
	id          INTEGER NOT NULL AUTO_INCREMENT,
	title       VARCHAR (150) NOT NULL,
	barcode     VARCHAR (50) NOT NULL,
	model       VARCHAR (50),
	description VARCHAR (250),
	status      SMALLINT,
	created     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	deleted     INTEGER NOT NULL,
	PRIMARY KEY (id)
	-- @todo add fields to identify the user who handled the record
);

-- indexes for faster searches on products table

CREATE INDEX products_title_idx ON products (title ASC);
CREATE INDEX products_barcode_idx ON products (barcode ASC);


CREATE TABLE inventory (
	id         INTEGER NOT NULL AUTO_INCREMENT,
	product_id INTEGER NOT NULL,
	document   VARCHAR (50) NOT NULL,
	amount     NUMERIC (11,2) NOT NULL,
	created    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	CONSTRAINT inventory_product_id_fk
		FOREIGN KEY (product_id)
			REFERENCES products (id)
	-- @todo add fields to identify the user who handled the record
);


-- this table tracks withdraw operations from the inventory

CREATE TABLE inventory_withdraws (
	id         INTEGER NOT NULL AUTO_INCREMENT,
	product_id INTEGER NOT NULL, -- reference to the product
	document   VARCHAR (50) NOT NULL,
	amount     NUMERIC (11,2) NOT NULL,
	created    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	CONSTRAINT inventory_withdraws_product_id_fk
		FOREIGN KEY (product_id)
			REFERENCES products (id)
	-- @todo add fields to identify the user who handled the record
);


-- insert fisrt user

INSERT INTO users (username, password, email, status)
	VALUES ('admin', MD5('123456'), 'admin@localhost', 0);

