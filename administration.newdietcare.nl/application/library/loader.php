<?php
/**
 * Loads class when it needed
 *
 * @param string $className
 * @return boolean.
 */
function __autoload($className) {
	if (defined ( "DOMPDF_INC_DIR" )) {
		$fname = strtolower ( $className ) . ".cls.php";
		if (file_exists ( DOMPDF_INC_DIR . "/$fname" )) {
			require_once (DOMPDF_INC_DIR . "/$fname");
			return;
		}
	}
	
	$fname = str_replace ( '_', '/', $className ) . '.php';
	require_once ($fname);
}