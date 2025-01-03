CREATE TABLE `Account` (
  `id` integer(8) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `locked` boolean NOT NULL,
  `enabled` boolean NOT NULL,
  `email` varchar(255),
  `phonenumber` varchar(255),
  `created` timestamp
);

CREATE TABLE `UserRoles` (
  `id` integer(8) PRIMARY KEY NOT NULL,
  `user_id` integer(8) NOT NULL,
  `role` varchar(255) NOT NULL
);

CREATE TABLE `Customer_addresses` (
  `customer_user_id` integer(8) NOT NULL,
  `city` varchar(255),
  `street` varchar(255),
  `housenumber` varchar(255),
  `apartmentnumber` varchar(255)
);

CREATE TABLE `Orders` (
  `id` integer(8) PRIMARY KEY NOT NULL,
  `comment` varchar(1000),
  `paymenttransactionid` varchar(255),
  `customer_id` integer(8) NOT NULL
);

CREATE TABLE `Delivery` (
  `id` integer(8) PRIMARY KEY NOT NULL,
  `city` varchar(255),
  `street` varchar(255),
  `housenumber` varchar(255),
  `apartmentnumber` varchar(255),
  `status` varchar(255),
  `deliveryperson_user_id` integer(8),
  `order_id` integer(8)
);

CREATE TABLE `Review` (
  `id` integer(8) PRIMARY KEY NOT NULL,
  `created` timestamp,
  `message` varchar(1000),
  `rated` integer(4) NOT NULL,
  `order_id` integer(8) NOT NULL
);

CREATE TABLE `Orderitem` (
  `id` integer(8) PRIMARY KEY NOT NULL,
  `quantity` integer(4) NOT NULL,
  `order_id` integer(8),
  `pizza_id` integer(8)
);

CREATE TABLE `Pizza` (
  `id` integer(8) PRIMARY KEY NOT NULL,
  `crust` varchar(50),
  `cutstyle` varchar(50),
  `pizzasize` varchar(50),
  `ingredient` varchar(1000)
);

CREATE TABLE `Coupon` (
  `id` integer(8) PRIMARY KEY NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` varchar(50) NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `expiration_date` date NOT NULL,
  `is_active` boolean NOT NULL
);

CREATE TABLE `Customer_coupon` (
  `customer_user_id` integer(8) NOT NULL,
  `coupon_id` integer(8) NOT NULL,
  `used_date` timestamp
);

ALTER TABLE `Customer_addresses` ADD FOREIGN KEY (`customer_user_id`) REFERENCES `UserRoles` (`user_id`);

ALTER TABLE `Orders` ADD FOREIGN KEY (`customer_id`) REFERENCES `UserRoles` (`user_id`);

ALTER TABLE `UserRoles` ADD FOREIGN KEY (`id`) REFERENCES `Account` (`id`);

ALTER TABLE `Delivery` ADD FOREIGN KEY (`deliveryperson_user_id`) REFERENCES `UserRoles` (`user_id`);

ALTER TABLE `Delivery` ADD FOREIGN KEY (`order_id`) REFERENCES `Orders` (`id`);

ALTER TABLE `Review` ADD FOREIGN KEY (`order_id`) REFERENCES `Orders` (`id`);

ALTER TABLE `Orderitem` ADD FOREIGN KEY (`order_id`) REFERENCES `Orders` (`id`);

ALTER TABLE `Orderitem` ADD FOREIGN KEY (`pizza_id`) REFERENCES `Pizza` (`id`);

ALTER TABLE `Customer_coupon` ADD FOREIGN KEY (`customer_user_id`) REFERENCES `UserRoles` (`user_id`);

ALTER TABLE `Coupon` ADD FOREIGN KEY (`id`) REFERENCES `Customer_coupon` (`coupon_id`);
