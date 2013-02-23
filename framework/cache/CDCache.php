<?php
/**
 * author: cty@20120408
 *   func: database cache class
 *   desc: 1, engine=memory,type=temporary.
 *         2, database mast be test.cache
 *         3, create tables sql cluse:
 *            create TEMPORARY table test.cache (
 *              id int not null auto_increment,
 *              expired int not null DEFAULT 0,
 *              val VARCHAR(6400) not null DEFAULT '',
 *              PRIMARY key(id),
 *              key(expired)
 *            )engine=memory CHARACTER set utf8;
 *
*/
class CDCache extends CCache {
    
    private $pdb  = null;
    
    private $dbInfo = array(
        'dsn'  => 'mysql:dbname=test;host=localhost',
        'user' => 'root',
        'pswd' => '123456');
    
    public function __construct()
    {
        $this->connect();
    }
    private function connect()
    {
        if(null === $this->pdb) {
            $this->pdb = new CPdb($this->dbInfo);
            // echo $this->pdb->getError();
            $this->createTable();
            return $this->pdb;
        }
        return $this->pdb;
    }
    
    private function createTable()
    { 
        if(!$this->isAbled()) return false;
        $sql = 'create  table if not exists test.cache (
                  id bigint not null auto_increment,
                  expired int not null DEFAULT 0,
                  val VARCHAR(6400) not null DEFAULT "",
                  PRIMARY key(id),
                  key(expired)
                )engine=memory CHARACTER set utf8';
        return $this->pdb->execute($sql);
    }
    
    public function saveValue($id, $val, $expire=1800)
    {
        $id  = $this->genIntId($id);
        $expired = time()+$expire;
        $jVal = addslashes(json_encode(array($val)));
        $sql = 'insert into test.cache values('.$id.', '.$expired.', "'.$jVal.'")';
        return $this->pdb->execute($sql);
    }
    public function loadValue($id)
    {
        if(!$this->isAbled()) return false;
        $sql  = 'delete LOW_PRIORITY from test.cache where expired>0 and expired<'.time();
        $this->pdb->execute($sql);
        
        $id   = $this->genIntId($id);
        $sql  = 'select * from test.cache where id='.$id;
        $row  = $this->pdb->getOne($sql);
        if(!$row) return false;
        $arr  = json_decode($row['val']);
        $val  = $arr[0];
        return $val;
    }
    public function removeVal($id)
    {
        if(!$this->isAbled()) return false;
        $sql = 'delete from test.cache where id='.$id;
        return $this->pdb->execute($sql);
    }
    public function removeAll($all=true)
    {
        if(!$this->isAbled()) return false;
        if($all) {
            $sql = 'delete from test.cache';
        }else {
            $sql = 'delete from test.cache where expired>0';
        }
        return $this->pdb->execute($sql);
    }
    private function genIntId($id)
    {
        return sprintf('%u', crc32($id));
    }
    private function isAbled() 
    {
        return null===$this->pdb?false:true;
    }
};
?>