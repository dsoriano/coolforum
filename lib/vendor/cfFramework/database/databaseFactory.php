<?php
class databaseFactory
{
    public static function connect($driver, array $params)
    {
        switch ($driver) {
            case 'mysqli' :
                include dirname(__FILE__) . '/cfMysqli/cfMysqli.php';
                $conn = cfMysqli::getInstance($params);
                break;

            /*case 'mysql' :
                include dirname(__FILE__) . '/cfMysql/cfMysql.php';
                $conn = cfMysql::getInstance($params);
                break;*/
        }
    }
}