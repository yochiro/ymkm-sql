<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Stateful class which defines an SQL expression for a table definition
 *
 * A table definition is valid within FROM/JOIN statements :
 * It creates a new table reference given a name and an optional alias.
 * Once defined, that table can be referenced in other parts of the
 * statement using either its alias (if provided), or its name.
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Expression_TableDefinition
                                    extends YMKM_SQL_Expression_AbstractTableDefinition
{
    /**
     * The table alias
     * @var string
     */
    private $_alias = null;

    /**
     * The table name
     * @var string
     */
    private $_name = null;


    /**
     * @see YMKM_SQL_Expression_AbstractTable
     */
    protected function doSetAlias($alias)
    {
        $this->_alias = $alias;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractTable
     */
    protected function doAlias()
    {
        return $this->_alias;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractTableDefinition
     */
    protected function doSetName($name)
    {
        $this->_name = $name;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractTableDefinition
     */
    protected function doName()
    {
        return $this->_name;
    }

    protected function _doParse(YMKM_SQL_Domain $domain)
    {
        return $this->name();
    }

    protected function doToTableReference()
    {
        return new YMKM_SQL_Expression_TableRefeference($this->alias());
    }
}
