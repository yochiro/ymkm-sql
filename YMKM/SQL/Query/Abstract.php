<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/../Domain.php');
require_once(__DIR__.'/../Entity/From.php');
require_once(__DIR__.'/../Entity/Group.php');
require_once(__DIR__.'/../Entity/Having.php');
require_once(__DIR__.'/../Entity/Join.php');
require_once(__DIR__.'/../Entity/Limit.php');
require_once(__DIR__.'/../Entity/Order.php');
require_once(__DIR__.'/../Entity/Select.php');
require_once(__DIR__.'/../Entity/Where.php');
require_once(__DIR__.'/../Iface/Entity.php');
require_once(__DIR__.'/../Iface/WhereAware.php');
require_once(__DIR__.'/../ParseException.php');
require_once(__DIR__.'/../../Exception.php');


/**
 * Abstract class to provide an interface for SQL queries (DML) through an object
 *
 * Queries currently map to SELECT type queries, ie. fetching results (Read).
 *
 * Most of the API is fluent, e.g, the object returns itself to make it possible
 * to chain function invocations.
 *
 * Query instances themselves can be used as subqueries within Where s-expressions.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Query_Abstract implements YMKM_SQL_Iface_WhereAware
{
    /**
     * Returns all column entities currently set in the SELECT part of the query
     *
     * @return array[YMKM_SQL_Iface_Entity] set columns
     */
    final public function cols()
    {
        return $this->doCols();
    }

    /**
     * Returns all table entities
     *
     * @return array[YMKM_SQL_Iface_FromAware] set tables
     */
    final public function tables()
    {
        return $this->doTables();
    }

    /**
     * Returns the FROM table(s)
     *
     * @return array[YMKM_SQL_Iface_FromAware] set from tables
     */
    final public function from()
    {
        return $this->doFrom();
    }

    /**
     * Adds a column in the SELECT part of the query
     *
     * @param YMKM_SQL_Entity_Select $s the column definition to add
     * @return $this
     */
    final public function addCol(YMKM_SQL_Entity_Select $s)
    {
        $this->doAddCol($s);
        return $this;
    }

    /**
     * Replaces a column in the SELECT part of the query
     *
     * @param YMKM_SQL_Entity_Select $o the old entity to replace
     * @param YMKM_SQL_Entity_Select $n the entity to be replaced with
     * @return $this for chaining
     */
    final public function replaceCol(YMKM_SQL_Entity_Select $o, YMKM_SQL_Entity_Select $n)
    {
        $this->doReplaceCol($o, $n);
        return $this;
    }

    /**
     * Removes one, a list or all columns from the SELECT part of the query
     *
     * @param YMKM_SQL_Entity_Select|array|null the single column, array of cols or null (=all) to remove
     * @return $this for chaining
     */
    final public function removeCols($cols=null)
    {
        $this->doRemoveCols($cols);
        return $this;
    }

    /**
     * Adds a column in the FROM part of the query
     *
     * @param YMKM_SQL_Entity_From $s the table definition to add
     * @return $this
     */
    final public function addFrom(YMKM_SQL_Entity_From $s)
    {
        $this->doAddFrom($s);
        return $this;
    }

    /**
     * Adds a column in the JOIN part of the query
     *
     * @param YMKM_SQL_Entity_Join $s the join definition to add
     * @return $this
     */
    final public function addJoin(YMKM_SQL_Entity_Join $s)
    {
        $this->doAddJoin($s);
        return $this;
    }

    /**
     * Adds where conditions to the previously stored JOIN matching specified entity
     *
     * This method assumes a Join entity with the specified table name|alias already
     * exists within the JOIN definitions.
     *
     * @param mixed $trefOrAlias opaque data which defines a table name|alias
     * @param YMKM_SQL_Entity_Where $s the where conditions to add to join if found
     * @return $this
     */
    final public function addWhereJoin($trefOrAlias, YMKM_SQL_Entity_Where $w)
    {
        $this->doAddWhereJoin($trefOrAlias, $w);
        return $this;
    }

    /**
     * Adds a column in the WHERE part of the query
     *
     * @param YMKM_SQL_Entity_Where $s the where expression to add
     * @return $this
     */
    final public function addWhere(YMKM_SQL_Entity_Where $s)
    {
        $this->doAddWhere($s);
        return $this;
    }

    /**
     * Adds a column in the GROUP BY part of the query
     *
     * @param YMKM_SQL_Entity_Group $s the group expression to add
     * @return $this
     */
    final public function addGroup(YMKM_SQL_Entity_Group $s)
    {
        $this->doAddGroup($s);
        return $this;
    }

    /**
     * Adds a column in the HAVING part of the query
     *
     * @param YMKM_SQL_Entity_Having $s the having expression to add
     * @return $this
     */
    final public function addHaving(YMKM_SQL_Entity_Having $s)
    {
        $this->doAddHaving($s);
        return $this;
    }

    /**
     * Adds a column in the ORDER BY part of the query
     *
     * @param YMKM_SQL_Entity_Order $s the order definition to add
     * @return $this
     */
    final public function addOrder(YMKM_SQL_Entity_Order $s)
    {
        $this->doAddOrder($s);
        return $this;
    }

    /**
     * Sets the query pagination values
     *
     * @param $s YMKM_SQL_Entity_Limit the limit values to set
     * @return $this
     */
    final public function setLimit(YMKM_SQL_Entity_Limit $s)
    {
        $this->doSetLimit($s);
        return $this;
    }

    /**
     * Parses and returns an SQL query
     *
     * The $domain contains all defined tables and columns for this query.
     * It can used to check for ambiguous expressions in Where/Join/Group...
     * when tables and/or columns are referenced w/o aliases or with aliases
     * that are not defined.
     * User call to parse should usually be done without the $domain parameter.
     * When the query is used as a subquery component of a parent query,
     * the $domain parameter will contain the definition domain of its parent.
     *
     * @param YMKM_SQL_Domain $domain the column and table definitions set on this query
     * @return string the parsed SQL query
     * @throw YMKM_SQL_ParseException if parsing fails
     */
    final public function parse(YMKM_SQL_Domain $domain=null)
    {
        try {
            return $this->doParse($domain);
        }
        catch (YMKM_SQL_ParseException $lspe) {
            throw new YMKM_Exception('YMKM_SQL_Query::parse() error : ' . $lspe->getMessage());
        }
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doCols();
    abstract protected function doTables();
    abstract protected function doFrom();
    abstract protected function doAddCol(YMKM_SQL_Entity_Select $s);
    abstract protected function doReplaceCol(YMKM_SQL_Entity_Select $o, YMKM_SQL_Entity_Select $n);
    abstract protected function doRemoveCols($cols=null);
    abstract protected function doAddFrom(YMKM_SQL_Entity_From $s);
    abstract protected function doAddJoin(YMKM_SQL_Entity_Join $s);
    abstract protected function doAddWhereJoin($ta, YMKM_SQL_Entity_Where $s);
    abstract protected function doAddWhere(YMKM_SQL_Entity_Where $s);
    abstract protected function doAddGroup(YMKM_SQL_Entity_Group $s);
    abstract protected function doAddHaving(YMKM_SQL_Entity_Having $s);
    abstract protected function doAddOrder(YMKM_SQL_Entity_Order $s);
    abstract protected function doSetLimit(YMKM_SQL_Entity_Limit $s);
    abstract protected function doParse(YMKM_SQL_Domain $domain=null);
}
