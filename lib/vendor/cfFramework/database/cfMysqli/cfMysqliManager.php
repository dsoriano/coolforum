<?php

require_once dirname(__FILE__) . '/cfMysqli.php';
require_once dirname(__FILE__) . '/cfMysqliRequest.php';
require_once dirname(__FILE__) . '/cfMysqliResult.php';

class cfMysqliManager
{
    protected static
        $_instance = NULL,
        $_nb_request = 0;
    private
        $_dbconn;

    /**
     * Constructeur
     * @param array $params tableau de paramètres
     */
    public function __construct($params)
    {
        $this->_dbconn = new cfMysqli($params['hostname'],
            $params['username'],
            $params['password'],
            $params['database']);
        //return $this->_dbconn;
    }

    /**
     * Récupère l'instance de la base de donnée (Singleton)
     * @param array $params tableau de paramètres
     * @return cfMysqli|null
     */
    public static function getInstance($params)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($params);
        }

        return self::$_instance;
    }

    /**
     * Lance une requête
     * @param $query_string
     * @param null $args
     * @return bool|fwMysqli_Request
     */
    public function query($query_string, $args = null)
    {
        if ($args === null || is_string($args) || is_numeric($args) || is_array($args)) {
            $request = new cfMysqliRequest($query_string, $args, $this->_dbconn);
        } else {
            $request = false;
        }

        return $request;
    }
/*
    public function pager()
    {

    }
*/
    /**
     * Compteur de requêtes
     * @param int $nb
     */
    public static function increaseNbRequest($nb = 1)
    {
        self::$_nb_request += $nb;
    }

    /**
     * Retourne le nombre de requêtes
     * @return int
     */
    public static function getNbRequests()
    {
        return self::$_nb_request;
    }

    /**
     * Retourne le nombre de lignes affectées
     * @return int
     */
    public function affectedRows()
    {
        return $this->_dbconn->affected_rows;
    }

    /**
     * Retourne l'ID de la dernière insertion
     * @return mixed
     */
    public function insertId()
    {
        return $this->_dbconn->insert_id;
    }
}