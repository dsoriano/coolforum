<?php

class cfMysqlResult
{
    protected
        $_results = null,
        $_is_writing = false,
        $_db_conn = null;

    public function __construct($res)
    {
        $this->_results = $res;

        if (is_bool($this->_results)) {
            $this->_is_writing = true;
        }

    }

    public function num_rows()
    {
        return mysql_num_rows($this->_results);
    }

    public function fetch_row()
    {
        return mysql_fetch_row($this->_results);
    }

    public function fetch_array()
    {
        return mysql_fetch_array($this->_results);
    }

    /**
     * Retourne le nombre de lignes affectées par une mise à jour
     * @return int
     */
    public function affected_rows()
    {
        return mysql_affected_rows();
    }

}