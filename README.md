# silverstripe-fulltext-innodb

A small SilverStripe module that enables usage of fulltext indexes with the InnoDB engine.

As of MySQL 5.6 InnoDB now supports fulltext indexes, so this just injects a replacement `MySQLDBSchemaManager` that removes the hard-coded/forced selection of engine=MyISAM for tables that have a fulltext index defined.

## Requirements

* [silverstripe-framework](https://github.com/silverstripe/silverstripe-framework) ^4

## Recommended

* [fromholdio/silverstripe-fulltext-filters](https://github.com/fromholdio/silverstripe-fulltext-filters) ^1.0

## Installation

`composer require fromholdio/silverstripe-fulltext-innodb`
