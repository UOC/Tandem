<?php 
	require_once 'gestorBD.php';
	class TypeLTI {
 
	    protected $id = false;
	    protected $published = true;
	    protected $name = null;
	    protected $description = null;
	    protected $toolurl = null;
	    protected $resourcekey = null;
	    protected $password = null;
	    protected $preferheight = 0;
	    protected $sendname = false;
	    protected $instructorchoicesendname = false;
	    protected $sendemailaddr = false;
	    protected $instructorchoicesendemailaddr = false;
	    protected $allowroster = false;
	    protected $instructorchoiceallowroster = false;
	    protected $allowsetting = false;
	    protected $instructorchoiceallowsetting = false;
	    protected $acceptgrades = false;
	    protected $instructorchoiceacceptgrades = false;
	    protected $customparameters = null;
	    protected $organizationid = null;
	    protected $organizationurl = null;
	    protected $organizationdescr = null;
	    protected $launchinpopup = 1;
	    protected $debuglaunch = 0;
	    protected $registered = null;
	    protected $update = null;
	    
	    public function get($property) {
	    	if (isset($this->$property)) {
	    		return $this->$property;
	    	}
	    	return null;
	    }
    
		/**
		 * Resolves toolurl's dinamic content
		 * @param array where the keys are the content to replace and the values are the new content
		 */
		public function resolve_dinamic_toolurl($translations = array()) {
			foreach ($translations as $key => $value) {
				$this->toolurl = str_replace($key, $value, $this->toolurl);
			}
		}
	    /**
	     * We override the constructor to fetch the extra data.
	     *
	     * @param integer
	     * @param object
	     */
	    public function __construct($id = 0) {
	        if (!empty($id)) {
	        	$this->loadDadesLTI($id);
	        }
	    }
	    
	    /**
	     * 
	     * Loads data LTI
	     * @param unknown_type $id
	     */
	    private function loadDadesLTI($id) {
	    	
	    	$g = new GestorBD();
	    	$array = $g->loadDadesLTI($id);
	    	if ($array!=null) {
	    		$this->published = true;
	    		$this->id = $id;
	    		$this->name = $array['name'];
	    		$this->description = $array['description'];
	    		$this->toolurl = $array['toolurl'];
	    		$this->resourcekey = $array['resourcekey'];
	    		$this->password = $array['password'];
	    		$this->preferheight = $array['preferheight'];
	    		$this->sendname = $array['sendname'];
	    		//$this->instructorchoicesendname = $array['instructorchoicesendname'];
	    		$this->sendemailaddr = $array['sendemailaddr'];
	    		//$this->instructorchoicesendemailaddr = $array['instructorchoicesendemailaddr'];
	    		$this->allowroster = $array['allowroster'];
	    		//$this->instructorchoiceallowroster = $array['instructorchoiceallowroster'];
	    		$this->allowsetting = $array['allowsetting'];
	    		//$this->instructorchoiceallowsetting = $array['instructorchoiceallowsetting'];
	    		$this->acceptgrades = $array['acceptgrades'];
	    		//$this->instructorchoiceacceptgrades = $array['instructorchoiceacceptgrades'];
	    		$this->customparameters = $array['customparameters'];
	    		$this->organizationid = $array['organizationid'];
	    		$this->organizationurl = $array['organizationurl'];
	    		$this->organizationdescr = isset($array['organizationdescr'])?$array['organizationdescr']:'';
	    		$this->launchinpopup = $array['launchinpopup'];
	    		$this->debuglaunch = $array['debugmode']=="1";
	    		$this->registered = $array['registered'];
	    		$this->updated = $array['updated'];
	    		 
	    	}
	    }
    
	}
	