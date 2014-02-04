<?php 
	require_once 'gestorBD.php';
	class TypeApp {
 
	    protected $id = false;
	    protected $published = true;
	    protected $name = null;
	    protected $description = null;
	    protected $toolurl = null;
	    protected $launchinpopup = 1;
	    protected $debuglaunch = 0;
	    protected $registered = null;
	    protected $updated = null;
	    
	    public function get($property) {
	    	if (isset($this->$property)) {
	    		return $this->$property;
	    	}
	    	return null;
	    }
    

	    /**
	     * We override the constructor to fetch the extra data.
	     *
	     * @param integer
	     * @param object
	     */
	    public function __construct($id = 0) {
	        if (!empty($id)) {
	        	$this->loadDadesApp($id);
	        }
	    }
	    
	    /**
	     * 
	     * Loads data Remote App
	     * @param unknown_type $id
	     */
	    private function loadDadesApp($id) {
	    	
	    	$g = new GestorBD();
	    	$array = $g->loadRemoteApp($id);
	    	if ($array!=null) {
	    		
	    		$this->published = true;
	    		$this->id = $id;
	    		$this->name = $array['name'];
	    		$this->description = $array['description'];
	    		$this->toolurl = $array['toolurl'];
	    		$this->launchinpopup = $array['launchinpopup'];
	    		$this->debuglaunch = $array['debugmode']=="1";
	    		$this->registered = $array['registered'];
	    		$this->updated = $array['updated'];
	    		 
	    	}
	    }
    
	}
	