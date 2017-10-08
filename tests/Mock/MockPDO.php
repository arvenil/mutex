<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Tests\Mock;

use PDO;
use PDOStatement;

/**
 * Mock PDO to mimic *_lock functionality
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MockPDO extends PDO
{
    /**
     * @var string[]
     */
    protected static $data = array();

    /**
     * @var MockPDOStatement
     */
    protected $_mock_pdo_statement;

    /**
     * @var string[]
     */
    protected $current = array();

    /**
     * Creates a PDO instance representing a connection to a database
     * @link http://php.net/manual/en/pdo.construct.php
     * @param $dsn
     * @param $username [optional]
     * @param $passwd   [optional]
     * @param $options  [optional]
     */
    public function __construct($dsn, $username, $passwd, $options)
    {
        $this->_mock_pdo_statement = new MockPDOStatement();
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     * @link http://php.net/manual/en/pdo.query.php
     * @param string $statement <p>
     * The SQL statement to prepare and execute.
     * </p>
     * <p>
     * Data inside the query should be properly escaped.
     * </p>
     * @param int $mode <p>
     * The fetch mode must be one of the PDO::FETCH_* constants.
     * </p>
     * @param mixed $arg3 <p>
     * The second and following parameters are the same as the parameters for PDOStatement::setFetchMode.
     * </p>
     * @param array $ctorargs [optional] <p>
     * Arguments of custom class constructor when the <i>mode</i>
     * parameter is set to <b>PDO::FETCH_CLASS</b>.
     * </p>
     * @return PDOStatement <b>PDO::query</b> returns a PDOStatement object, or <b>FALSE</b>
     * on failure.
     */
    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = array())
    {
        if (preg_match('/RELEASE_LOCK\((.*)\)/', $statement, $m)) {
            return $this->_mock_release_lock($m[1]);
        } elseif (preg_match('/GET_LOCK\((.*), *(.*)\)/', $statement, $m)) {
            return $this->_mock_get_lock($m[1], $m[2]);
        } elseif (preg_match('/IS_FREE_LOCK\((.*)\)/', $statement, $m)) {
            return $this->_mock_is_free_lock($m[1]);
        }
    }

    /**
     * Quotes a string for use in a query.
     * @link http://php.net/manual/en/pdo.quote.php
     * @param string $string <p>
     * The string to be quoted.
     * </p>
     * @param int $parameter_type [optional] <p>
     * Provides a data type hint for drivers that have alternate quoting styles.
     * </p>
     * @return string a quoted string that is theoretically safe to pass into an
     * SQL statement. Returns <b>FALSE</b> if the driver does not support quoting in
     * this way.
     */
    public function quote($string, $parameter_type = PDO::PARAM_STR)
    {
        return $string;
    }

    /**
     * @param  string           $key
     * @param  int              $timeout
     * @return MockPDOStatement
     */
    protected function _mock_get_lock($key, $timeout)
    {
        // http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_get-lock
        //
        // "If you have a lock obtained with GET_LOCK(),
        // it is released when you (...) execute a new GET_LOCK()"
        //
        // SELECT IS_FREE_LOCK( 'a' ) , GET_LOCK( 'a', 0 ) , IS_FREE_LOCK( 'a' ) , GET_LOCK( 'a', 0 )
        // IS_FREE_LOCK('a') GET_LOCK('a', 0) IS_FREE_LOCK('a') GET_LOCK('a', 0)
        // 1                 1                0                 1
        if ($this->_mock_is_free_lock($key)->fetch() || isset($this->current[$key])) {
            // This part is made to reflect behaviour that second GET_LOCK() releases all current locks
            foreach ($this->current as $k => $v) {
                unset(self::$data[$k]);
                unset($this->current[$k]);
            }

            self::$data[$key] = true;
            $this->current[$key] = true;

            return $this->_mock_pdo_statement->_mock_set_fetch("1");
        }

        // We use sleep because GET_LOCK(str,timeout) accept timeout in seconds
        sleep($timeout);

        return $this->_mock_pdo_statement->_mock_set_fetch("0");
    }

    /**
     * @param  string           $key
     * @return MockPDOStatement
     */
    protected function _mock_is_free_lock($key)
    {
        if (isset(self::$data[$key])) {
            return $this->_mock_pdo_statement->_mock_set_fetch("0");
        }

        return $this->_mock_pdo_statement->_mock_set_fetch("1");
    }

    /**
     * @param  string           $key
     * @return MockPDOStatement
     */
    protected function _mock_release_lock($key)
    {
        if (isset($this->current[$key])) {
            unset(self::$data[$key]);
            unset($this->current[$key]);

            return $this->_mock_pdo_statement->_mock_set_fetch("1");
        }

        return $this->_mock_pdo_statement->_mock_set_fetch("0");
    }

    public function __destruct()
    {
        foreach ($this->current as $k => $v) {
            unset(self::$data[$k]);
            unset($this->current[$k]);
        }
    }
}
