<?php
class cfMysqlQuery
{
	private 
		$_dbconn,
		$_query_string = '',
		$_results;

    /**
     * Constructeur
     * @param $query_string
     * @param $args
     * @param $dbconn
     */
    public function __construct($query_string, $args, $dbconn)
	{
		$this->_dbconn = &$dbconn;
		$this->evalQueryString($query_string, $args);
	}

    /**
     * Evalue la requête
     * @param $query_string
     * @param $args
     */
    protected function evalQueryString($query_string, $args)
	{
		if (!is_array($args) && $args !== NULL) {
			$args = array($args);
		}
		
		if ($args !== NULL) {
			$args = array_map('mysql_real_escape_string', $args);
			$this->_query_string .= ' ' . vsprintf($query_string, $args);
		} else {
			$this->_query_string .= ' ' . $query_string;
		}
	}

    /**
     * Ajoute une requête
     * @param $query_string
     * @param null $args
     * @return $this
     */
    public function addQuery($query_string, $args = NULL)
	{
		$this->evalQueryString($query_string, $args);
		return $this;
	}

    /**
     * Exécute la requête
     * @return mixed
     * @throws Exception
     */
    public function execute()
	{
		$this->_query_string = trim($this->_query_string);
        $result_res = mysql_query($this->_query_string);

        if ($result_res !== false) {
            $this->_results = new cfMysqlResult($result_res);
            cfMysqlManager::increaseNbRequest();
            return $this->_results;
        }

		throw new Exception(mysql_error($this->_dbconn) . ' Query : ' . $this->_query_string);
		//return false;
		
	}

    /**
     * Retourne la chaîne de la requête
     * @return string
     */
    public function getSqlQuery()
	{
		return $this->_query_string;
	}
}