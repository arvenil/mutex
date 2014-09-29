<?php
/**
 * This file is part of ninja-mutex.
 *
 * (C) Kamil Dziedzic <arvenil@klecza.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NinjaMutex\Mock;

use PDO;
use PDOStatement;

/**
 * Mock PDOStatement to use with MockPDO
 *
 * @author Kamil Dziedzic <arvenil@klecza.pl>
 */
class MockPDOStatement extends PDOStatement
{
    /**
     * @var string
     */
    protected $_mock_fetch = '';

    /**
     * @param  string           $result
     * @return MockPDOStatement
     */
    public function _mock_set_fetch($result)
    {
        $this->_mock_fetch = $result;

        return $this;
    }

    /**
     * @param  int|null $fetch_style
     * @param  int|null $cursor_orientation
     * @param  int|null $cursor_offset
     * @return string
     */
    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        return $this->_mock_fetch;
    }
}
