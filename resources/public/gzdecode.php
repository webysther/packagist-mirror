<?php
/**
 * User: mr.jin
 * Date: 18/8/11
 * Time: 下午9:14
 * Description: 用于全量补充
 */

	$uri = $_SERVER['REQUEST_URI'];
	$data = file_get_contents('https://repo.packagist.org' . $uri);
	print_r($data);