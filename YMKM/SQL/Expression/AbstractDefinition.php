<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Abstract class which defines an SQL expression which defines a new entity in the domain
 *
 * Columns are defined in the SELECT statement while Tables are defined in FROM/JOIN area.
 * They need a canonical name and an optional alias.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_AbstractDefinition extends YMKM_SQL_Expression_Abstract
{
    /**
     * Sets the column name
     *
     * @param string $name the column name
     * @return $this for chaining
     */
    final public function setName($name)
    {
        $this->doSetName($name);
        return $this;
    }

    /**
     * Returns the column name
     *
     * @return string the column name
     */
    final public function name()
    {
        return $this->doName();
    }

    /**
     * Sets the column alias
     *
     * @param string $alias the alias to set
     * @return $this for chaining
     */
    final public function setAlias($alias)
    {
        $this->doSetAlias($alias);
        return $this;
    }

    /**
     * Returns the column alias
     *
     * @return string the column alias
     */
    final public function alias()
    {
        return $this->doAlias();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetName($name);
    abstract protected function doName();
    abstract protected function doSetAlias($alias);
    abstract protected function doAlias();
}
