# FixMailAdmin

A simple web interface for allowing the maintenance of email virtual domains in postfix, which are stored in a MySQL database. It's a Laravel5-based application with a Bootstrap styling with event handling by jQuery.
The MySQL schema follows the web article available [here](https://www.howtoforge.com/virtual-users-and-domains-with-postfix-courier-mysql-and-squirrelmail-debian-wheezy#-create-the-mysql-database-for-postfixcourier) entitled **Virtual Users And Domains With Postfix, Courier, MySQL And SquirrelMail (Debian Wheezy)** .

Updated to allow the use of [`sqlite`](http://sqlite.org/index.html) as the storage engine.

The schema of the database followed is:

```sql

CREATE TABLE IF NOT EXISTS `domains` (
  `domain` varchar(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS `forwardings` (
  `source` varchar(80) NOT NULL,
  `destination` text NOT NULL
);

CREATE TABLE IF NOT EXISTS `transport` (
  `domain` varchar(128) NOT NULL DEFAULT '',
  `transport` varchar(128) NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS `users` (
  `email` varchar(80) NOT NULL,
  `password` varchar(20) NOT NULL,
  `quota` int(10) DEFAULT '10485760'
);

```


