<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Stateful class which defines an SQL expression for a column position
 *
 * A column position is defined as an integer, referring to the nth column
 * defined in the SELECT part of the query.
 * Can be mosly used in ORDER BY clauses.
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Expression_ColumnPosition
                            extends YMKM_SQL_Expression_AbstractColumnPosition
{
    /**
     * The column position
     * @var int
     */
    private $_position = null;

    /**
     * The column alias if any
     * @var string
     */
    private $_alias = null;


    /**
     * @see YMKM_SQL_Expression_AbstractColumnPosition
     */
    protected function doSetPosition($position)
    {
        $this->_position = $position;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractColumnPosition
     */
    protected function doPosition()
    {
        return $this->_position;
    }


    /**
     * @see YMKM_SQL_Expression_AbstractColumnPosition
     */
    protected function doSetAlias($alias)
    {
        $this->_alias = $alias;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractColumnPosition
     */
    protected function doAlias()
    {
        return $this->_alias;
    }
}
