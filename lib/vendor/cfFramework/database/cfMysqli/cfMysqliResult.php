<?php

class cfMysqliResult extends MySQLi_Result
{
    public function num_rows()
    {
        return $this->num_rows;
    }

}