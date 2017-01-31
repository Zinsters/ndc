<?php
/**
 * Loads class when it needed
 *
 * @param string $className
 * @return boolean.
 */
function autoload($className) {
    $fname = MC_FULL_PATH . '/' . str_replace ( '_', '/', $className ) . '.php';
    if (file_exists($fname)) {
	   require_once ($fname);
    }
}
spl_autoload_register('autoload');