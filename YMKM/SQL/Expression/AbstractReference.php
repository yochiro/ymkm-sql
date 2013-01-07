<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Abstract class which defines an SQL expression which references a previous definition
 *
 * References are used outside of the definition area, ie.
 * Column references outside of SELECT
 * Table references outside of FROM/JOIn
 * The refName is used to reference either the alias, the name...
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_AbstractReference extends YMKM_SQL_Expression_Abstract
{
    /**
     * Sets the reference name
     *
     * @param string $refName the reference name
     * @return $this for chaining
     */
    final public function setRefName($refName)
    {
        $this->doSetRefName($refName);
        return $this;
    }

    /**
     * Returns the reference name
     *
     * @return string the reference name
     */
    final public function refName()
    {
        return $this->doRefName();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetRefName($refName);
    abstract protected function doRefName();
}
