<?php
//--------------------------------------------------------------------------------
// Copyright 2003 Procata, Inc.
// Released under the LGPL license (http://www.gnu.org/copyleft/lesser.html)
//--------------------------------------------------------------------------------
/**
* @package WACT_DATASOURCE
* @version $Id: datasource.face.php,v 1.1 2005/02/14 16:36:25 jeffmoore Exp $
*/

/**
* DataSource allows dynamic access to an objects properties.
*/
interface DataSource {
    /**
    * Gets a copy of a stored property by name
    * @param string name of property
    * @return mixed value of property or NULL if not found
    */
    function get($name);

    /**
    * Get a reference to an object property.
    * WARNING:  When support for PHP 4 is dropped, this method will be dropped.
    *           Do not use unless you are supporting PHP 4.
    * @param string name of property
    * @return object value of property or NULL if not found
    */
    function &getObject($name);

    /**
    * Gets a property value by navagating a dot separated path
    * that dereferences elements within the DataSource.
    * If an element cannot be dereferenced or is not set, the
    * value NULL is returned.
    * @param string name of property
    * @return mixed value of property or NULL if not found
    */
    function getPath($path);

    /**
    * Gets an object property value by navagating a dot separated path
    * that dereferences elements within the DataSource.
    * If an element cannot be dereferenced or is not set, the
    * value NULL is returned.
    * WARNING:  When support for PHP 4 is dropped, this method will be dropped.
    *           Do not use unless you are supporting PHP 4.
    * @param string name of property
    * @return object value of property or NULL if not found
    */
    function &getObjectPath($path);
    
    /**
    * Stores a copy of value into a Property
    * @param string name of property
    * @param mixed value of property
    * @return void
    */
    function set($name, $value);

    /**
    * Stores an object reference into a Property
    * WARNING:  When support for PHP 4 is dropped, this method will be dropped.
    *           Do not use unless you are supporting PHP 4.
    * @param string name of property
    * @param object value of property
    * @return void
    */
    function setObject($name, &$obj);

    /**
    * Stores a copy of value into a Property based on a dot separated
    * path.
    * If an element cannot be dereferenced, or is not set, it is 
    * converted to an array.
    * @param string name of property
    * @param mixed value of property
    * @return void
    */
    function setPath($path, $value);

    function setObjectPath($path, &$obj);

    /**
    * removes stored property value
    * Triggers an error if this DataSource does not support Dynamic Properties
    * @param string name of property
    * @return void
    */
    function remove($name);
    
    /**
    * removes all property values
    * Triggers an error if this DataSource does not support Dynamic Properties
    *
    * @return void
    */
    function removeAll();

    /**
    * replaces the current properties of this DataSource with the properties and
    * values
    * passed as a parameter
    * @param array
    * @return void
    */
    function import($property_list);
    
    /**
    * Append a new set of properties and their values. Existing key values will
    * be overwritten if duplicated in the new value list.
    *
    * This is equivelent to calling set with each name value pair.
    *
    * @param array a list of property names and the values to set them to
    * @return void
    */
    function merge($property_list);

    /**
    * Returns an associative array of all readable property names and 
    * corresponding values.
    * @return array
    */
    function export();

    /**
    * Any class that implements the DataSource interface should implement this
    * method This is a PHP4 way of detecting which objects implement the 
    * interface.
    * @return Boolean always TRUE
    */
    function isDataSource();
    
    /**
    * Returns true if this DataSource allows arbitrary properies to be 
    * dynamically
    * created.
    * @return Boolean
    */
    function hasDynamicPropertySupport();

    /**
    * Has a value been assigned under this name for this DataSource?
    * @param string name of property
    * @return boolean TRUE if property exists
    */
    function hasProperty($name);
    
    /**
    * Return a unique list of readable property names
    * This method is probably going to have capitalization problems.
    * @return array
    */
    function getPropertyList();
    
    /**
    * Returns an object describing the qualities of the named property.
    * @return object
    */
    function getPropertyDescriptor($name);
    
}

?>