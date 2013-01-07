<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Abstract class which defines entities valid inside a WHERE part of an SQL query
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Entity_AbstractWhere extends YMKM_SQL_Entity_Abstract
{
    /**
     * Sets the where expression on current entity
     *
     * @param YMKM_SQL_Iface_WhereAware $expr any entity useable within WHEREs
     * @return $this for chaining
     */
    final public function setExpr(YMKM_SQL_Iface_WhereAware $expr)
    {
        $this->doSetExpr($expr);
        return $this;
    }

    /**
     * Returns the expression set on current entity
     *
     * @return YMKM_SQL_Iface_WhereAware expression
     */
    final public function expr()
    {
        return $this->doExpr();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetExpr(YMKM_SQL_Iface_WhereAware $expr);
    abstract protected function doExpr();
}
