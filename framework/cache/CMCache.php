<?php
/**
 * author: cty@20120408
 *   func: memcache class
 *   desc: if the memcache class is not exists,then the caching will be unable.
 *
*/
class CMCache extends CCache{
    
    private $iMem = null;
    public function __construct()
    {
        $this->connect();
    }
    private function connect($host='localhost')
    {
        if(null===$this->iMem && class_exists('Memcache',false)) {
            $this->iMem = new Memcache;
            $this->iMem->connect($host, 11211, 2);
        }
    }
    
    public function saveValue($id, $val, $expire=1800)
    {
        if(!$this->isAbled()) return false;
        return $this->iMem->set($id, $val, 0, $expire);
    }
    public function loadValue($id)
    {
        if(!$this->isAbled()) return false;
        return $this->iMem->get($id);
    }
    public function removeVal($id)
    {
        if(!$this->isAbled()) return false;
        return $this->iMem->delete($id, 0);
    }
    public function removeAll($all=true)
    {
        if(!$this->isAbled()) return false;
        return $this->iMem->flush();
    }
    private function isAbled() 
    {
        return null===$this->iMem?false:true;
    }
};
?>