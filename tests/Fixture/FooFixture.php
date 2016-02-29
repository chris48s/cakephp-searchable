<?php
namespace Chris48s\Searchable\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class FooFixture extends TestFixture
{

    public $table = 'foo';

    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'textcol1' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'textcol2' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'textcol3' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        '_indexes' => [
            'ft1' => ['type' => 'fulltext', 'columns' => ['textcol1'], 'length' => []],
            'ft2' => ['type' => 'fulltext', 'columns' => ['textcol2', 'textcol3'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    public $records = [
        [
            'id' => 1,
            'textcol1' => "Now this is a story all about how",
            'textcol2' => "My life got flipped-turned upside down",
            'textcol3' => "And I'd like to take a minute just sit right there"
        ],
        [
            'id' => 2,
            'textcol1' => "I'll tell you how I became the prince of a town called Bel-Air",
            'textcol2' => "In west Philadelphia born and raised",
            'textcol3' => "On the playground was where I spent most of my days"
        ],
        [
            'id' => 3,
            'textcol1' => "Chillin' out max and relaxin' all cool",
            'textcol2' => "And I was shooting some b-ball outside of the school",
            'textcol3' => "When a couple of guys who were up to no good"
        ],
        [
            'id' => 4,
            'textcol1' => "Started making trouble in my neighborhood",
            'textcol2' => "I got in one little fight and my mom got scared",
            'textcol3' => "She said 'You're movin' with your auntie and uncle in Bel-Air'"
        ]
    ];
}
