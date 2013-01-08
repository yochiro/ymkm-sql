<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Domain.php');
require_once(__DIR__.'/Entity/From.php');
require_once(__DIR__.'/Entity/Group.php');
require_once(__DIR__.'/Entity/Having.php');
require_once(__DIR__.'/Entity/Join.php');
require_once(__DIR__.'/Entity/Limit.php');
require_once(__DIR__.'/Entity/Order.php');
require_once(__DIR__.'/Entity/Select.php');
require_once(__DIR__.'/Entity/Where.php');
require_once(__DIR__.'/Query/Abstract.php');
require_once(__DIR__.'/Statement/From.php');
require_once(__DIR__.'/Statement/Group.php');
require_once(__DIR__.'/Statement/Having.php');
require_once(__DIR__.'/Statement/Join.php');
require_once(__DIR__.'/Statement/Limit.php');
require_once(__DIR__.'/Statement/Order.php');
require_once(__DIR__.'/Statement/Select.php');
require_once(__DIR__.'/Statement/Where.php');


/**
 * Implementation of an SQL query class
 *
 * Queries currently map to SELECT type queries, ie. fetching results (Read).
 * Implementation acts as a proxy for YMKM_SQL_Iface_Statement objects,
 * delegating addXYZ methods to the Statement instance's addEntity  assigned to XYZ.
 *
 * parsing uses lambda functions, and thus this class requires PHP5.3 or more
 * to work.
 *
 * @require PHP 5.3+ (Lambda-fn)
 * @package ymkm-sql
 */
final class YMKM_SQL_Query extends YMKM_SQL_Query_Abstract
{
    /**
     * List of SQL statement parts
     * @var array
     */
    private $_sqlStatementParts = array('select' => null,
                                      'from'   => null,
                                      'join'   => null,
                                      'where'  => null,
                                      'group'  => null,
                                      'having' => null,
                                      'order'  => null,
                                      'limit'  => null);

    /**
     * Constructor
     *
     * creates Statement instances for each SQL query relevant part.
     */
    public function __construct()
    {
        $this->_sqlStatementParts['select'] = new YMKM_SQL_Statement_Select();
        $this->_sqlStatementParts['from']   = new YMKM_SQL_Statement_From();
        $this->_sqlStatementParts['join']   = new YMKM_SQL_Statement_Join();
        $this->_sqlStatementParts['where']  = new YMKM_SQL_Statement_Where();
        $this->_sqlStatementParts['group']  = new YMKM_SQL_Statement_Group();
        $this->_sqlStatementParts['having'] = new YMKM_SQL_Statement_Having();
        $this->_sqlStatementParts['order']  = new YMKM_SQL_Statement_Order();
        $this->_sqlStatementParts['limit']  = new YMKM_SQL_Statement_Limit();
    }


    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doCols()
    {
        return array_map(function($e) {
                           return $e->colDef();
                         }, $this->_sqlStatementParts['select']->entities());
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doTables()
    {
        return array_merge(
                 array_map(function($e) {
                             return $e->source();
                           }, $this->_sqlStatementParts['from']->entities()),
                 array_map(function($e) {
                             return $e->target();
                           }, $this->_sqlStatementParts['join']->entities()));
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doFrom()
    {
        return array_map(function($e) {
                             return $e->source();
                           }, $this->_sqlStatementParts['from']->entities());
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doAddCol(YMKM_SQL_Entity_Select $s)
    {
        $this->_sqlStatementParts['select']->addEntity($s);
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doReplaceCol(YMKM_SQL_Entity_Select $o,
                                    YMKM_SQL_Entity_Select $n)
    {
        foreach($this->_sqlStatementParts['select'] as $i => $s) {
            if ($o == $s) {
                $this->_sqlStatementParts['select']->replaceEntity($i, $n);
                break;
            }
        }
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doRemoveCols($cols=null)
    {
        if (is_null($cols)) {
            $this->_sqlStatementParts['select']->clearEntities();
        }
        else {
            $currCols = $this->_sqlStatementParts['select']->entities();
            $newCols = array();
            if ($cols instanceof YMKM_SQL_Entity_Select) {
                $cols = array($cols);
            }
            $removed = false;
            foreach($currCols as $i => $s) {
                $idx = false;
                if (false === ($idx = array_search($s, $cols))) {
                    array_push($newCols, $s);
                }
                else {
                    $removed = true;
                }
            }
            if ($removed) {
                $this->_sqlStatementParts['select']->setEntities($newCols);
            }
        }
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doAddFrom(YMKM_SQL_Entity_From $s)
    {
        $this->_sqlStatementParts['from']->addEntity($s);
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doAddJoin(YMKM_SQL_Entity_Join $s)
    {
        $this->_sqlStatementParts['join']->addEntity($s);
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doAddWhereJoin($trefOrAlias, YMKM_SQL_Entity_Where $s)
    {
        foreach ($this->_sqlStatementParts['join']->entities() as $idx => $e) {
            $t = $e->target(); $a = $t->alias(); $n = $t->name();
            if (($trefOrAlias === $a) || ($trefOrAlias === $n)) {
                $e->augment($s);
                $this->_sqlStatementParts['join']->replaceEntity($idx, $e);
                break;
            }
        }
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doAddWhere(YMKM_SQL_Entity_Where $s)
    {
        $this->_sqlStatementParts['where']->addEntity($s);
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doAddGroup(YMKM_SQL_Entity_Group $s)
    {
        $this->_sqlStatementParts['group']->addEntity($s);
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doAddHaving(YMKM_SQL_Entity_Having $s)
    {
        $this->_sqlStatementParts['having']->addEntity($s);
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doAddOrder(YMKM_SQL_Entity_Order $s)
    {
        $this->_sqlStatementParts['order']->addEntity($s);
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doSetLimit(YMKM_SQL_Entity_Limit $s)
    {
        $this->_sqlStatementParts['limit']->setEntity($s);
    }

    /**
     * @see YMKM_SQL_Query_Abstract
     */
    protected function doParse(YMKM_SQL_Domain $domain=null)
    {
        // Create a new environment for the new parsing, with all previous
        // values as a default.
        $newDomain = new YMKM_SQL_Domain();
        $newDomain->set('tables', array());
        $newDomain->set('columns', array());
        if ($domain instanceof YMKM_SQL_Domain) {
            foreach ($domain->get() as $k => $v) {
                $newDomain->set($k, $v);
            }
        }

        $newDomain->set('from',
            array_map(
              function($t) {
                return $t->source(); },
              $this->_sqlStatementParts['from']->entities()));
        // Contains all entities defined in FROM and JOIN SQL parts :
        // determines domain in which all other SQL parts can be queried.
        $newDomain->set('tables',
          array_merge(
            $newDomain->get('tables'),
            $newDomain->get('from'),
            array_map(
              function($t) {
                return $t->target(); },
              $this->_sqlStatementParts['join']->entities())));
        $newDomain->set('columns',
          array_merge(
            $newDomain->get('columns'),
            array_map(
              function($c) {
                return $c->colDef(); },
                  $this->_sqlStatementParts['select']->entities())));

        // Provides map/reduce lambda functions to _doParse
        // Map function : calls parse($domain) on each entity
        // Red function : Concatenates all entity generated content in one string
        $r = $this->_doParse(
                 function($n) use($newDomain) {
                   return $n->parse($newDomain); },
                 function($m, $n) {
                   return $m.(''!==$m&&''!==$n?' ':'').$n; }, '');
        // Copy back the values in the new domain to the current domain
        // except tables and columns that should not be propagated back
        foreach ($newDomain->get() as $k => $v) {
            if ('tables' !== $k && 'columns' !== $k && 'from' !== $k) {
                $domain->set($k, $v);
            }
        }
        return $r;
    }


    /**
     * Parses this query using given map/red fns
     */
    protected function _doParse($mapFn, $reduceFn, $init)
    {
        return array_reduce(
                 @array_map($mapFn, $this->_sqlStatementParts),
                 $reduceFn, $init);
    }
}
