<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Abstract class which defines entities valid inside a SELECT part of an SQL query
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Entity_AbstractSelect extends YMKM_SQL_Entity_Abstract
{
    /**
     * Sets the column definition on current entity
     *
     * @param YMKM_SQL_Iface_SelectAware $c any entity useable within SELECTs
     * @return $this for chaining
     */
    final public function setColDef(YMKM_SQL_Iface_SelectAware $c)
    {
        $this->doSetColDef($c);
        return $this;
    }

    /**
     * Returns the column definition on current entity
     *
     * @return YMKM_SQL_Iface_SelectAware column definition
     */
    final public function colDef()
    {
        return $this->doColDef();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetColDef(YMKM_SQL_Iface_SelectAware $c);
    abstract protected function doColDef();
}
