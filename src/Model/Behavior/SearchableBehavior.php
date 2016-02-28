<?php

namespace Chris48s\Searchable\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Chris48s\Searchable\Exception\SearchableException;
use Chris48s\Searchable\Exception\SearchableFatalException;

class SearchableBehavior extends Behavior
{
    //valid match modes
    protected $_modesWhitelist = [
        'IN NATURAL LANGUAGE MODE',
        'IN BOOLEAN MODE',
        'WITH QUERY EXPANSION',
        'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION'
    ];
    //valid columns - this is populated in initialize()
    protected $_columnsWhitelist = [];

    /**
     * Constructor hook method
     *
     * @param array $config Configuration options for the Behavior
     * @throws SearchableFatalException If database engine is not MySQL
     * @return void
     */
    public function initialize(array $config)
    {
        $connection = $this->_table->connection();

        //ensure database engine is MySQL
        if (!$connection->driver() instanceof Mysql) {
            throw new SearchableFatalException('Only MySQL is supported');
        }

        //build a whitelist of string/text columns
        $collection = $connection->schemaCollection();
        $columns = $collection->describe($this->_table->table())->columns();
        foreach ($columns as $column) {
            $columnInfo = $collection->describe($this->_table->table())->column($column);
            if (($columnInfo['type'] == 'string') || ($columnInfo['type'] == 'text')) {
                $this->_columnsWhitelist[] = $column;
            }
        }
    }

    /**
     * Find Matches
     *
     * Assemble a query containing one or more MATCH() AGAINST() clauses
     * @param \Cake\ORM\Query $query Query to modify
     * @param array $options Options for the query
     * @throws SearchableException If keys 'match' or 'against' are not set
     * @throws SearchableException If columns are invalid
     * @return \Cake\ORM\Query
     */
    public function findMatches(Query $query, array $options)
    {
        $conditions = [];

        //assemble MATCH() AGAINST() clauses
        foreach ($options as $key => $option) {

            if (!isset($option['match']) || !isset($option['against'])) {
                throw new SearchableException("Keys 'match' and 'against' must be set");
            }

            if ($this->_validateColumns($option['match'])) {
                $conditions[$key] = "MATCH({$option['match']}) AGAINST (:match{$key} ";
                if (isset($option['mode'])) {
                    if (in_array($option['mode'], $this->_modesWhitelist)) {
                        $conditions[$key] .= $option['mode'] . ' ';
                    }
                }
                $conditions[$key] .= ")";
            }
        }

        $query->find('all', ['conditions' => $conditions]);

        //bind params
        foreach ($options as $key => $option) {
            $query->bind(":match{$key}", $option['against']);
        }

        return $query;
    }

    /**
     * Validate Columns
     *
     * Check columns used in $columnList are all string or text
     * @param string $columnList Comma seperated list of columns
     * @throws SearchableException If columns are invalid
     * @return bool
     */
    private function _validateColumns($columnList)
    {
        $columns = explode(',', $columnList);
        foreach ($columns as $column) {
            if (!in_array(trim($column), $this->_columnsWhitelist)) {
                throw new SearchableException('Invalid column');
            }
        }
        return true;
    }
}
