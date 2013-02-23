<?php
/**
 * author: cty@20120408
 *   func: CCache abstract class
 *
*/
abstract class CCache {
    abstract public function save($id, $val, $expire=1800);
    abstract public function load($id);
    abstract public function rm($id);
    abstract public function rmAll($all=true);
};
