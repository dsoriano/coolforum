<?php
class databaseFactory
{
    public static function connect($driver, array $params)
    {
        switch ($driver) {
            case 'mysqli' :
                include dirname(__FILE__) . '/cfMysqli/cfMysqliManager.php';
                $conn = cfMysqliManager::getInstance($params);
                return $conn;
                break;

            /*case 'mysql' :
                include dirname(__FILE__) . '/cfMysql/cfMysql.php';
                $conn = cfMysql::getInstance($params);
                break;*/
        }
    }
}