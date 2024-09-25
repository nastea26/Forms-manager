CREATE TABLE users(
	id int unsigned AUTO_INCREMENT NOT NULL PRIMARY KEY,
	email varchar(255) NOT NULL UNIQUE,
    pass varchar(255) NOT NULL
);
