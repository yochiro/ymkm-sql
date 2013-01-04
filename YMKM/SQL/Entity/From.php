<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Stateful class which defines entities valid inside a FROM part of an SQL query
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Entity_From extends YMKM_SQL_Entity_AbstractFrom
{
    /**
     * Stores the source (table) definition
     * @var YMKM_SQL_Iface_FromAware
     */
    private $_source = null;


    /**
     * Constructor
     *
     * @param YMKM_SQL_Iface_FromAware $s the source (table) to handle
     */
    public function __construct(YMKM_SQL_Iface_FromAware $s)
    {
        $this->setSource($s);
    }


    /**
     * @see YMKM_SQL_Entity_AbstractFrom
     */
    protected function doSetSource(YMKM_SQL_Iface_FromAware $t)
    {
        $this->_source = $t;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractFrom
     */
    protected function doSource()
    {
        return $this->_source;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractSelect
     */
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        // Returns source->parse [AS source->alias]
        return $this->_source->parse($domain) .
                (!is_null($this->_source->alias())?
                 ' AS ' . $this->_source->alias():
                 '');
    }
}
