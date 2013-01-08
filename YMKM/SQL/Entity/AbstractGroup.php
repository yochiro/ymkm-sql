<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Abstract.php');
require_once(__DIR__.'/../Iface/GroupAware.php');


/**
 * Abstract class which entities valid inside a GROUP BY part of an SQL query
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Entity_AbstractGroup extends YMKM_SQL_Entity_Abstract
{
    /**
     * Sets the target column to group by
     *
     * @param YMKM_SQL_Iface_GroupAware $t the target column to group by
     * @return $this for chaining
     */
    final public function setTarget(YMKM_SQL_Iface_GroupAware $t)
    {
        $this->doSetTarget($t);
        return $this;
    }

    /**
     * Returns the target column to group by
     *
     * @return YMKM_SQL_Iface_GroupAware the target column to group by
     */
    final public function target()
    {
        return $this->doTarget();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetTarget(YMKM_SQL_Iface_GroupAware $t);
    abstract protected function doTarget();
}
