<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Entity.php');
require_once(__DIR__.'/Parseable.php');


/**
 * Defines interface for SQL statement parts in queries
 *
 * Each statement contains a list of entities which are then used to render the
 * whole part the statement is meant to generate.
 *
 * @package ymkm-sql
 */
interface YMKM_SQL_Iface_Statement extends YMKM_SQL_Iface_Parseable
{
    /**
     * Adds a new SQL entity to this statement
     *
     * @param YMKM_SQL_Iface_Entity $entity the entity to add
     * @return $this for chaining
     */
    function addEntity(YMKM_SQL_Iface_Entity $entity);

    /**
     * Sets given SQL entity to this statement
     *
     * Any previous list of entities will be overwritten after this call.
     *
     * @param YMKM_SQL_Iface_Entity $entity the entity to set
     * @return $this for chaining
     */
    function setEntity(YMKM_SQL_Iface_Entity $entity);

    /**
     * Clear SQL entities stored in this statement
     *
     * @return $this for chaining
     */
    function clearEntities();

    /**
     * Replaces SQL entity determined by $old with given new SQL entity
     *
     * @param mixed $old the old entity to replace, or its location
     * @param YMKM_SQL_Iface_Entity $entity the new entity to replace with
     * @return $this for chaining
     * @throw YMKM_Exception when supplied index is invalid
     */
    function replaceEntity($old, YMKM_SQL_Iface_Entity $entity);

    /**
     * Returns SQL entities stored in this statement
     *
     * @return array(YMKM_SQL_Iface_Entity) list of entities
     */
    function entities();
}
