<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php


/**
 * Implementation of the ZenMagick database layer using <em>Creole</em>.
 *
 * @see http://creole.phpdb.org/trac/
 * @author DerManoMann
 * @package org.zenmagick.database.provider
 * @version $Id$
 */
class ZMCreoleDatabase extends ZMObject implements ZMDatabase {
    private $conn_;
    private $queriesCount;
    private $queriesTime;
    private $queriesMap = array();
    private $mapper;


    /**
     * Create a new instance.
     *
     * @param array conf Configuration properties.
     */
    function __construct($conf) {
        parent::__construct();
        $drivers = array(
            'mysql' => 'MySQLConnection',
            'mysqli' => 'MySQLiConnection',
            'pgsql' => 'PgSQLConnection',
            'sqlite' => 'SQLiteConnection',
            'oracle' => 'OCI8Connection',
            'mssql' => 'MSSQLConnection',
            'odbc' => 'ODBCConnection'
        );
        if (!array_key_exists($conf['driver'], $drivers)) {
            throw ZMLoader::make('DatabaseException', 'invalid driver: ' . $conf['driver']);
        }
        // avoid creole dot notation as that does not work with the compressed version
        Creole::registerDriver($conf['driver'], $drivers[$conf['driver']]);
        // map some things that are named differently
        $conf['phptype'] = $conf['driver'];
        $conf['hostspec'] = $conf['host'];
        $this->conn_ = Creole::getConnection($conf);
        $this->mapper = ZMDbTableMapper::instance();
        $this->queriesCount = 0;
        $this->queriesTime = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * {@inheritDoc}
     */
    public function setAutoCommit($value) {
        $this->conn_->setAutoCommit($value);
    }

    /**
     * {@inheritDoc}
     */
    public function commit() {
        $this->conn_->commit();
    }

    /**
     * {@inheritDoc}
     */
    public function rollback() {
        $this->conn_->rollback();
    }

    /**
     * {@inheritDoc}
     */
    public function getStats() {
        $stats = array();
        $stats['time'] = $this->queriesTime;
        $stats['queries'] = $this->queriesCount;
        $stats['details'] = $this->queriesMap;
        return $stats;
    }

    /**
     * Get the elapsed time since <code>$start</code>.
     *
     * @param string start The starting time.
     * @return long The time in milliseconds.
     */
    protected function getExecutionTime($start) {
        $start = explode (' ', $start);
        $end = explode (' ', microtime());
        return $end[1]+$end[0]-$start[1]-$start[0];
    }

    /**
     * {@inheritDoc}
     */
    public function createModel($table, $model, $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping(null !== $mapping ? $mapping : $table);

        $sql = 'INSERT INTO '.$table.' SET';
        $firstSet = true;
        $properties = array_flip($model->getPropertyNames());
        foreach ($mapping as $field) {
            // ignore unset custom fields as they might not allow NULL but have defaults
            if (!$field['custom'] || isset($properties[$field['property']])) {
                if (!$field['auto']) {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'];
                    $firstSet = false;
                }
            }
        }

        $stmt = $this->prepareStatement($sql, $model, $mapping);
        $idgen = $this->conn_->getIdGenerator();
        $newId = null;
        //XXX: add support for SEQUENCE?
        if($idgen->isBeforeInsert()) {
            $newId = $idgen->getId();
            $stmt->executeUpdate();
        } else { // isAfterInsert()
            $stmt->executeUpdate();
            $newId = $idgen->getId();
        }
        ++$this->queriesCount;

        foreach ($mapping as $property => $field) {
            if ($field['auto']) {
                ZMBeanUtils::setAll($model, array($property => $newId));
            }
        }

        $this->queriesMap[] = array('time'=>$this->getExecutionTime($startTime), 'sql'=>$sql);
        $this->queriesTime += $this->getExecutionTime($startTime);
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $data=array(), $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping($mapping);

        $stmt = $this->prepareStatement($sql, $data, $mapping);
        $rows = $stmt->executeUpdate();
        ++$this->queriesCount;
        $this->queriesMap[] = array('time'=>$this->getExecutionTime($startTime), 'sql'=>$sql);
        $this->queriesTime += $this->getExecutionTime($startTime);
        return $rows;
    }

    /**
     * {@inheritDoc}
     */
    public function updateModel($table, $model, $mapping=null) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping(null !== $mapping ? $mapping : $table);

        $sql = 'UPDATE '.$table.' SET';
        $firstSet = true;
        $firstWhere = true;
        $where = ' WHERE ';
        $properties = array_flip($model->getPropertyNames());
        foreach ($mapping as $field) {
            // ignore unset custom fields as they might not allow NULL but have defaults
            if (!$field['custom'] || isset($properties[$field['property']])) {
                if ($field['key']) {
                    if (!$firstWhere) {
                        $where .= ' AND ';
                    }
                    $where .= $field['column'].' = :'.$field['property'];
                    $firstWhere = false;
                } else {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'];
                    $firstSet = false;
                }
            }
        }
        if (7 > strlen($where)) {
            throw ZMLoader::make('ZMException', 'missing key');
        }
        $sql .= $where;

        $stmt = $this->prepareStatement($sql, $model, $mapping);
        $stmt->executeUpdate();
        ++$this->queriesCount;
        $this->queriesMap[] = array('time'=>$this->getExecutionTime($startTime), 'sql'=>$sql);
        $this->queriesTime += $this->getExecutionTime($startTime);
    }

    /**
     * {@inheritDoc}
     */
    public function querySingle($sql, $args=array(), $mapping=null, $modelClass=null) {
        $results = $this->query($sql, $args, $mapping, $modelClass);
        return 0 < count($results) ? $results[0] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function query($sql, $args=array(), $mapping=null, $modelClass=null) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping($mapping);

        $stmt = $this->prepareStatement($sql, $args, $mapping);
        $rs = $stmt->executeQuery();
        ++$this->queriesCount;

        $results = array();
        while ($rs->next()) {
            $results[] = $this->rs2model($modelClass, $rs, $mapping);
        }

        $this->queriesMap[] = array('time'=>$this->getExecutionTime($startTime), 'sql'=>$sql);
        $this->queriesTime += $this->getExecutionTime($startTime);
        return $results;
    }

    /**
     * Create a prepared statement.
     *
     * @param string sql The initial SQL.
     * @param mixed args The data either as map or ZMModel instance.
     * @param array mapping The field mapping.
     * @return A <code>PreparedStatement</code> or null;
     */
    protected function prepareStatement($sql, $args, $mapping=null) {
        // make sure we are working on a map
        if (is_object($args)) {
            $args = ZMBeanUtils::obj2map($args, array_keys($mapping));
        }

        // find out the order of args
        // the sorting is done to avoid invalid matches in cases where one key is the prefix of another
        $argKeys = array_keys($args);
        rsort($argKeys);
        $regexp = ':'.implode($argKeys, '|:');
        preg_match_all('/'.$regexp.'/', $sql, $argOrder);
        $argOrder = $argOrder[0];
        // modify SQL replacing :key syntax with ?
        foreach (explode('|', $regexp) as $ii => $key) {
            $name = substr($key, 1);
            if (!empty($name)) {
                $pl = '?';
                if (isset($args[$name]) && is_array($args[$name])) {
                    // expand placeholder
                    for ($ii=1; $ii < count($args[$name]); ++$ii) {
                        $pl .= ',?';
                    }
                }
                $sql = str_replace($key, $pl, $sql);
            }
        }

        // create statement
        $stmt = $this->conn_->prepareStatement($sql);
        $index = 1;
        // set values by index
        foreach ($argOrder as $name) {
            $name = substr($name, 1);
            $type = $mapping[$name]['type'];
            $values = $args[$name];
            if (!is_array($values)) {
                // treat all values as value arrays
                $values = array($values);
            }
            foreach ($values as $value) {
                switch ($type) {
                case 'integer':
                    $stmt->setInt($index, $value);
                    break;
                case 'boolean':
                    $stmt->setBoolean($index, $value);
                    break;
                case 'string':
                    $stmt->setString($index, $value);
                    break;
                case 'float':
                    $stmt->setFloat($index, $value);
                    break;
                case 'datetime':
                    if (null === $value) {
                        $value = ZM_DB_NULL_DATETIME;
                    }
                    $stmt->setTimestamp($index, $value);
                    break;
                case 'date':
                    if (null === $value) {
                        $value = ZM_DB_NULL_DATE;
                    }
                    $stmt->setDate($index, $value);
                    break;
                case 'blob':
                    $stmt->setBlob($index, $value);
                    break;
                default:
                    throw ZMLoader::make('ZMException', 'unsupported data(prepare) type='.$type.' for name='.$name);
                }
                ++$index;
            }
        }

        return $stmt;
    }

    /**
     * Create model and populate using the given rs and field map.
     *
     * @param string modelClass The model class.
     * @param ResultSet rs A Creole result set.
     * @param array mapping The field mapping.
     * @return mixed The model instance or array (if modelClass is <code>null</code>).
     */
    protected function rs2model($modelClass, $rs, $mapping=null) {
        $row = $rs->getRow();
        if (null === $mapping || ZM_DB_MODEL_RAW == $modelClass) {
            return $row;
        }

        // build typed data map
        $data = array();

        foreach ($mapping as $field => $info) {
            if (!array_key_exists($info['column'], $row)) {
                // field not in result set, so ignore
                continue;
            }

            switch ($info['type']) {
            case 'integer':
                $value = $rs->getInt($info['column']);
                break;
            case 'boolean':
                $value = $rs->getBoolean($info['column']);
                break;
            case 'string':
                $value = $rs->getString($info['column']);
                break;
            case 'float':
                $value = $rs->getFloat($info['column']);
                break;
            case 'datetime':
                try {
                    // TODO: creole will throw a fit as strtotime doesn't like ZM_DB_NULL_DATETIME
                    $value = $rs->getTimestamp($info['column']);
                    if (ZM_DB_NULL_DATETIME == $value) {
                        $value = null;
                    }
                } catch (SQLException $e) {
                    $value = null;
                }
                break;
            case 'date':
                try {
                    // TODO: creole will throw a fit as strtotime doesn't like ZM_DB_NULL_DATETIME
                    $value = $rs->getDate($info['column']);
                    if (ZM_DB_NULL_DATE == $value) {
                        $value = null;
                    }
                } catch (SQLException $e) {
                    $value = null;
                }
                break;
            case 'blob':
                $blob = $rs->getBlob($info['column']);
                $value = null != $blob ? $blob->getContents() : null;
                break;
            default:
                throw ZMLoader::make('ZMException', 'unsupported data(read) type='.$info['type'].' for field='.$field);
            }

            $data[$field] = $value;
        }

        // either data map or model instance
        return null == $modelClass ? $data : ZMBeanUtils::map2obj($modelClass, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData($table=null) {
        //TODO: where do we put this??
        $typeMap = array('int'=>'integer','char'=>'string','varchar'=>'string', 'tinyint'=>'integer', 'text'=>'string', 'mediumtext' => 'string', 'smallint' => 'integer', 'int unsigned' => 'integer', 'tinytext' => 'string', 'mediumblob', 'blob');
        if (null !== $table) {
            $info = $this->conn_->getDatabaseInfo();
            foreach ($info->getTables() as $tbl) {
                if ($tbl->getName() == $table) {
                    $meta = array();
                    foreach ($tbl->getColumns() as $col) {
                        $type = $col->getNativeType();
                        if (isset($typeMap[$type])) {
                            $type=$typeMap[$type];
                        } 
                        $meta[$col->getName()] = array(
                            'type' => $type,
                            'maxLen' => $col->getSize()
                        );
                    }
                }
            }
            return $meta;
        }

        return array();
    }

}

?>
