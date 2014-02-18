<?php

class cfMysqli extends MySQLi
{
    public function query($query)
    {
        $this->real_query($query);
        return new cfMysqliResult($this);
    }
}