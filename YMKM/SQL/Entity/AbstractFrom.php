<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Abstract class which defines entities valid inside a FROM part of an SQL query
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Entity_AbstractFrom extends YMKM_SQL_Entity_Abstract
{
    /**
     * Sets the source (table) definition on current entity
     *
     * @param YMKM_SQL_Iface_FromAware $t any entity useable within FROMs
     * @return $this for chaining
     */
    final public function setSource(YMKM_SQL_Iface_FromAware $t)
    {
        $this->doSetSource($t);
        return $this;
    }

    /**
     * Returns the source definition on current entity
     *
     * @return YMKM_SQL_Iface_FromAware source (table) definition
     */
    final public function source()
    {
        return $this->doSource();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetSource(YMKM_SQL_Iface_FromAware $t);
    abstract protected function doSource();
}
