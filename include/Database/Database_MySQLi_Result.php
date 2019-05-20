<?php
namespace Database;

class Database_MySQLi_Result extends \MySQLi_Result
{
    public function num_rows()
    {
        return $this->num_rows;
    }
}
