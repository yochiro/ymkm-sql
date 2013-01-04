<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Abstract class which defines entities valid inside a LIMIT part of an SQL query
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Entity_AbstractLimit extends YMKM_SQL_Entity_Abstract
{
    /**
     * Sets the results number limit to set
     *
     * @param int|string $n the limit to set
     * @return $this for chaining
     */
    final public function setNumber($n)
    {
        $this->doSetNumber($n);
        return $this;
    }

    /**
     * Returns the limit
     *
     * @return int the limit set
     */
    final public function number()
    {
        return $this->doNumber();
    }

    /**
     * Sets the results offset to set
     *
     * @param int|string $o the offset to set
     * @return $this for chaining
     */
    final public function setOffset($o)
    {
        $this->doSetOffset($o);
        return $this;
    }

    /**
     * Returns the offset
     *
     * @return int the offset set
     */
    final public function offset()
    {
        return $this->doOffset();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetNumber($n);
    abstract protected function doNumber();
    abstract protected function doSetOffset($o);
    abstract protected function doOffset();
}
