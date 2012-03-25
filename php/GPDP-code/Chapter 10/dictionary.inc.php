<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_UTIL
* @version $Id: dictionary.inc.php,v 1.1 2005/02/25 18:30:13 jeffmoore Exp $
*/

/**
* The DictionaryDataSource is a container for a set of named data values (properties).
* @see http://wact.sourceforge.net/index.php/DataSpace
* @access public
* @abstract
* @package WACT
*/
class DictionaryDataSource {
    /**
    * Properties stored in the DataSpace are contained here
    * @var array
    * @access private
    */
    var $_properties = array();

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
        // Allow change vetos?
    }

    /**
    * Gets a copy of a stored property by name
    * @param string name of property
    * @return mixed value of property or NULL if not found
    * @access public
    */
    function get($name) {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        } else {
            return $this->propertyNotFound($name);
        }
    }

    /**
    * Get a reference to an object property.
    * WARNING:  When support for PHP 4 is dropped, this method will be dropped.
    *           Do not use unless you are supporting PHP 4.
    * @param string name of property
    * @return mixed value of property or NULL if not found
    * @access public
    */
    function &getObject($name) {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        } else {
            $this->propertyNotFound($name);
        }
    }

    /**
    * Gets a property value by navagating a dot separated path
    * that dereferences elements within the dataspace.
    * If an element cannot be dereferenced or is not set, the
    * value NULL is returned.
    * @param string name of property
    * @return mixed value of property or NULL if not found
    * @access public
    */
    function getPath($path) {
        if (($pos = strpos($path, '.')) === FALSE) {
            return $this->get($path);
        }
        $var =& $this->_getPathReference($pos, $path); // can modify $path
        if (is_object($var)) {
           if (method_exists($var, 'isDataSource')) {
                return $var->getPath($path);
           } else {
                return $var->$path;
           }
        } else if (is_array($var)) {
            return $var[$path];
        } else {
            $this->propertyNotFound($path);  // Not yet correct.
        }
    }

    /**
    * Get a reference to an object property.
    * WARNING:  When support for PHP 4 is dropped, this method will be dropped.
    *           Do not use unless you are supporting PHP 4.
    * @param string name of property
    * @return mixed value of property or NULL if not found
    * @access public
    */
    function &getObjectPath($path) {
        if (($pos = strpos($path, '.')) === FALSE) {
            return $this->getObject($path);
        }
        $var =& $this->_getPathReference($pos, $path); // can modify $path
        if (is_object($var)) {
            if (method_exists($var, 'isDataSource')) {
                return $var->getObjectPath($path);
            } else {
                return $var->$path;
            }
        } else if (is_array($var)) {
            return $var[$path];
        } else {
            $this->propertyNotFound($path); // Not yet correct
        }
    }

    /**
    * Stores a copy of value into a Property
    * @param string name of property
    * @param mixed value of property
    * @return void
    * @access public
    */
    function set($name, $value) {
        $this->_properties[$name] = $value;
        $this->propertyChanged($name);
    }

    /**
    * Stores an object reference into a Property
    * WARNING:  When support for PHP 4 is dropped, this method will be dropped.
    *           Do not use unless you are supporting PHP 4.
    * @param string name of property
    * @param object value of property
    * @return void
    * @access public
    */
    function setObject($name, &$value) {
        $this->_properties[$name] =& $value;
        $this->propertyChanged($name);
    }

    /**
    * Stores a copy of value into a Property based on a dot separated
    * path.
    * If an element cannot be dereferenced, or is not set, it is 
    * converted to an array.
    * @param string name of property
    * @param mixed value of property
    * @return void
    * @access public
    */
    function setPath($path, $value) {
        if (($pos = strpos($path, '.')) === FALSE) {
            // die('Do not remove this line until a test case has been written which executes it');
            return $this->set($path, $value);
        }
        $var =& $this->_getPathReference($pos, $path, TRUE); // can modify $path
        if (is_object($var)) {
           if (method_exists($var, 'isDataSource')) {
                $var->setPath($path, $value);
           } else {
                // die('Do not remove this line until a test case has been written which executes it');
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
    * Stores a copy of value into a Property based on a dot separated
    * path.
    * If an element cannot be dereferenced, or is not set, it is 
    * converted to an array.
    * @param string name of property
    * @param mixed value of property
    * @return void
    * @access public
    */
    function setObjectPath($path, &$value) {
        // die('Do not remove this line until a test case has been written which executes it');
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
    * Reset property to its default value
    * @param string name of property
    * @return void
    * @access public
    */
    function clear($name) {
        unset($this->_properties[$name]);
        $this->propertyChanged($name);
    }
    
    /**
    * removes all property values to their default values
    * @return void
    * @access public
    */
    function clearAll() {
        // This implementation bypasses the propertyChanged calls
        $this->_properties = array();
    }

    /**
    * replaces the current properties of this dataspace with the proprties and values
    * passed as a parameter
    * @param array
    * @return void
    * @access public
    */
    function import($property_list) {
        $this->_properties = $property_list;
        // This implementation bypasses the propertyChanged calls
    }

    /**
    * Append a new list of values to the DataSpace. Existing key values will be
    * overwritten if duplicated in the new value list.
    *
    * @param array a list of property names and the values to set them to
    * @return void
    * @access public
    */
    function merge($list) {
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
    * Returns a reference to the complete array of properties stored
    * @return array
    * @access public
    */
    function export() {
        return $this->_properties;
    }

    /**
    * Any class that implements the DataSource interface should implement this method
    * This is a PHP4 way of detecting which objects implement the interface.
    * @return Boolean always TRUE
    * @access public
    */
    function isDataSource() {
        return TRUE;
    }

    /**
    * Has a value been assigned under this name for this dataspace?
    * @param string name of property
    * @return boolean TRUE if property exists
    * @access public
    */
    function hasProperty($name) {
        return isset($this->_properties[$name]);
    }
    
    /**
    * Return a unique list of available properties
    * This method is probably going to have capitalization problems.
    * @return array
    * @access public
    */
    function getPropertyList() {
        return array_keys($this->_properties);
    }

    /**
    * 
    * @return array
    * @access protected
    */
    function &_getPathReference($pos, &$path, $shouldExtendPath = FALSE) {
        $var =& $this->_properties;
        do {
            $key = substr($path, 0, $pos);
            if (is_object($var)) {
                // die('Do not remove this line until a test case has been written which executes it');
                if (method_exists($var, 'isDataSource')) {
                    // die('Do not remove this line until a test case has been written which executes it');
                    return $var;
                } else {
                    // die('Do not remove this line until a test case has been written which executes it');
                    $var =& $var->$key;
                }
            } else if (is_array($var)) {
                $var =& $var[$key];
            } else {
                // die('Do not remove this line until a test case has been written which executes it');
                if ($shouldExtendPath) {
                    // die('Do not remove this line until a test case has been written which executes it');
                    $var = array();
                    $var =& $var[$key];
                } else {
                    // die('Do not remove this line until a test case has been written which executes it');
                    return NULL;  // Can't de-reference
                }
            }
            $path = substr($path, $pos + 1);
            $pos = strpos($path, '.');
        } while ($pos !== FALSE);
        return $var;
    }
    
}

?>