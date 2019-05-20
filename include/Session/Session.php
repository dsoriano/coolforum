<?php
namespace Session;

use Database\Database_MySQLi;

/**
 * Class Session
 *
 */
class Session
{

    /**
     * @var Database_MySQLi
     */
    private $sql;

    /**
     * @var string
     */
    private $data;

    /**
     * @var array
     */
    private $connectedUsers;

    /**
     * @var bool
     */
    private $gc = false;

    /**
     * Session constructor.
     * @param $sql
     */
    public function __construct($sql)
    {
        $this->sql = $sql;

        session_set_save_handler(
            [$this, "open"],
            [$this, "close"],
            [$this, "read"],
            [$this, "write"],
            [$this, "destroy"],
            [$this, "gc"]
        );

        register_shutdown_function('session_write_close');

        session_start();
    }

    public function open($savePath, $sessionName)
    {
        if ($this->sql) {
            return true;
        }
        return false;
    }

    public function close()
    {
        if ($this->gc) {
            $this->gc(1440);

            $this->sql->query("UPDATE " . _PRE_ . "config SET valeur='%s' WHERE options='%s'", [date('Y-m-d H:i:s', strtotime( 'now + 1 hour')), 'next_session_gc'])->execute();
        }
        return true;
    }

    public function read($id)
    {
        $query = $this->sql->query('SELECT data FROM ' . _PRE_ . 'session WHERE id = "%s"', $id)->execute();

        if ($query->num_rows() > 0) {
            $result = $query->fetch_array();
            return $result['data'];
        }

        return '';
    }

    public function write($id, $data)
    {
        if ($data !== $this->data) {
            $this->data = $data;

            $DateTime = date('Y-m-d H:i:s');
            //$NewDateTime = date('Y-m-d H:i:s', strtotime($DateTime . ' + 1 hour'));

            if ($this->sql->query('REPLACE INTO ' . _PRE_ . 'session (id, username, userid, userstatus, access, typelieu, forumid, topicid, data)  VALUES ("%s", "%s", %d, %d, "%s", "%s", "%s", "%s", "%s")', [
                $id,
                self::get(['user','username'], ''),
                self::get(['user','userid'], 0),
                self::get(['user','userstatus'], 1),
                $DateTime,
                self::get('SessLieu', ''),
                self::get('SessForum', 0),
                self::get('SessTopic', 0),
                $data
            ])->execute()) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function destroy($id)
    {
        if ($this->sql->query('DELETE FROM ' . _PRE_ . 'session WHERE id = "%s"', $id)->execute()) {
            return true;
        }

        return false;
    }

    public function gc($maxlifetime)
    {
        $DateTime = date('Y-m-d H:i:s', strtotime( $maxlifetime . 'seconds ago'));

        if ($this->sql->query('DELETE FROM ' . _PRE_ . 'session WHERE access < "%s"', $DateTime)->execute()) {
            return true;
        }

        return false;
    }

    public function checkConnected()
    {
        $DateTime = date('Y-m-d H:i:s', strtotime( '5 minutes ago'));

        $this->connectedUsers = [];
        $query = $this->sql->query("SELECT id, username, userid, userstatus, typelieu, forumid, topicid FROM " . _PRE_ . "session 
            WHERE id <> '%s'
            AND access > '%s'", [session_id(), $DateTime])->execute();

        $this->connectedUsers[] = [
            'name' => $_SESSION['user']['username'],
            'status' => $_SESSION['user']['userstatus'],
            'userid' => $_SESSION['user']['userid'],
            'typelieu' => $_SESSION['SessLieu'],
            'forumid' => $_SESSION['SessForum'],
            'topicid' => $_SESSION['SessTopic'],
        ];

        if ($query->num_rows > 0) {
            while ($user = $query->fetch_array()) {
                $this->connectedUsers[] = [
                    'name' => $user['username'],
                    'status' => (int)$user['userstatus'],
                    'userid' => (int)$user['userid'],
                    'typelieu' => $user['typelieu'],
                    'forumid' => (int)$user['forumid'],
                    'topicid' => (int)$user['topicid'],
                ];
            }
        }

        return $this->connectedUsers;
    }

    /**
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {

        if (is_array($key)) {
            return count($key) === 2 && isset($_SESSION[$key[0]]) && array_key_exists($key[1], $_SESSION[$key[0]]) ? $_SESSION[$key[0]][$key[1]] : $default;
        }

        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    /**
     * @param $key
     * @param mixed $value
     */
    public static function set($key, $value = null) {
        if (is_array($key)) {
            if ($value === null) {
                $_SESSION = array_merge($_SESSION, $key);
            } elseif (count($key) === 2) {
                $_SESSION[$key[0]][$key[1]] = $value;
            }
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * @param $key
     */
    public static function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * @param string $date
     */
    public function setGc($date)
    {
        if (strtotime($date) <= time()) {
            $this->gc = true;
        }
    }
}

