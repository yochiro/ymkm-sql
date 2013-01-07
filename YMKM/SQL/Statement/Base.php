<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Defines a stateful abstract class of YMKM_SQL_Statement_Abstract
 *
 * This abstract class physically stores entities it later parses.
 *
 * parsing uses lambda functions, and thus this class requires PHP5.3 or more
 * to work.
 *
 * @require PHP 5.3+ (Lambda-fn)
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Statement_Base extends YMKM_SQL_Statement_Abstract
{
    /**
     * All entities assigned to this statement
     * @var array
     */
    private $_entities = array();


    /**
     * @see YMKM_SQL_Statement_Abstract
     */
    final protected function doAddEntity(YMKM_SQL_Iface_Entity $entity)
    {
        array_push($this->_entities, $entity);
    }

    /**
     * @see YMKM_SQL_Statement_Abstract
     */
    final protected function doClearEntities()
    {
        $this->_entities = array();
    }

    /**
     * @see YMKM_SQL_Statement_Abstract
     */
    final protected function doSetEntity(YMKM_SQL_Iface_Entity $entity)
    {
        $this->clearEntities();
        $this->addEntity($entity);
    }

    /**
     * @see YMKM_SQL_Statement_Abstract
     */
    final protected function doReplaceEntity($i, YMKM_SQL_Iface_Entity $entity)
    {
        if (isset($this->_entities[$i])) {
            $this->_entities[$i] = $entity;
        }
        else {
            throw new YMKM_Exception('Invalid index!');
        }
    }

    /**
     * @see YMKM_SQL_Statement_Abstract
     */
    final protected function doEntities()
    {
        return $this->_entities;
    }

    /**
     * @see YMKM_SQL_Statement_Abstract
     */
    final protected function doParse(YMKM_SQL_Domain $domain)
    {
        $out = '';
        // For use as a closure variable
        $self = $this;
        if (!empty($this->_entities)) {
            // Calls _doParse with lambda function as first parameter :
            // It map/reds the stored entities in this statement
            // Using subclass provided map/reduce functions and initial value.
            $out .= $this->_doParse(
                      function($m, $r, $i=null) use ($self) {
                        return array_reduce(
                               array_map($m, $self->entities()),
                               $r, $i);
                      }, $domain);
        }
        return $out;
    }


    /// Abstract method to be implemented by subclasses. ///

    /**
     * Parses SQL part using the lambda function supplied as first parameter
     *
     * The supplied $entitiesFn should be called within the subclass _doParse
     * and takes 3 parameters :
     * - Closure $m : map function : called on each entity of the statement instance
     * - Closure $r : reduce function : called on generated pairs from previous map function
     * - mixed $i : initial value to apply to reduce function when needed.
     *
     * Note : calling the $entitiesFn is not required, and only the return value
     * of _doParse becomes the result of this statement's parsing.
     * It just offers a structure that should be useable for most statement
     * constructs.
     *
     * @param Closure $entitiesFn the lambda function performing map/reduce on entities.
     * @param YMKM_SQL_Domain $domain the column and table definitions set on this query
     * @return string the parsed SQL statement
     * @throw YMKM_SQL_ParseException if parsing fails
     */
    abstract protected function _doParse(Closure $entitiesFn, YMKM_SQL_Domain $domain);
}
