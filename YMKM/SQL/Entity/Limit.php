<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Stateful class which defines entities valid inside a LIMIT part of an SQL query
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Entity_Limit extends YMKM_SQL_Entity_AbstractLimit
{
    /**
     * The number limit of the query
     * @var int
     */
    private $_number = null;

    /**
     * The offset limit of the query
     * @var int
     */
    private $_offset = null;


    /**
     * Constructor
     *
     * @param int|string $number the number limit to set
     * @param int|string $offset the offset limit to set
     */
    public function __construct($number = null, $offset = null)
    {
        $this->setOffset($offset?$offset:0);
        $this->setNumber($number?$number:2147483647);
    }


    /**
     * @see YMKM_SQL_Entity_AbstractLimit
     */
    protected function doSetNumber($n)
    {
        $this->_number = doubleval($n);
    }

    /**
     * @see YMKM_SQL_Entity_AbstractLimit
     */
    protected function doNumber()
    {
        return $this->_number;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractLimit
     */
    protected function doSetOffset($o)
    {
        $this->_offset = intval($o);
    }

    /**
     * @see YMKM_SQL_Entity_AbstractLimit
     */
    public function doOffset()
    {
        return $this->_offset;
    }


    /**
     * @see YMKM_SQL_Entity_AbstractLimit
     */
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        // Statement takes care of the parsing, nothing needed here
        return null;
    }
}
