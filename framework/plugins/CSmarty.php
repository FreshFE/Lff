<?php
require_once(dirname(__FILE__). '/Smarty/Smarty.class.php');

class CSmarty {
    
    private $smarty = null;
    private $tpldir = '.';
    
    public function __construct($tpl_base_dir='.')
    {
        $this->tpldir = $tpl_base_dir;
    }

    public function getSmarty()
    {
        if(null === $this->smarty) {
            $this->smarty = new Smarty($this->tpldir);
        }
        return $this->smarty;
    }

    public function assign($key, $value=null, $nocache=false)
    {
        $smarty = $this->getSmarty();
        return $smarty->assign($key, $value, $nocache);
    }
    
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        $smarty = $this->getSmarty();
        return $smarty->display($template, $cache_id, $compile_id, $parent);
    }
    
    public function setAtt($key, $val)
    {   
        $smarty = $this->getSmarty();
        $smarty->$key = $val;
        $smarty->$key = $val;
        $smarty->$key = $val;
    }
};
?>
