<?php

namespace MPN\DB;

use Exception;

/**
 * @Description: construct SQL query string.
 * @DP: Singleton
 *
 * @author Martin Nikolov
 */
class QueryBilder {

    protected $query = '';

    public function __construct() {
        
    }

    /**
     * clear query string.
     * every time, when string is complete, go() will call
     * unsetThisQuery().
     */
    public function unsetThisQuery() {
        $this->query = '';
    }

    /**
     * "SELECT first parameter/s FROM second parameter/s"
     * 
     * @param array $columnsNames
     * @param array $tablesNames
     * 
     * @return $this
     * 
     * @throws Exception
     */
    public function s($columnsNames, $tablesNames, $distinct = false) {
        $this->unsetThisQuery();
        if ($distinct) {
            $select = 'SELECT!DISTINCT!';
        } else {
            $select = 'SELECT!';
        }

        $string = '`';
        $glue = '';
        $start = 0;
        if (\is_array($columnsNames)) {
            foreach ($columnsNames as $column) {
                if ($start > 0) {
                    $glue = ',`';
                }
                $string .= $glue . $column . '`';
                $start++;
            }
            $this->query .= $select . $string . '!FROM!';
            $string = '`';
        } else {
            if ($columnsNames == '*') {
                $this->query .= $select . $columnsNames . '!FROM!';
            } else {
                $this->query .= $select . $string . $columnsNames . $string . '!FROM!';
                $string = '`';
            }
        }
        if (\is_array($tablesNames)) {
            $start = 0;
            foreach ($tablesNames as $tableName) {
                if ($start > 0) {
                    $glue = ',`';
                }
                $string .= $glue . $tableName . '`';
                $start++;
            }
            $this->query .= $string;
        } else {
            $this->query .= $string . $tablesNames . $string;
        }
        return $this;
    }

    /**
     * "WHERE `user_id{first parameter/s}`  ={second parameter/s} ?"
     * "WHERE `user_id{first parameter/s}`  >{second parameter/s} ? AND{third parameter/s} `user_age{first parameter/s}`  <{second parameter/s} ?"
     * 
     * @param type $columns
     * @param type $operator
     * @param  "&&" and/or "||" $logic
     * @return $this
     */
    public function w($columns, $operator, $logic = null) {
        $operations = [];
        if (\is_array($operator)) {
            foreach ($operator as $operation) {
                $operations[] = $operation;
            }
        } else {
            $operations[0] = $operator;
        }
        if ($logic == '||') {
            $logic = '!OR!';
        } elseif ($logic == '&&') {
            $logic = '!AND!';
        } elseif ($logic == '!') {
            $logic = '!NOT!';
        }
        $string = '`';
        $glue = '';
        $start = 0;
        if (\is_array($columns)) {
            foreach ($columns as $column) {
                if ($start > 0) {
                    $glue = $logic . '`';
                }
                $string .= $glue . $column . '`' . $operations[$start] . '?';
                $start++;
            }
            $this->query .= '!WHERE!' . $string;
        } else {
            $this->query .= '!WHERE!`' . $columns . '`' . $operations[$start] . '?';
        }

        return $this;
    }

    public function ob($column) {
        $this->query .= '!ORDER!BY!' . '`' . $column . '`';
        return $this;
    }

    public function l($param) {
        if ($param === '?') {
            $this->query .= '!LIMIT!?';
        } else {
            $this->query .= '!LIMIT!?,?';
        }
        return $this;
    }

    public function u($table, $columns) {
        $this->unsetThisQuery();
        $this->query .= 'UPDATE!`' . $table . '`!SET!';
        if (\is_array($columns)) {
            $newColumns = '`';
            $glue = '';
            $start = 0;
            foreach ($columns as $column) {
                if ($start > 0) {
                    $glue = ',`';
                }
                $newColumns .= $glue . $column . '`=?';
                $start++;
            }
            $this->query .= $newColumns;
        } else {
            $this->query .= '`' . $columns . '`=?';
        }
        return $this;
    }

    public function i($table, $values) {
        $this->unsetThisQuery();
        $params = '?';
        $columnsNames = '`';
        $glue = '';
        $start = 0;
        $this->query .= 'INSERT!INTO!`' . $table . '`!(';
        foreach ($values as $columnName) {
            if ($start > 0) {
                $glue = ',`';
                $params .= ',?';
            }
            $columnsNames .= $glue . $columnName . '`';
            $start++;
        }
        $this->query .= $columnsNames . ') VALUES (' . $params . ')';
        return $this;
    }

    public function delete($tables) {
        $this->unsetThisQuery();
        $this->query .= '!DELETE!FROM!`' . $tables . '`';
        return $this;
    }

    public function go() {
        $this->query = str_replace('!', ' ', $this->query);
        return $this->query;
    }

}
