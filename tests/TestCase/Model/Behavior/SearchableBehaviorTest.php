<?php

namespace Chris48s\Searchable\Test\TestCase\Model\Behavior;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Chris48s\Searchable\Exception\SearchableException;
use Chris48s\Searchable\Exception\SearchableFatalException;

class SearchableBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Chris48s\Searchable.Foo'
    ];

    public function tearDown(): void
    {
        parent::tearDown();
        $this->getTableLocator()->clear();
    }

    // set up a table to use for testing
    private function getTable()
    {
        $table = $this->getTableLocator()->get('Foo');
        $table->addBehavior('Chris48s/Searchable.Searchable');

        return $table;
    }

    /* pass in column which is not of type string or text
       and ensure SearchableException is thrown */
    public function testNonStringColumn()
    {
        $this->expectException(SearchableException::class);
        $table = $this->getTable();
        $table->find('matches', [
            [
                'match' => 'id',
                'against' => 'foo'
            ]
        ]);
    }

    /* pass in column that doesn't exist
       and ensure SearchableException is thrown */
    public function testInvalidColumn()
    {
        $this->expectException(SearchableException::class);
        $table = $this->getTable();
        $table->find('matches', [
            [
                'match' => 'foo',
                'against' => 'foo'
            ]
        ]);
    }

    /* pass in a mode that is not in the whitelist and ensure
       no mode argument is added to the MATCH() AGAINST() clause */
    public function testInvalidMode()
    {
        $table = $this->getTable();
        $query = $table->find('matches', [
            [
                'match' => 'textcol1',
                'against' => 'foo',
                'mode' => 'foo'
            ]
        ]);
        $expectedSql = "WHERE MATCH(textcol1) AGAINST (:match0 )";
        $this->assertEquals($expectedSql, substr($query->sql(), -40));
    }

    // omit 'match' key and ensure SearchableException is thrown
    public function testMissingMatchKey()
    {
        $this->expectException(SearchableException::class);
        $table = $this->getTable();
        $table->find('matches', [
            [
                'against' => 'foo',
            ]
        ]);
    }

    // omit 'against' key and ensure SearchableException is thrown
    public function testMissingAgainstKey()
    {
        $this->expectException(SearchableException::class);
        $table = $this->getTable();
        $table->find('matches', [
            [
                'match' => 'textcol1'
            ]
        ]);
    }

    /* Only MySQL is supported
       Pass in a connection to another DB engine
       and ensure correct exception is thrown */
    public function testInvalidDB()
    {
        $this->expectException(SearchableFatalException::class);

        //set up a SQLite DB connection - SQLite is not supported
        ConnectionManager::setConfig('invalid', [
            'url' => 'sqlite:///:memory:',
            'timezone' => 'UTC'
        ]);
        $conn = ConnectionManager::get('invalid');

        //create a table in SQLite
        $conn->query("CREATE TABLE `Foo` (
            `id` int(11) NOT NULL,
            `textcol` VARCHAR(255),
            PRIMARY KEY (`id`)
        );");
        $table = $this->getTableLocator()->get('Foo', ['connection' => $conn]);
        $table->addBehavior('Chris48s/Searchable.Searchable');

        //tidy up
        ConnectionManager::dropAlias('invalid');
    }

    // pass some valid options and ensure the expected query is returned
    public function testSimpleValidQuery()
    {
        $table = $this->getTable();
        $query = $table->find('matches', [
            [
                'match' => 'textcol1',
                'against' => 'foo'
            ]
        ]);
        $expectedSql = "WHERE MATCH(textcol1) AGAINST (:match0 )";
        $this->assertEquals($expectedSql, substr($query->sql(), -40));
    }

    // pass some valid options and explicitly specify table name in query
    public function testSimpleValidQueryWithTableName()
    {
        $table = $this->getTable();
        $query = $table->find('matches', [
            [
                'match' => 'Foo.textcol1',
                'against' => 'Now this is a story'
            ]
        ]);
        $this->assertEquals(1, count($query->toArray()));
    }

    /* pass some slightly more diverse valid options
       and ensure the expected query is returned */
    public function testValidQueryMultipleMatchClauses()
    {
        $table = $this->getTable();
        $options = [
            [
                'match' => 'textcol1',
                'against' => 'foo',
                'mode' => 'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION' //valid mode
            ],
            [
                'match' => '    textcol2,  textcol3', //multiple columns and some extraneous whitespace
                'against' => 'foo',
                'mode' => 'IN BOOLEAN MODE' //another valid mode
            ]
        ];
        $query = $table->find('matches', $options);
        $expectedSql = "WHERE (MATCH(textcol1) AGAINST (:match0 {$options[0]['mode']} ) AND " .
                       "MATCH({$options[1]['match']}) AGAINST (:match1 {$options[1]['mode']} ))";
        $this->assertEquals($expectedSql, substr($query->sql(), -158));
    }

    /* pass multiple match clauses with string keys
       and ensure the expected query is returned */
    public function testValidQueryMultipleMatchClausesStringKeys()
    {
        $table = $this->getTable();
        $options = [
            'abc' => [
                'match' => 'textcol1',
                'against' => 'foo',
                'mode' => 'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION' //valid mode
            ],
            'def' => [
                'match' => '    textcol2,  textcol3', //multiple columns and some extraneous whitespace
                'against' => 'foo',
                'mode' => 'IN BOOLEAN MODE' //another valid mode
            ]
        ];
        $query = $table->find('matches', $options);
        $expectedSql = "WHERE (MATCH(textcol1) AGAINST (:match0 {$options['abc']['mode']} ) AND " .
                       "MATCH({$options['def']['match']}) AGAINST (:match1 {$options['def']['mode']} ))";
        $this->assertEquals($expectedSql, substr($query->sql(), -158));
    }
}
