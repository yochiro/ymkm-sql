<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Defines abstract class which represent basic fules for SQL statement in queries
 *
 * Each statement contains a list of entities which are then used to render the
 * whole part the statement is meant to generate.
 *
 * Most of the API is fluent, e.g, the object returns itself to make it possible
 * to chain function invocations.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Statement_Abstract implements YMKM_SQL_Iface_Statement
{
    /**
     * Adds a new SQL entity to this statement
     *
     * @param YMKM_SQL_Iface_Entity $entity the entity to add
     * @return $this for chaining
     */
    final public function addEntity(YMKM_SQL_Iface_Entity $entity)
    {
        $this->doAddEntity($entity);
        return $this;
    }

    /**
     * Sets given SQL entity to this statement
     *
     * Any previous list of entities will be overwritten after this call.
     *
     * @param YMKM_SQL_Iface_Entity $entity the entity to set
     * @return $this for chaining
     */
    final public function setEntity(YMKM_SQL_Iface_Entity $entity)
    {
        $this->doSetEntity($entity);
        return $this;
    }

    /**
     * Clear SQL entities stored in this statement
     *
     * @return $this for chaining
     */
    final public function clearEntities()
    {
        $this->doClearEntities();
        return $this;
    }

    /**
     * Replaces SQL entity located at index $i with given new SQL entity
     *
     * @param int $i the index of the entity to replace
     * @param YMKM_SQL_Iface_Entity $entity the new entity to replace with
     * @return $this for chaining
     * @throw YMKM_Exception when supplied index is invalid
     */
    final public function replaceEntity($i, YMKM_SQL_Iface_Entity $entity)
    {
        $this->doReplaceEntity($i, $entity);
        return $this;
    }

    /**
     * Returns SQL entities stored in this statement
     *
     * @return array(YMKM_SQL_Iface_Entity) list of entities
     */
    final public function entities()
    {
        return $this->doEntities();
    }

    /**
     * Parses and returns an SQL part based on SQL statement rules
     *
     * The $domain contains all defined tables and columns for this query.
     * It can used to check for ambiguous expressions in Where/Join/Group...
     * when tables and/or columns are referenced w/o aliases or with aliases
     * that are not defined.
     *
     * @return string the parsed SQL part
     * @throw YMKM_SQL_ParseException if parsing fails
     */
    final public function parse(YMKM_SQL_Domain $domain)
    {
        return $this->doParse($domain);
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doAddEntity(YMKM_SQL_Iface_Entity $entity);
    abstract protected function doSetEntity(YMKM_SQL_Iface_Entity $entity);
    abstract protected function doClearEntities();
    abstract protected function doReplaceEntity($i, YMKM_SQL_Iface_Entity $entity);
    abstract protected function doEntities();
    abstract protected function doParse(YMKM_SQL_Domain $domain);
}
