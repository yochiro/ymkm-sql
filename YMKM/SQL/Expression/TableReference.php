<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Stateful class which defines an SQL expression for a table reference
 *
 * A table reference is valid anywhere outside FROM/JOIN statements :
 * It assumes a table definition was created in the FROM/JOIN section
 * with specified reference name and enforces that check during parsing.
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Expression_TableReference
                        extends YMKM_SQL_Expression_AbstractTableReference
{
    /**
     * Table refName/name
     * @var string
     */
    private $_refName = null;


    /**
     * Constructor
     *
     * A table reference only needs a reference name, as it supposes the table is
     * already defined somewhere else.
     *
     * @param string $refName the table name reference
     */
    public function __construct($refName=null)
    {
        $this->setRefName($refName);
    }


    /**
     * @see YMKM_SQL_Expression_AbstractTableReference
     */
    protected function doSetRefName($refName)
    {
        $this->_refName = $refName;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractTableReference
     */
    protected function doRefName()
    {
        return $this->_refName;
    }

    protected function _doParse(YMKM_SQL_Domain $domain)
    {
        return $this->refName();
    }
}
