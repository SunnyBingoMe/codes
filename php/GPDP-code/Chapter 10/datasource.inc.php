<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_DATASOURCE
* @version $Id: datasource.inc.php,v 1.1 2005/03/05 21:47:05 jeffmoore Exp $
*/

/**
* DataSourceSupport is a base class designed to assist in implementing the
* DataSource interface.  It does not declare that it implements DataSource
* to retain compatibility with PHP 4.  Sub classes may choose to explicitly
* declare the interface.
* @see http://wact.sourceforge.net/index.php/DataSource
* @access public
* @abstract
*/
class DataSourceSupport {

    /**
    * Properties stored in the DataSource are contained here
    * @var array
    * @access private
    */
    var $_properties = array();

    /**
    * PHP 4 constructor.  automatically calls the PHP 5 constructor
    */
    function DataSourceSupport() {
        $this->__construct();
    }

    /**
    * PHP 5 constructor
    */
    function __construct() {
    }

    /**
    * Allows subclasses to perform a specific action in cases where a property
    * is not defined.
    * @param string name of property
    * @return mixed value of property
    * @access protected
    */
    function &propertyNotFound($name) {
        return NULL;
    }

    /**
    * Called whenever a property value is changed.
    * @param string name of property
    * @access protected
    */
    function propertyChanged($name) {
        return TRUE;
    }

    /**
    * Called whenever a property value is changed.
    * @param string name of property
    * @access protected
    */
    function propertyChange($name, &$old, &$new) {
        return TRUE;
    }

    /**
    * @see DataSource.get
    */
    function get($name) {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        } else {
            return $this->propertyNotFound($name);
        }
    }

    /**
    * @see DataSource.getObject
    */
    function &getObject($name) {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        } else {
            $this->propertyNotFound($name);
        }
    }

    /**
    * @see DataSource.getPath
    */
    function getPath($path) {
        return $this->_getPath($path, 'get', 'getPath');
    }

    /**
    */
    function &_getPath($path, $method, $methodPath) {
        if (($pos = strpos($path, '.')) === FALSE) {
            return $this->$method($path);
        }
        $key = substr($path, 0, $pos);
        $rest = substr($path, $pos + 1);
        $var =& $this->$method($key);
        $pos = strpos($rest, '.');
        while ($pos !== FALSE) {
            $key = substr($rest, 0, $pos);
            if (is_object($var)) {
                if (method_exists($var, 'isDataSource')) {
                    return $var->$methodPath($rest);
                } else {
                    $var =& $var->$key;
                }
            } else if (is_array($var)) {
                $var =& $var[$key];
            } else {
                return $this->propertyNotFound($path);
            }
            $rest = substr($rest, $pos + 1);
            $pos = strpos($rest, '.');
        }
        $key = $rest;
        if (is_object($var)) {
            if (method_exists($var, 'isDataSource')) {
                return $var->$methodPath($key);
            } else {
                return $var->$key;
            }
        } else if (is_array($var)) {
            return $var[$key];
        } else {
            $this->propertyNotFound($path);
        }

        return $var;
    }

    /**
    * @see DataSource.getObjectPath
    */
    function &getObjectPath($path) {
        return $this->_getPath($path, 'getObject', 'getObjectPath');
    }

    /**
    * @see DataSource.set
    */
    function set($name, $value) {
        if (isset($this->_properties[$name])) {
            if ($this->propertyChange($name, $this->_properties[$name], $value)) {
                $this->_properties[$name] = $value;
            }
        } else {
            $this->addProperty($name, $value);
        }
    }

    /**
    * @see DataSource.SetObject
    */
    function setObject($name, &$value) {
        if (isset($this->_properties[$name])) {
            if ($this->propertyChange($name, $this->_properties[$name], $value)) {
                $this->_properties[$name] =& $value;
            }
        } else {
            $this->addObjectProperty($name, $value);
        }
    }

    /**
    * @see DataSource.setPath
    */
    function setPath($path, $value) {
        if (($pos = strpos($path, '.')) === FALSE) {
            return $this->set($path, $value);
        }
        $var =& $this->_getPathReference($pos, $path, TRUE); // can modify $path
        if (is_object($var)) {
           if (method_exists($var, 'isDataSource')) {
                $var->setPath($path, $value);
           } else {
                $var->$path = $value;
                $this->propertyChanged($path);   // Not exactly correct
           }
        } else if (is_array($var)) {
            $var[$path] = $value;
            $this->propertyChanged($path);  // Not exactly correct
        } else {
            $var = array();
            $var[$path] = $value;
            $this->propertyChanged($path);  // Not exactly correct
        }
    }

    /**
    * @see DataSource.setObjectPath
    */
    function setObjectPath($path, &$value) {
        if (($pos = strpos($path, '.')) === FALSE) {
            return $this->setObject($path, &$value);
        }
        $var =& $this->_getPathReference($pos, $path, TRUE); // can modify $path
        if (is_object($var)) {
           if (method_exists($var, 'isDataSource')) {
                $var->setObjectPath($path, $value);
           } else {
                $var->$path =& $value;
                $this->propertyChanged($path);  // Not exactly correct
           }
        } else if (is_array($var)) {
            $var[$path] =& $value;
            $this->propertyChanged($path);  // Not exactly correct
        } else {
            $var = array();
            $var[$path] =& $value;
            $this->propertyChanged($path);  // Not exactly correct
        }
    }

    /**
    * @see DataSource.removeProperty
    */
    function removeProperty($name) {
        unset($this->_properties[$name]);
        $this->propertyChanged($name);
    }
    
    /**
    * @see DataSource.removeAllProperties
    */
    function removeAllProperties() {
        // This implementation bypasses the propertyChanged calls
        $this->_properties = array();
    }

    /**
    * @see DataSource.addProperty
    */
    function addProperty($name, $value) {
        $this->_properties[$name] = $value;
    }
    
    /**
    * @see DataSource.addObjectProperty
    */
    function addObjectProperty($name, &$value) {
        $this->_properties[$name] =& $value;
    }

    /**
    * @see DataSource.import
    */
    function import($property_list) {
        $this->_properties = $property_list;
        // This implementation bypasses the propertyChanged calls
    }

    /**
    * @see DataSource.setMany
    */
    function setMany($list) {
        if (is_array($list)) {
            foreach ($list as $name => $value) {
                $this->set($name, $value);
            }
        } else if (is_a($list, 'Iterator')) {
            for ($list->reset(); $list->valid(); $list->next()) {
                $this->set($list->key(), $list->current());
            }
        }
    }

    /**
    * @see DataSource.export
    */
    function export() {
        return $this->_properties;
    }

    /**
    * @see DataSource.isDataSource
    */
    function isDataSource() {
        return TRUE;
    }

    /**
    * @see DataSource.hasProperty
    */
    function hasProperty($name) {
        return isset($this->_properties[$name]);
    }
    
    /**
    * @see DataSource.getPropertyList
    */
    function getPropertyList() {
        return array_keys($this->_properties);
    }

    /**
    * @return array
    * @access protected
    */
    function &_getPathReference($pos, &$path, $shouldExtendPath = FALSE) {
        $var =& $this->_properties;
        do {
            $key = substr($path, 0, $pos);
            if (is_object($var)) {
                if (method_exists($var, 'isDataSource')) {
                    return $var;
                } else {
                    $var =& $var->$key;
                }
            } else if (is_array($var)) {
                $var =& $var[$key];
            } else {
                if ($shouldExtendPath) {
                    $var = array();
                    $var =& $var[$key];
                } else {
                    return NULL;  // Can't de-reference
                }
            }
            $path = substr($path, $pos + 1);
            $pos = strpos($path, '.');
        } while ($pos !== FALSE);
        return $var;
    }
    
}

class EmptyDataSource extends DataSourceSupport {
}

class ArrayDataSource extends DataSourceSupport {

    function ArrayDataSource($props = NULL) {
        $this->__construct($props);
    }

    function __construct($props = NULL) {
        if (is_array($props)) {
            $this->_properties = $props;
        }      
    }

}

?>