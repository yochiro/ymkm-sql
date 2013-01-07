<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Abstract class which defines entities valid inside a JOIN part of an SQL query
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Entity_AbstractJoin extends YMKM_SQL_Entity_Abstract
{
    /**
     * Sets the join type (LEFT, RIGHT...)
     *
     * @param string $jType the join type to set
     * @return $this for chaining
     */
    final public function setJoinType($jType)
    {
        $this->doSetJoinType($jType);
        return $this;
    }

    /**
     * Returns the join type
     *
     * @return string the join type
     */
    final public function joinType()
    {
        return $this->doJoinType();
    }

    /**
     * Sets the target (table) definition
     *
     * @param YMKM_SQL_Iface_FromAware $t the target
     * @return $this for chaining
     */
    final public function setTarget(YMKM_SQL_Iface_FromAware $t)
    {
        $this->doSetTarget($t);
        return $this;
    }

    /**
     * Returns the target definition on current entity
     *
     * @return YMKM_SQL_Iface_FromAware target definition
     */
    final public function target()
    {
        return $this->doTarget();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetJoinType($jType);
    abstract protected function doJoinType();
    abstract protected function doSetTarget(YMKM_SQL_Iface_FromAware $t);
    abstract protected function doTarget();
}
