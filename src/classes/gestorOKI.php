<?php 

require_once 'constants.php';
require_once('gestorBD.php');
require_once('utils.php');

$required_class = 'org/osid/shared/SharedException.php';
$exists= filexists_tandem ($required_class);
if ($exists) {

	try{
		require_once $required_class;
		require_once "org/campusproject/components/AuthenticationComponent.php";
		require_once "org/campusproject/components/IdComponent.php";
		require_once "org/campusproject/components/AgentComponent.php";
	} catch (Exception $e){
		//Problema en carregar els components 
//echo "<pre>";		var_dump($e);echo "</pre>";
	}
}
class GestorOKI
{
	private $_last_error = null;
	
	/**
	 * 
	 * Obte el darrer error
	 */
	public function getLastError() {
		return $this->_last_error;
	}
	
	
	/**
	*
	* Obte el llistat d'usuaris
	* @param unknown_type $gestorBD
	* @param unknown_type $course_id
	*/
	function obte_llistat_usuaris($gestorBD, $course_id) {
		//Loads users
		$users = false;
		try {
			
			if (class_exists("AuthenticationComponent")) {
				$authN =new AuthenticationComponent();
				if ($authN->isUserAuthenticated()) {
					$users = array();
					$agent = new AgentComponent($osidContext);
					$group = $agent->getInstanceContainerGroup();
					
					for($roles = $group->getMembers(false); $roles->hasNextAgent();) {
						$sa = $roles->nextAgent();
						if (AgentComponent::isStudent($sa) || AgentComponent::isTeacher($sa) /*|| AgentComponent::isAdmin($sa)*/) {
								
							for($members = $sa->getMembers(false); $members->hasNextAgent();) {
								$member = $members->nextAgent();
								$current_name = $member->getDisplayName();
								
								$current_fullname = '';
								$current_surname = '';
								$current_email = '';
								$current_image = '';
								$properties = $member->getPropertiesByType($agent->getUserPropertiesType());
								$keys_iterator = $properties->getKeys();
								while ($keys_iterator->hasNextObject()) {
									$key = $keys_iterator->nextObject();
									$value = $properties->getProperty($key);
									//$value = mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
									if ($key==FULLNAMEOKI) {
										$current_fullname = $value;
									} elseif ($key==FIRSTNAMEOKI) {
										$current_name = $value;
									} elseif ($key==SURNAMEOKI) {
										$current_surname = $value;
									} elseif ($key==EMAILOKI) {
										$current_email = $value;
									} elseif ($key==IMAGEOKI) {
										$current_image = $value;
									}
								}
								$gestorBD->afegeixUsuari($course_id, $member->getDisplayName(), $current_name, $current_surname, $current_fullname, $current_email, $current_image);
								$users[$member->getDisplayName()] = $gestorBD->get_user_by_username($member->getDisplayName());
								
							}
						}
					}
				}
			}
			$users = $gestorBD->obte_llistat_usuaris($course_id);
		} catch (Exception $e) {
			//$this->_last_error = $e->getMessage();
			//var_dump($e);
		}
		return $users;
	}

}
?>
