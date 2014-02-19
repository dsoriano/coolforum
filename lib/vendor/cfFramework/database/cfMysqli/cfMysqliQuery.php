<?php
class cfMysqliQuery
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
			$args = array_map(array($this->_dbconn, 'real_escape_string'), $args);
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
		if ($this->_results = $this->_dbconn->query($this->_query_string)) {
		    cfMysqliManager::increaseNbRequest();
		    return $this->_results;
		}
		throw new Exception($this->_dbconn->error . ' Request : ' . $this->_query_string);
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