<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Stateful class which defines entities valid inside a SELECT part of an SQL query
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Entity_Select extends YMKM_SQL_Entity_AbstractSelect
{
    /**
     * Stores the column definition
     * @var YMKM_SQL_Iface_SelectAware
     */
    private $_colDef = null;


    /**
     * Constructor
     *
     * @param YMKM_SQL_Iface_SelectAware $c the column to handle
     */
    public function __construct(YMKM_SQL_Iface_SelectAware $c)
    {
        $this->setColDef($c);
    }


    /**
     * @see YMKM_SQL_Entity_AbstractSelect
     */
    protected function doSetColDef(YMKM_SQL_Iface_SelectAware $c)
    {
        $this->_colDef = $c;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractSelect
     */
    protected function doColDef()
    {
        return $this->_colDef;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractSelect
     */
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        // Returns colDef->parse [AS colDef->alias]
        return $this->_colDef->parse($domain) .
               (!is_null($this->_colDef->alias())?
                ' AS ' . $this->_colDef->alias():
                '');
    }
}
