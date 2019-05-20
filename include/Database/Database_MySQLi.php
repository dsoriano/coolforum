<?php
namespace Database;

class Database_MySQLi extends \MySQLi
{
	protected static $_instance = null;

	protected  static $_nb_request = 0;

	private $queryString;

	/**
	 * R�cup�re l'instance de la base de donn�e (Singleton)
	 *
	 * @param array $params tableau de param�tres
	 *
	 * @return Database_MySQLi|null
	 */
	public static function getInstance($params)
	{
		if (self::$_instance === null) {
			self::$_instance = new self($params['hostname'],
				$params['username'],
				$params['password'],
				$params['database']);

			if (self::$_instance->connect_error) {
				die('Erreur de connexion (' . self::$_instance->connect_errno . ') ' . self::$_instance->connect_error);
			}
		}

		return self::$_instance;
	}

	/**
	 * Lance une requ�te
	 *
	 * @param string $query_string
	 * @param null $args
	 *
	 * @return Database_MySQLi
	 */
	public function query($query_string, $args = null)
	{
		$this->queryString = '';

		if ($args === null || is_string($args) || is_numeric($args) || is_array($args)) {
			$this->evalQueryString($query_string, $args);
		}

		return $this;
	}

	/**
	 * Ajoute une requ�te
	 *
	 * @param $query_string
	 * @param null $args
	 * @return $this
	 */
	public function addQuery($query_string, $args = null)
	{
		$this->evalQueryString($query_string, $args);

		return $this;
	}

	/**
	 * Ex�cute la requ�te
	 * @return Database_MySQLi_Result|boolean
	 * @throws \Exception
	 */
	public function execute()
	{
		$this->queryString = trim($this->queryString);

		$this->real_query($this->queryString);
		$result = new Database_MySQLi_Result($this);

		if ($result !== false) {
			self::increaseNbRequest();
		}

		return $result;
	}

	/**
	 * Retourne la cha�ne de la requ�te
	 * @return string
	 */
	public function getSqlQuery()
	{
		return $this->queryString;
	}

	/**
	 * Evalue la requ�te
	 *
	 * @param string $queryString
	 * @param mixed $args - Can be string, numeric type or array
	 */
	protected function evalQueryString($queryString, $args)
	{
		if (!is_array($args) && $args !== null) {
			$args = [$args];
		}

		if ($args !== null) {
			$args = array_map([$this, 'real_escape_string'], $args);
			$this->queryString .= ' ' . vsprintf($queryString, $args);
		} else {
			$this->queryString .= ' ' . $queryString;
		}
	}

	/**
	 * Compteur de requ�tes
	 * @param int $nb
	 */
	public static function increaseNbRequest($nb = 1)
	{
		self::$_nb_request += $nb;
	}

	/**
	 * Retourne le nombre de requ�tes
	 * @return int
	 */
	public static function getNbRequests()
	{
		return self::$_nb_request;
	}

	/**
	 * Retourne le nombre de lignes affect�es
	 * @return int
	 */
	public function affectedRows()
	{
		return $this->affected_rows;
	}

	/**
	 * Retourne l'ID de la derni�re insertion
	 * @return mixed
	 */
	public function insertId()
	{
		return $this->insert_id;
	}

	/**
	 * Retourne le dernier code d'erreur produit
	 * @return int
	 */
	public function errno()
	{
		return $this->errno;
	}

	/**
	 * Retourne une cha�ne d�crivant la derni�re erreur
	 * @return string
	 */
	public function error()
	{
		return $this->error;
	}
}
