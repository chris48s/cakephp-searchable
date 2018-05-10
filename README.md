[![Build Status](https://travis-ci.org/chris48s/cakephp-searchable.svg?branch=master)](https://travis-ci.org/chris48s/cakephp-searchable)
[![Coverage Status](https://coveralls.io/repos/github/chris48s/cakephp-searchable/badge.svg?branch=master)](https://coveralls.io/github/chris48s/cakephp-searchable?branch=master)

# CakePHP Searchable Behavior Plugin

A CakePHP 3 Behavior for creating MySQL [MATCH() AGAINST() queries](https://dev.mysql.com/doc/refman/5.6/en/fulltext-search.html).

CakePHP-Searchable adds a custom `find('matches')` method to your CakePHP models, alleviating the need to pass raw SQL into your queries. It is safe against SQL injection and uses a query syntax which is consistent with the conventions of CakePHP's ORM.

## Installation

Install from [packagist](https://packagist.org/packages/chris48s/cakephp-searchable) using [composer](https://getcomposer.org/).
Add the following to your `composer.json`:

```
"require": {
    "chris48s/cakephp-searchable": "^2.0.0"
}
```

and run `composer install` or `composer update`, as applicable.

## Database Support

In order to use FULLTEXT indexes on InnoDB tables, you must be using MySQL >=5.6.4. Earlier versions only allow use of FULLTEXT indexes on MyISAM tables.

## Usage

### Loading the plugin

Add the code `Plugin::load('Chris48s/Searchable');` to your `bootstrap.php`.

### Using the Behavior

Add the behavior in your table class.

```php
<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class MyTable extends Table
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->addBehavior('Chris48s/Searchable.Searchable');
    }
}
```
### Querying data

Having added the behavior to a table class, you now have access to the query method `find('matches')`, which you can use to construct MATCH() AGAINST() queries. For example:

```php
<?php

use Cake\ORM\TableRegistry;

$myTable = TableRegistry::get('MyTable');

$query = $myTable
    ->find('matches', [
        [
            'match' => 'textcol1',
            'against' => 'foo'
        ],
        [
            'match' => 'textcol2, textcol3',
            'against' => '+foo bar*',
            'mode' => 'IN BOOLEAN MODE'
        ]
    ]);
```

Available modes are:
* `'IN NATURAL LANGUAGE MODE'`
* `'IN BOOLEAN MODE'`
* `'WITH QUERY EXPANSION'`
* `'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION'`

When using boolean mode, some [additional operators](https://dev.mysql.com/doc/refman/5.6/en/fulltext-boolean.html) are available.

The method `find('matches')` returns a CakePHP query object, so you can chain
additional methods on to this (e.g: `->where()`, `->order()`, `->limit()`, etc).

## Error Handling
If the keys `'match'` or `'against'` are not set, or if any of the columns contained in the column list are not of type `string` or `text`, an exception of class `SearchableException` will be thrown.

## Reporting Issues
If you have any issues with this plugin then please feel free to create a new [Issue](https://github.com/chris48s/cakephp-searchable/issues) on the [GitHub repository](https://github.com/chris48s/cakephp-searchable). This plugin is licensed under the MIT Licence.
