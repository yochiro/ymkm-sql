<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Abstract class which defines entities valid inside a ORDER BY part of an SQL query
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Entity_AbstractOrder extends YMKM_SQL_Entity_Abstract
{
    /**
     * Sets the sort direction (ASC, DESC)
     *
     * @param string $dir the sort direction, ASC or DESC
     * @return $this for chaining
     */
    final public function setDir($dir)
    {
        $this->doSetDir($dir);
        return $this;
    }

    /**
     * Returns the sort direction : ASC or DESC
     *
     * @return string the sort direction (ASC or DESC)
     */
    final public function dir()
    {
        return $this->doDir();
    }

    /**
     * Sets the target column to order by
     *
     * @param YMKM_SQL_Iface_OrderAware $t the target column to order by
     * @return $this for chaining
     */
    final public function setTarget(YMKM_SQL_Iface_OrderAware $t)
    {
        $this->doSetTarget($t);
        return $this;
    }

    /**
     * Returns the target column to order by
     *
     * @return YMKM_SQL_Iface_OrderAware the target column to order by
     */
    final public function target()
    {
        return $this->doTarget();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetDir($dir);
    abstract protected function doDir();
    abstract protected function doSetTarget(YMKM_SQL_Iface_OrderAware $t);
    abstract protected function doTarget();
}
