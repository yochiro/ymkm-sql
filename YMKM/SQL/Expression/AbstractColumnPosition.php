<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Abstract class which defines an SQL expression for a column position
 *
 * A column position is defined as an integer, referring to the nth column
 * defined in the SELECT part of the query.
 * Can be used in ORDER BY or GROUP BY clauses.
 *
 * This syntax was removed from the SQL standard and therefore is not recommended for use.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_AbstractColumnPosition
                                    extends YMKM_SQL_Expression_Abstract
                                 implements YMKM_SQL_Iface_OrderAware, YMKM_SQL_Iface_GroupAware
{
    /**
     * Constructor
     *
     * A column position needs only a positive integer as an argument.
     *
     * @param string $position the column position
     */
    public function __construct($position, $alias=null)
    {
        $this->setPosition($position);
        $this->setAlias($alias);
    }

    /**
     * Sets the column position
     *
     * @param int|string $position the column position
     * @return $this for chaining
     */
    final public function setPosition($position)
    {
        $this->doSetPosition($position);
        return $this;
    }

    /**
     * Returns the column position
     *
     * @return int the column position
     */
    final public function position()
    {
        return $this->doPosition();
    }

    /**
     * When used as a column definition, return column position alias
     *
     * @return string the alias if any
     */
    final public function alias()
    {
        return $this->doAlias();
    }

    final public function setAlias($alias)
    {
        $this->doSetAlias($alias);
        return $this;
    }

    /**
     * @see YMKM_SQL_Expression_Abstract
     *
     * Just return the column position, $domain and $this->alias() are unneeded
     */
    final protected function doParse(YMKM_SQL_Domain $domain)
    {
        return $this->position();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetPosition($position);
    abstract protected function doSetAlias($alias);
    abstract protected function doPosition();
    abstract protected function doAlias();
}
