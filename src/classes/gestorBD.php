<?php

// Include the SDK
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/TandemBadges.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../config.inc.php';

class GestorBD {
	private $conn;
	private $_debug = false;
	private $use_deprecated = false;

	/**
	 * GestorBD constructor.
	 *
	 * @param bool $use_deprecated
	 */
	public function __construct( $use_deprecated = false ) {
		$this->conectar();
		$this->use_deprecated = $use_deprecated;
	}

	/**
	 * @return bool
	 */
	public function conectar() {
		if ( $this->use_deprecated ) {
			/** @noinspection PhpDeprecationInspection */
			$this->conn = mysql_connect( BD_HOST, BD_USERNAME, BD_PASSWORD );
			if ( ! $this->conn ) {
				/** @noinspection PhpDeprecationInspection */
				die( 'No se pudo conectar: ' . mysql_error() );
			}
			/** @noinspection PhpDeprecationInspection */
			mysql_select_db( BD_NAME, $this->conn );
		} else {
			$this->conn = mysqli_connect( BD_HOST, BD_USERNAME, BD_PASSWORD, BD_NAME );
			if ( ! $this->conn ) {
				die( 'No se pudo conectar: ' . mysqli_error( $this->conn ) );
			}
		}

		return true;
	}

	public function enableDebug() {
		$this->_debug = true;
	}

	public function disableDebug() {
		$this->_debug = false;
	}

	/**
	 * @param $msg
	 */
	public function debugMessage( $msg ) {
		if ( $this->_debug ) {
			echo "<p>DEBUG: " . $msg . "</p>";
		}
	}

	public function desconectar() {
		if ( $this->use_deprecated ) {
			mysql_close( $this->conn );
		} else {
			mysqli_close( $this->conn );
		}
	}

	/**
	 * @param $str
	 *
	 * @return string
	 */
	public function escapeString( $str ) {
		if ( $str === null ) {
			return "'null'";
		}
		if ( is_array( $str ) ) {
			/** @noinspection ForgottenDebugOutputInspection */
			error_log( "Array sent " . serialize( $str ) );
			/** @noinspection ForgottenDebugOutputInspection */
			error_log( "debug " . serialize( debug_backtrace() ) );
		}
		if ( $this->use_deprecated ) {
			return "'" . mysql_real_escape_string( $str, $this->conn ) . "'";
		} else {
			return "'" . mysqli_real_escape_string( $this->conn, $str ) . "'";
		}
	}

	/**
	 * @param $query
	 *
	 * @return bool|mysqli_result|string
	 */
	public function consulta( $query ) //CUIDAOOOOOOOOO! SQL Injection !!
	{
		$this->debugMessage( $query );
		if ( $this->use_deprecated ) {
			$result = mysql_query( $query, $this->conn );
		} else {
			$result = mysqli_query( $this->conn, $query );
		}
		if ( ! $result ) {
			if ( $this->use_deprecated ) {
				error_log( "<!--Error BD " . mysql_error() );
			} else {
				error_log( "<!--Error BD " . mysqli_error( $this->conn ) );
			}
			error_log( "Query:" . $query . "-->" );
		}

		return $result;
	}

	/**
	 * @param $result
	 *
	 * @return array|null
	 */
	private function obteObjecteComArray( $result ) {
		if ( $this->use_deprecated ) {
			return mysql_fetch_assoc( $result );
		} else {
			return mysqli_fetch_assoc( $result );
		}
	}

	/**
	 * @param $result
	 *
	 * @return null|stdClass
	 */
	private function obteObjecte( $result ) {
		if ( $this->use_deprecated ) {
			return mysql_fetch_object( $result );
		} else {
			return mysqli_fetch_object( $result );
		}
	}

	/**
	 * @return int|string
	 */
	private function getLastInsertedId() {
		if ( $this->use_deprecated ) {
			return mysql_insert_id( $this->conn );
		} else {
			return mysqli_insert_id( $this->conn );
		}
	}

	/**
	 * @param $result
	 *
	 * @return int
	 */
	public function numResultats( $result ) {
		if ( $this->use_deprecated ) {
			return mysql_num_rows( $result );
		} else {
			return mysqli_num_rows( $result );
		}
	}

	/**
	 * @return int
	 */
	private function getAffectedRows() {
		if ( $this->use_deprecated ) {
			return mysql_affected_rows( $this->conn );
		} else {
			return mysqli_affected_rows( $this->conn );
		}
	}

	/**
	 * @param $result
	 *
	 * @return array
	 */
	public function obteComArray( $result ) {
		$rows = array();
		if ( $this->use_deprecated ) {
			while ( $row = mysql_fetch_assoc( $result ) ) {
				$rows[] = $row;
			}
		} else {
			while ( $row = mysqli_fetch_assoc( $result ) ) {
				$rows[] = $row;
			}
		}

		return $rows;
	}

	/**
	 * @param $result
	 *
	 * @return array|null
	 */
	private function get_mysql_fetch_array( $result ) {
		$rows = array();
		if ( $this->use_deprecated ) {
			$rows = mysql_fetch_array( $result );
		} else {
			$rows = mysqli_fetch_array( $result );
		}

		return $rows;
	}

	/**
	 *
	 * Obte si existeix l'usuari
	 *
	 * @param string $userKey
	 *
	 * @return array|boolean
	 */
	public function get_user_by_username( $userKey ) {
		$row    = false;
		$result = $this->consulta( "SELECT * FROM user where username = " . $this->escapeString( $userKey ) );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );
		}

		return $row;
	}

	/**
	 * Register user in database
	 *
	 * @param String $username
	 * @param String $name
	 * @param String $surname
	 * @param String $fullname
	 * @param String $email
	 * @param String $image
	 * @param String $icq
	 * @param String $skype
	 * @param String $yahoo
	 * @param String $msn
	 * @param string $user_agent
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function register_user(
		$username,
		$name,
		$surname,
		$fullname,
		$email,
		$image,
		$icq = '',
		$skype = '',
		$yahoo = '',
		$msn = '',
		$user_agent = ''
	) {
		$result = false;
		$sql    = 'INSERT INTO user (username, firstname, surname, fullname, email, image, icq, skype, yahoo, msn, last_user_agent, last_session,  blocked, created)
                												VALUES (' . $this->escapeString( $username ) . ',' . $this->escapeString( $name ) . ',' . $this->escapeString( $surname ) . ',' . $this->escapeString( $fullname ) . ',' . $this->escapeString( $email ) . ',' .
		          $this->escapeString( $image ) . ',' . $this->escapeString( $icq ) . ', ' . $this->escapeString( $skype ) . ', ' . $this->escapeString( $yahoo ) . ', ' . $this->escapeString( $msn ) . ',' .
		          $this->escapeString( $user_agent ) . ',' .
		          ' now(), 0, now())';
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 *
	 * Updates user data
	 *
	 * @param String $username
	 * @param String $name
	 * @param String $surname
	 * @param String $fullname
	 * @param String $email
	 * @param String $image
	 * @param String $icq
	 * @param String $skype
	 * @param String $yahoo
	 * @param String $msn
	 * @param $user_agent
	 * @param boolean $update_profile
	 *
	 * @return bool|mysqli_result|resource|string
	 */
	public function update_user(
		$username,
		$name,
		$surname,
		$fullname,
		$email,
		$image = '',
		$icq = '',
		$skype = '',
		$yahoo = '',
		$msn = '',
		$user_agent,
		$update_profile = true
	) {
		$result = false;
		$sql    = 'UPDATE user SET firstname = ' . $this->escapeString( $name ) . ', surname = ' . $this->escapeString( $surname ) . ', fullname = ' . $this->escapeString( $fullname ) . ', email = ' . $this->escapeString( $email ) . ', last_session=now(), image = ' . $this->escapeString( $image ) . ', last_user_agent = ' . $this->escapeString( $user_agent );

		if ( $update_profile ) {
			$sql .= ',icq = ' . $this->escapeString( $icq ) . ', skype = ' . $this->escapeString( $skype ) . ', yahoo = ' . $this->escapeString( $yahoo ) . ', msn = ' . $this->escapeString( $msn );
		}

		$sql    .= ' WHERE username = ' . $this->escapeString( $username );
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Obte si existeix el curs
	 *
	 * @param string $id
	 *
	 * @return array|boolean
	 */
	public function get_course_by_id( $id ) {
		$result = $this->consulta( "SELECT * FROM course where id = " . $this->escapeString( $id ) );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return $row;
		} else {
			return false;
		}
	}

	/**
	 * Obte si existeix el curs
	 *
	 * @param string $courseKey
	 *
	 * @return array|boolean
	 */
	public function get_course_by_courseKey( $courseKey ) {
		$result = $this->consulta( "SELECT * FROM course where courseKey = " . $this->escapeString( $courseKey ) );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return $row;
		} else {
			return false;
		}
	}

	/**
	 * Obte si usuari está comunicant
	 *
	 * @param string $user
	 * @param string $id_course
	 *
	 * @return int
	 */
	public function get_userInTandem( $user, $id_course ) {

		$result = $this->consulta( "SELECT * FROM user_course WHERE id_user=" . $this->escapeString( $user ) . " AND id_course=" . $this->escapeString( $id_course ) );
		$row    = $this->obteObjecte( $result );

		return $row->inTandem;
	}

	/**
	 * Set si usuari está comunicant
	 *
	 * @param string $user
	 * @param string $id_course
	 * @param string $status
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function set_userInTandem( $user, $id_course, $status ) {
		$result = $this->consulta( "UPDATE user_course SET inTandem = " . $status . " WHERE id_user = " . $this->escapeString( $user ) . " AND id_course=" . $this->escapeString( $id_course ) );

		return $result;
	}

	/**
	 * Set date des de que usuari está comunicant
	 *
	 * @param string $user
	 * @param string $id_course
	 * @param string $date
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function set_userLastAccess( $user, $id_course, $date ) {
		$result = $this->consulta( "UPDATE user_course SET lastAccessTandem = " . $this->escapeString( $date ) . " WHERE id_user = " . $this->escapeString( $user ) . " AND id_course=" . $this->escapeString( $id_course ) );

		return $result;
	}

	/**
	 * Get date des de que usuari está comunicant
	 *
	 * @param string $user
	 * @param string $id_course
	 *
	 * @return string
	 */
	public function get_lastAccessTandemStatus( $user, $id_course ) {
		$result = $this->consulta( "SELECT * FROM user_course WHERE id_user=" . $this->escapeString( $user ) . " AND id_course=" . $this->escapeString( $id_course ) );
		$row    = $this->obteObjecte( $result );

		return $row->lastAccessTandem;
	}

	/**
	 * Register courseKey in database
	 *
	 * @param string $courseKey
	 * @param string $title
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function register_course( $courseKey, $title ) {
		$result = false;
		$sql    = 'INSERT INTO course (courseKey, title, created)
                				VALUES (' . $this->escapeString( $courseKey ) . ',' . $this->escapeString( $title ) . ', now())';
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Updates course data
	 *
	 * @param string $courseKey
	 * @param string $title
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function update_course( $courseKey, $title ) {
		$result = false;
		$sql    = 'UPDATE course SET title = ' . $this->escapeString( $title ) . ' ' .
		          'WHERE courseKey = ' . $this->escapeString( $courseKey );
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Obte el rol en la bd
	 *
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return boolean
	 */
	public function obte_rol( $course_id, $user_id ) {
		$result = $this->consulta( "SELECT * FROM user_course where id_user = " . $user_id . " AND id_course = " . $course_id );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return $row;
		} else {
			return false;
		}
	}

	/**
	 * Afegeix l'usuari al curs si no esta
	 *
	 * @param string $course_id
	 * @param string $user_id
	 * @param string $isInstructor
	 * @param string $lis_result_sourceid
	 * @param $language
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function join_course( $course_id, $user_id, $isInstructor, $lis_result_sourceid, $language ) {
		$result = false;
		if ( ! $this->obte_rol( $course_id, $user_id ) ) {
			$sql = 'INSERT INTO user_course (id_user, id_course, is_instructor, lis_result_sourceid, lastAccessTandem, language)
                												VALUES (' . $this->escapeString( $user_id ) . ',' . $this->escapeString( $course_id ) . ',' . ( $isInstructor ? 1 : 0 ) . ', ' . $this->escapeString( $lis_result_sourceid ) . ', ' . $this->escapeString( date( 'Y-m-d H:i:s' ) ) . ', ' . $this->escapeString( $language ) . ')';
		} else {
			$sql = 'UPDATE user_course SET is_instructor =' . ( $isInstructor ? 1 : 0 ) . ',lis_result_sourceid=' . $this->escapeString( $lis_result_sourceid ) . ', language = ' . $this->escapeString( $language ) . ', lastAccessTandem = ' . $this->escapeString( date( 'Y-m-d H:i:s' ) ) . ' where id_user = ' . $user_id . ' AND id_course = ' . $course_id;
		}
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Afegeix l'usuari al curs si no esta
	 *
	 * @param string $course_id
	 * @param string $username
	 * @param $name
	 * @param $surname
	 * @param $fullname
	 * @param $email
	 * @param $image
	 * @param null $lis_result_sourceid
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function afegeixUsuari(
		$course_id,
		$username,
		$name,
		$surname,
		$fullname,
		$email,
		$image,
		$lis_result_sourceid = null
	) {
		$isInstructor = 0;
		$result       = false;
		$user         = $this->get_user_by_username( $username );
		if ( ! $user ) {
			if ( $this->register_user( $username, $name, $surname, $fullname, $email, $image ) ) {
				$user = $this->get_user_by_username( $username );
			}
		} else {
			if ( $this->update_user( $username, $name, $surname, $fullname, $email, $image, '', '', '', '', false ) ) {
				$user = $this->get_user_by_username( $username );
			}
		}
		if ( $user ) {
			$user_id = $user['id'];
			$result  = $this->join_course( $course_id, $user_id, $isInstructor, $lis_result_sourceid, '' );
		}

		return $result;
	}

	/**
	 * Gets the object of tandem if exists, if not this user is the host
	 *
	 * @param $id_user
	 * @param $id_resource_lti
	 * @param $id_course
	 *
	 * @return array|bool|null
	 */
	public function is_invited_to_join( $id_user, $id_resource_lti, $id_course ) {

		return $this->get_invited_to_join( $id_user, $id_resource_lti, $id_course, false );
	}

	/**
	 * Gets the object of tandem if exists, if not this user is the host
	 *
	 * @param $id_user
	 * @param $id_resource_lti
	 * @param String $id_course
	 * @param boolean $return_array
	 *
	 * @return array|bool|null
	 */
	public function get_invited_to_join( $id_user, $id_resource_lti, $id_course, $return_array ) {
		$sql    = 'SELECT t.*, e.name, e.name_xml_file, e.relative_path, user.firstname, user.surname from tandem as t ' .
		          'inner join exercise e on e.id=t.id_exercise  ' . ( isset( $_SESSION[ FORCE_EXERCISE ] ) && $_SESSION[ FORCE_EXERCISE ] && $_SESSION[ FORCED_EXERCISE_NUMBER ] > 0 ? '' : ' and e.enabled=1 ' ) .
		          'LEFT outer join user on user.id=t.id_user_host ' .
		          'where id_course = ' . $id_course . ' AND id_resource_lti = ' . $this->escapeString( $id_resource_lti ) . ' and id_user_guest = ' . $id_user
		          . ' and is_guest_user_logged=0 and is_finished=0 order by t.created desc ';
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			if ( $return_array ) {
				$row = $this->obteComArray( $result );
			} else {
				$row = $this->obteObjecteComArray( $result );
			}

			return $row;
		} else {
			return false;
		}
	}

	/**
	 * Gets the last id
	 *
	 * @param $id_user
	 * @param $id_resource_lti
	 * @param String $id_course
	 *
	 * @return int
	 */
	public function get_lastid_invited_to_join( $id_user, $id_resource_lti, $id_course ) {
		$sql    = 'SELECT t.id from tandem as t ' .
		          'where t.id_course = ' . $id_course . ' AND t.id_resource_lti = ' . $this->escapeString( $id_resource_lti ) . ' and t.id_user_guest = ' . $id_user
		          . ' and t.is_guest_user_logged=0 and t.is_finished=0 order by t.created desc limit 0,1 ';
		$result = $this->consulta( $sql );
		$id     = 0;
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );
			if ( isset( $row ) && isset( $row['id'] ) ) {
				$id = intval( $row['id'] );
			}
		}

		return $id;
	}

	/**
	 * Gets the new tandems object of tandem if exists, if not this user is the host
	 *
	 * @param $id_last_insert
	 * @param $id_user
	 * @param $id_resource_lti
	 * @param String $id_course
	 * @param boolean $return_array
	 *
	 * @return array|bool|null
	 */
	public function get_new_invited_to_join( $id_last_insert, $id_user, $id_resource_lti, $id_course, $return_array ) {
		$sql = 'SELECT t.*, e.name, e.name_xml_file, e.relative_path, user.firstname, user.surname from tandem as t ' .
		       'inner join exercise e on e.id=t.id_exercise  ' . ( isset( $_SESSION[ FORCE_EXERCISE ] ) && $_SESSION[ FORCE_EXERCISE ] && $_SESSION[ FORCED_EXERCISE_NUMBER ] > 0 ? '' : ' and e.enabled=1 ' ) .
		       'LEFT outer join user on user.id=t.id_user_host ' .
		       'where t.id>' . $id_last_insert . ' AND id_course = ' . $id_course . ' AND id_resource_lti = ' . $this->escapeString( $id_resource_lti ) . ' and id_user_guest = ' . $id_user
		       . ' and is_guest_user_logged=0 and is_finished=0 order by t.created desc limit 0,1 ';

		$result = $this->consulta( $sql );

		if ( $this->numResultats( $result ) > 0 ) {
			if ( $return_array ) {
				$row = $this->obteComArray( $result );
			} else {
				$row = $this->obteObjecteComArray( $result );
			}

			return $row;
		} else {
			return false;
		}
	}

	/**
	 * Retorna el llista d'estudiants
	 *
	 * @param int $course_id
	 * #param int $current_user_id (check the user not to be this one, if not pass then there show all users of course)
	 * @param int $current_user_id
	 *
	 * @return array|bool
	 */
	public function obte_llistat_usuaris( $course_id, $current_user_id = - 1 ) {
		$row    = false;
		$result = $this->consulta( 'SELECT
            uc.id_user, uc.id_course,
            CAST(uc.is_instructor AS unsigned int) as is_instructor,
            uc.lis_result_sourceid, uc.inTandem, uc.lastAccessTandem, uc.language,
            u.* FROM user_course uc inner join `user` u on u.id = uc.id_user
                	WHERE uc.id_course = ' . $this->escapeString( $course_id ) . ' and uc.id_user != ' . $this->escapeString( $current_user_id )
		                           . ' order by u.surname' );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteComArray( $result );
		}

		return $row;
	}

	/**
	 * Returns the where clause to filter by created and finalized_type
	 *
	 * @param $created_date
	 * @param $finalized_date
	 * @param $table
	 *
	 * @return string
	 */
	private function add_start_finish_date_where( $created_date = '', $finalized_date = '', $table ) {
		$moreWhere = '';
		if ( strlen( $created_date ) > 0 ) {

			$moreWhere .= ' AND ' . $table . '.created >= ' . $this->escapeString( $created_date ) . ' ';
		}
		if ( strlen( $finalized_date ) > 0 ) {
			$moreWhere .= ' AND ' . $table . '.finalized <= ' . $this->escapeString( $finalized_date ) . ' ';
		}

		return $moreWhere;
	}

	/**
	 * Adds finsished to where clause
	 *
	 * @param $finished
	 * @param string $table
	 *
	 * @return string
	 */
	private static function add_finished_where( $finished = - 1, $table ) {
		$moreWhere = '';
		switch ( $finished ) {
			case 2:
				if ( $table == 'tandem' ) {
					$moreWhere .= ' AND ' . $table . '.date_guest_user_logged is null AND ' . $table . '.is_finished = 0 ';
				} else {
					$moreWhere .= ' AND ' . $table . '.is_finished = 0 ';
				}
				break;
			case 1:
				$moreWhere .= ' AND ' . $table . '.is_finished = 1 ';
				break;
			case 0:
				if ( $table == 'tandem' ) {
					$moreWhere .= ' AND ' . $table . '.date_guest_user_logged is not null AND ' . $table . '.is_finished = 0 ';
				} else {
					$moreWhere .= ' AND ' . $table . '.is_finished = 0 ';
				}
				break;
		}

		return $moreWhere;
	}

	/**
	 * @param $course_id
	 * @param $user_id
	 * @param $exercise
	 * @param int $id_tandem
	 * @param int $order
	 * @param int $order_dir
	 * @param string $created_date
	 * @param string $finalized_date
	 * @param int $finished
	 *
	 * @return array|null
	 */
	public function obte_llistat_tandems(
		$course_id,
		$user_id,
		$exercise,
		$id_tandem = - 1,
		$order = 0,
		$order_dir = 0,
		$created_date = '',
		$finalized_date = '',
		$finished = - 1
	) {
		$rows      = null;
		$moreWhere = '';
		if ( $exercise > 0 ) {
			$moreWhere .= ' AND tandem.id_exercise = ' . $exercise;
		}
		if ( $user_id > 0 ) {
			$moreWhere .= 'AND (tandem.id_user_host = ' . $user_id . ' OR tandem.id_user_guest  = ' . $user_id . ')';
		}
		if ( $id_tandem > 0 ) {
			$moreWhere .= 'AND tandem.id = ' . $id_tandem . ' ';
		}
		$moreWhere .= $this->add_start_finish_date_where( $created_date, $finalized_date, 'tandem' );
		$moreWhere .= self::add_finished_where( $finished, 'tandem' );

		$order_by = '';
		switch ( $order ) {
			case 1:
				$order_by = 'exercise.name';
				break;
			case 2:
				$order_by = 'user_tandem.total_time';
				break;
			case 3:
				$order_by = 'user.fullname';
				break;
			case 4:
				$order_by = 'user_host.fullname';
				break;
			case 5:
				$order_by = 'user_guest.fullname';
				break;
			case 6:
				$order_by = 'tandem.date_guest_user_logged';
				break;
			case 7:
				$order_by = 'status';
				break;
			case 8:
				$order_by = 'tandem.user_agent_host';
				break;
			case 9:
				$order_by = 'tandem.user_agent_guest';
				break;
			default:
				$order_by = 'tandem.created';
		}
		if ( $order_dir == 1 ) {
			$order_by .= ' DESC';
		}

		$sql = 'SELECT distinct tandem.*, exercise.name as exercise, user_tandem.points, user_tandem.total_time, ' .
		       ' user.fullname, user_host.fullname as user_host,user_guest.fullname as user_guest, (tandem.xml is not null) has_xml_description, ' .
		       ' CASE
                                                    WHEN tandem.date_guest_user_logged is not null AND tandem.finalized is not null
                                                        THEN 2
                                                    WHEN tandem.date_guest_user_logged is not null AND tandem.finalized is null
                                                        THEN 1
                                                        ELSE 0
                                                  END as status ' .
		       ' FROM tandem ' .
		       ' inner join exercise on tandem.id_exercise = exercise.id ' .
		       ' LEFT outer join user_tandem on user_tandem.id_tandem=tandem.id ' . ( ( $user_id > 0 ) ? ' AND user_tandem.id_user = ' . $user_id . ' ' : ' AND user_tandem.id_user = tandem.id_user_host' ) .
		       ' left join user on user_tandem.id_user = user.id ' .
		       ' left join user as user_host on tandem.id_user_host = user_host.id ' .
		       ' left join user as user_guest on tandem.id_user_guest = user_guest.id ' .
		       ' WHERE tandem.id_course = ' . $course_id . ' ' .
		       $moreWhere .
		       ' ORDER BY ' . $order_by;

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$rows_temp = $this->obteComArray( $result );
			$rows      = array();

			foreach ( $rows_temp as $key => $row ) {
				if ( $row['id_user_host'] > 0 && $row['id_user_guest'] > 0 ) {
					$rows[ $key ] = $row;
				}
			}
		}

		return $rows;
	}

	/**
	 * @param $course_id
	 * @param $user_id
	 * @param $exercise
	 * @param int $id_tandem
	 * @param int $id_task
	 * @param int $order
	 * @param int $order_dir
	 * @param string $created_date
	 * @param string $finalized_date
	 * @param int $finished
	 *
	 * @return array|null
	 */
	public function obte_task_tandems(
		$course_id,
		$user_id,
		$exercise,
		$id_tandem,
		$id_task,
		$order = 0,
		$order_dir = 0,
		$created_date = '',
		$finalized_date = '',
		$finished = - 1
	) {
		$rows      = null;
		$moreWhere = '';
		if ( $exercise > 0 ) {
			$moreWhere .= ' AND tandem.id_exercise = ' . $exercise;
		}
		if ( $user_id > 0 ) {
			$moreWhere .= ' AND user_tandem_task.id_user = ' . $user_id;
		}
		if ( $id_task > 0 ) {
			$moreWhere .= ' AND user_tandem_task.task_number = ' . $id_task;
		}
		$moreWhere .= $this->add_start_finish_date_where( $created_date, $finalized_date, 'user_tandem_task' );
		$moreWhere .= self::add_finished_where( $finished, 'user_tandem_task' );

		$order_by = '';
		switch ( $order ) {
			case 1:
				$order_by = 'exercise.name';
				break;
			case 2:
				$order_by = 'user_tandem_task.task_number';
				break;
			case 3:
				$order_by = 'user_tandem_task.total_time';
				break;
			case 4:
				$order_by = 'user.fullname';
				break;
			/* case 5:
              $order_by = 'user_tandem_task.finalized';
              break; */
			case 6:
				$order_by = 'user_tandem_task.finalized';
				break;
			default:
				$order_by = 'user_tandem_task.created';
		}
		if ( $order_dir == 1 ) {
			$order_by .= ' ASC';
		}

		$sql = 'SELECT user_tandem_task.*, tandem.id_exercise, tandem.is_finished as is_finished_tandem, tandem.finalized as finalized_tandem, exercise.name as exercise, ' .
		       ' user.fullname as user ' .
		       ' FROM user_tandem_task ' .
		       ' inner join tandem on user_tandem_task.id_tandem = tandem.id ' .
		       ' inner join exercise on tandem.id_exercise = exercise.id ' .
		       ' left join user  on user_tandem_task.id_user = user.id ' .
		       ' WHERE tandem.id_course = ' . $course_id . ' AND user_tandem_task.id_tandem = ' . $id_tandem . ' '
		       . $moreWhere . ' ORDER BY ' . $order_by;

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$rows = $this->obteComArray( $result );
		}

		return $rows;
	}

	/**
	 * @param $course_id
	 * @param $user_id
	 * @param $exercise
	 * @param $id_tandem
	 * @param $id_task
	 * @param int $id_question
	 * @param int $order
	 * @param int $order_dir
	 * @param string $created_date
	 * @param string $finalized_date
	 * @param int $finished
	 *
	 * @return array|null
	 */
	public function obte_questions_task_tandems(
		$course_id,
		$user_id,
		$exercise,
		$id_tandem,
		$id_task,
		$id_question,
		$order = 0,
		$order_dir = 0,
		$created_date = '',
		$finalized_date = '',
		$finished = - 1
	) {
		$rows      = null;
		$moreWhere = '';
		if ( $exercise > 0 ) {
			$moreWhere .= ' AND tandem.id_exercise = ' . $exercise;
		}
		if ( $user_id > 0 ) {
			$moreWhere .= ' AND user_tandem_task_question.id_user = ' . $user_id;
		}
		if ( $id_question > 0 ) {
			$moreWhere .= ' AND user_tandem_task_question.question_number = ' . $id_question;
		}
		$moreWhere .= $this->add_start_finish_date_where( $created_date, $finalized_date, 'user_tandem_task_question' );
		$moreWhere .= self::add_finished_where( $finished, 'user_tandem_task_question' );

		$order_by = '';
		switch ( $order ) {
			case 1:
				$order_by = 'exercise.name';
				break;
			case 2:
				$order_by = 'user_tandem_task_question.task_number';
				break;
			case 3:
				$order_by = 'user_tandem_task_question.question_number';
				break;
			case 4:
				$order_by = 'user_tandem_task_question.total_time';
				break;
			case 5:
				$order_by = 'user.fullname';
				break;
			/* case 6:
              $order_by = 'user_tandem_task.finalized';
              break; */
			case 7:
				$order_by = 'user_tandem_task_question.finalized';
				break;
			default:
				$order_by = 'user_tandem_task_question.created';
		}
		if ( $order_dir == 1 ) {
			$order_by .= ' ASC';
		}

		$sql    = 'SELECT user_tandem_task_question.*, tandem.id_exercise, tandem.is_finished as is_finished_tandem, tandem.finalized as finalized_tandem, exercise.name as exercise, ' .
		          ' user.fullname as user ' .
		          ' FROM user_tandem_task_question ' .
		          ' inner join tandem on user_tandem_task_question.id_tandem = tandem.id ' .
		          ' inner join exercise on tandem.id_exercise = exercise.id ' .
		          ' left join user on user_tandem_task_question.id_user = user.id ' .
		          ' WHERE tandem.id_course = ' . $course_id . ' AND user_tandem_task_question.id_tandem = ' . $id_tandem .
		          ' AND user_tandem_task_question.task_number = ' . $id_task
		          . $moreWhere . ' ORDER BY ' . $order_by;
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$rows = $this->obteComArray( $result );
		}

		return $rows;
	}

	/**
	 * @param $user_id
	 *
	 * @return mixed|string
	 */
	public function getUserRelatedTandem( $user_id ) {
		$sql    = 'SELECT fullname FROM `user` WHERE id = ' . $this->escapeString( $user_id );
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return $row['fullname'];
		}

		return '';
	}

	/**
	 * Obte tots els exercicis relacionats amb el curs
	 *
	 * @param $course_id
	 * @param int $enabled
	 * @param bool $week
	 *
	 * @return array|boolean
	 */
	public function get_tandem_exercises( $course_id, $enabled = 1, $week = false ) {
		$row   = false;
		$where = 'where (e.name_xml_file is not null and e.name_xml_file!=\'\' )';
		if ( $enabled == 1 ) {
			$where .= 'and e.enabled=1 ';
		}
		if ( $week !== false && intval( $week ) > 0 ) {
			$where .= 'and ce.week=' . intval( $week );
		}
		$result = $this->consulta( 'SELECT e.*, ce.id_course, ce.week, ce.lang FROM exercise e  ' .
		                           'inner join course_exercise ce on ce.id_exercise=e.id AND (ce.id_course = ' . $course_id . ' OR ce.id_course=-1) ' .
		                           $where
		);
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteComArray( $result );
		}

		return $row;
	}

	/**
	 * Gets data of exercise
	 *
	 * @param $exercise_id
	 *
	 * @return boolean
	 */
	public function get_exercise( $exercise_id ) {
		$row    = false;
		$where  = 'where e.id = ' . $exercise_id;
		$result = $this->consulta( 'SELECT e.* FROM exercise e  ' .
		                           $where
		);
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteComArray( $result );
		}

		return $row;
	}

	/**
	 * Deletes the exercise and course relactions
	 *
	 * @param $course_id
	 * @param $exercise_id
	 *
	 * @return bool
	 */
	public function delete_exercise( $course_id, $exercise_id ) {
		$row = $this->get_exercise( $exercise_id );
		if ( $row ) {
			$result = $this->consulta( 'DELETE FROM exercise  where id = ' . $this->escapeString( $exercise_id ) );
			if ( $result ) {
				$result = $this->consulta( 'DELETE FROM course_exercise where id_exercise = ' . $this->escapeString( $exercise_id ) );
			} else {
				$row = false;
			}
		}

		return $row;
	}

	/**
	 * Enables or disabled an exercise
	 *
	 * @param $exercise_id
	 *
	 * @return resource
	 */
	public function enable_exercise( $exercise_id ) {
		$result = $this->consulta( 'update exercise SET enabled = IF( enabled=\'0\',\'1\',\'0\')  where id = ' . $this->escapeString( $exercise_id ) );

		return $result;
	}

	/**
	 * Obte tots els exercicis relacionats amb el curs que no estan deshabilitats
	 *
	 * @param $course_id
	 *
	 * @return array|boolean
	 * @deprecated
	 */
	public function get_tandem_exercises_v1( $course_id ) {
		$row    = false;
		$result = $this->consulta( 'SELECT e.* FROM exercise e  ' .
		                           'where e.enabled=1 and e.id not in ' .
		                           '(select ce.id_exercise from course_exercise_disabled ce where ce.id_course = ' . $course_id . ')' );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteComArray( $result );
		}

		return $row;
	}

	/**
	 * Register a new exercise to a course
	 *
	 * @param $id_course
	 * @param $id_exercise
	 * @param $id_user
	 * @param $name
	 * @param $name_xml_file
	 * @param $enabled
	 * @param int $week
	 * @param String $lang
	 *
	 * @return string
	 */
	public function register_tandem_exercise(
		$id_course,
		$id_exercise,
		$id_user,
		$name,
		$name_xml_file,
		$enabled,
		$week = 0,
		$lang = 'all'
	) {
		$sql = '';
		if ( $id_exercise <= 0 ) {
			$sql = 'INSERT INTO exercise (name, name_xml_file, enabled, created, created_user_id, modified, modified_user_id)  ' .
			       'VALUES ' .
			       '(' . $this->escapeString( $name ) . ', ' . $this->escapeString( $name_xml_file ) . ', ' . ( $enabled ? 1 : 0 ) . ', NOW(), ' . $id_user . ', NOW(), ' . $id_user . ')';
		} else {
			$sql = 'UPDATE exercise SET name=' . $this->escapeString( $name ) . ', name_xml_file=' . $this->escapeString( $name_xml_file ) . ', enabled=' . ( $enabled ? 1 : 0 ) . ', modified = NOW(), modified_user_id = ' . $id_user . ' ' .
			       ' WHERE id = ' . $id_exercise;
		}
		$result = $this->consulta( $sql );
		if ( $result ) {
			if ( $id_exercise <= 0 ) {
				$id_exercise   = $this->get_last_inserted_id();
				$relative_path = '/' . $id_exercise;
				$sql           = 'UPDATE exercise SET relative_path=' . $this->escapeString( $relative_path ) .
				                 ' WHERE id = ' . $id_exercise;
				$result        = $this->consulta( $sql );

				//Relacionem amb el curs
				$sql    = 'INSERT INTO course_exercise (id_course, id_exercise, created, created_user_id, week, lang) ' .
				          'VALUES ' .
				          '(' . $id_course . ', ' . $id_exercise . ', NOW(), ' . $id_user . ', ' . $this->escapeString( $week ) . ', ' . $this->escapeString( $lang ) . ')';
				$result = $this->consulta( $sql );
			}
		}

		return $id_exercise;
	}

	/**
	 * Registra un nou tandem a l'espera que l'altre usuari es connecti
	 *
	 * @param $id_exercise
	 * @param $id_course
	 * @param $id_resource_lti
	 * @param $id_user_host
	 * @param $id_user_guest
	 * @param $message
	 * @param $user_agent
	 *
	 * @return bool|mysqli_result|string
	 */
	public function register_tandem(
		$id_exercise,
		$id_course,
		$id_resource_lti,
		$id_user_host,
		$id_user_guest,
		$message,
		$user_agent
	) {
		$other_user_data  = $this->getUserData( $id_user_guest );
		$user_agent_guest = $other_user_data['last_user_agent'];
		$sql              = 'INSERT INTO tandem (id_exercise, id_course, id_resource_lti, id_user_host, id_user_guest, message, is_guest_user_logged, is_finished, created, user_agent_host, user_agent_guest)
        	        VALUES (' . $id_exercise . ' ,' . $id_course . ' ,' . $this->escapeString( $id_resource_lti ) . ',' . $id_user_host . ',' . $id_user_guest . ' ,' . $this->escapeString( $message ) . ', 0, 0, now(), ' . $this->escapeString( $user_agent ) . ', ' . $this->escapeString( $user_agent_guest ) . ')';
		$result           = $this->consulta( $sql );
		if ( $result ) {
			$result = $this->get_last_inserted_id();
		}

		return $result;
	}

	/**
	 * Registra un nou tandem a l'espera que l'altre usuari es connecti
	 *
	 * @param $id_exercise
	 * @param $id_course
	 * @param $id_resource_lti
	 * @param $id_current_user
	 * @param $id_other_user
	 *
	 * @return resource
	 */
	public function has_invited_to_tandem(
		$id_exercise,
		$id_course,
		$id_resource_lti,
		$id_current_user,
		$id_other_user
	) {
		$ret = - 1;
		//Mirem si ens ha invitat
		$sql    = 'SELECT id FROM tandem
                WHERE id_exercise = ' . $id_exercise . ' AND id_course = ' . $id_course . ' AND id_resource_lti = ' . $this->escapeString( $id_resource_lti ) . '
                AND id_user_host = ' . $id_other_user . ' AND id_user_guest = ' . $id_current_user . ' AND is_guest_user_logged!=1 AND TIMESTAMPDIFF(MINUTE,created,now()) <= 10 order by created desc ';
		$result = $this->consulta( $sql );
		if ( $result && $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );
			if ( $row ) {
				$ret = $row['id'];
			}
		}

		return $ret;
	}

	/**
	 * Actualitza la connexio de l'altre usuari
	 *
	 * @param int $id
	 * @param $user_agent
	 *
	 * @return resource
	 */
	public function update_user_guest_tandem( $id, $user_agent ) {
		$result = false;
		$sql    = 'UPDATE tandem SET is_guest_user_logged = 1, date_guest_user_logged = now(), user_agent_guest = ' . $this->escapeString( $user_agent ) . ' ' .
		          'WHERE id = ' . $id;
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * @param string $id
	 * @param string $user_id
	 *
	 * @return bool|mysqli_result|string
	 */
	public function assign_partner_user_tandem( $id, $user_id ) {
		$result = false;
		$sql    = 'UPDATE tandem SET id_user_guest = ' . $user_id .
		          ' WHERE id = ' . $id;
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Actualitza la connexio de l'altre usuari
	 *
	 * @param string[] $tandem
	 *
	 * @return bool|mysqli_result|string
	 */
	public function update_user_guest_tandem_others( $tandem ) {
		$result = false;
		$sql    = 'UPDATE tandem SET is_guest_user_logged = 1 ' .
		          'WHERE id_course = ' . $tandem['id_course'] .
		          ' AND id_user_host = ' . $tandem['id_user_host'] .
		          ' AND id_user_guest = ' . $tandem['id_user_guest'] .
		          ' AND is_guest_user_logged=0 AND is_finished=0';
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Gets the last inserted id
	 * @return bool|mixed|mysqli_result|string
	 */
	public function get_last_inserted_id() {
		$sql    = 'select last_insert_id() as id ';
		$result = $this->consulta( $sql );
		if ( $result ) {
			$row = $this->obteObjecteComArray( $result );
			if ( $row ) {
				$result = $row['id'];
			}
		}

		return $result;
	}

	/**
	 * Retorna el id de l'exercici segons el nom que li passen de l'xml
	 *
	 * @param $name_xml_file
	 * @param int $id_course
	 *
	 * @return bool|mixed|mysqli_result|string
	 */
	public function getExerciseByXmlName( $name_xml_file, $id_course = - 1 ) {
		$sql    = 'select * from exercise e inner join course_exercise c on c.id_exercise=e.id where e.name_xml_file =' . $this->escapeString( $name_xml_file ) . ' and c.id_course =' . $this->escapeString( $id_course );
		$result = $this->consulta( $sql );
		if ( $result ) {
			$row = $this->obteObjecteComArray( $result );
			if ( $row ) {
				$result = $row['id'];
			}
		}

		return $result;
	}

	/**
	 * Retorna el tandem per id
	 *
	 * @param int $id
	 *
	 * @return bool|mixed|mysqli_result|string
	 */
	public function obteTandem( $id ) {
		$sql    = 'select tandem.*, exercise.name_xml_file, exercise.relative_path, exercise.name as name_exercise from tandem ' .
		          'inner join exercise on exercise.id = tandem.id_exercise ' .
		          'where tandem.id =' . $this->escapeString( $id );
		$result = $this->consulta( $sql );
		if ( $result ) {
			$result = $this->obteObjecteComArray( $result );
		}

		return $result;
	}

	/**
	 * @param $id
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function deleteTandem( $id ) {
		$sql    = 'SELECT * FROM tandem
                WHERE id = ' . $this->escapeString( $id );
		$result = $this->consulta( $sql );
		if ( $result ) {
			$row    = $this->obteObjecteComArray( $result );
			$sql    = 'INSERT INTO tandem_deleted (id, id_exercise, id_course, id_resource_lti,
                                                id_user_host, id_user_guest, message, xml,
                                                is_guest_user_logged, date_guest_user_logged,
                                                user_agent_host, user_agent_guest, is_finished,
                                                finalized, created) '
			          . ' VALUES ("' . $row['id'] . '", "' . $row['id_exercise'] . '", "' . $row['id_course']
			          . '", "' . $row['id_resource_lti'] . '", "' . $row['id_user_host'] . '", "' . $row['id_user_guest']
			          . '", "' . $row['message'] . '", "' . mysqli_real_escape_string( $this->conn, $row['xml'] )
			          . '", ' . $row['is_guest_user_logged'] . ', "' . $row['date_guest_user_logged']
			          . '", "' . $row['user_agent_host'] . '", "' . $row['user_agent_guest'] . '", '
			          . $row['is_finished'] . ', "' . ( $row['finalized'] ? $row['finalized'] : '0000-00-00 00:00:00' )
			          . '", "' . $row['created'] . '")';
			$result = $this->consulta( $sql );
			if ( $result ) {
				$sql = 'DELETE FROM tandem WHERE id = ' . $row['id'];

				return $this->consulta( $sql );
			}

			return $result;
		}

		return $result;
	}

	/**
	 * Obté el usuari guest pel waiting for user
	 *
	 * @param $id
	 *
	 * @return null|stdClass
	 */
	public function getUserB( $id ) {
		$sql    = 'select * from user ' .
		          'where id =' . $id;
		$result = $this->consulta( $sql );

		return $this->obteObjecte( $result );
	}

	/**
	 * Actualitza l'xml del tandem
	 *
	 * @param int $id_tandem
	 * @param string $xml
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function registra_xml_tandem( $id_tandem, $xml ) {
		$result = false;
		$sql    = 'UPDATE tandem SET xml = ' . $this->escapeString( $xml ) .
		          ' WHERE id = ' . $id_tandem .
		          ' AND is_finished=0';
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Finalitza el tandem
	 *
	 * @param int $id_tandem
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function finalitza_tandem( $id_tandem ) {
		$result = false;
		$sql    = 'UPDATE tandem SET is_finished = 1, finalized = now() ' .
		          ' WHERE id = ' . $id_tandem .
		          ' AND is_finished=0';
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Obte el temps total al tandem
	 *
	 * @param int $id_tandem
	 * @param $id_user
	 *
	 * @return int
	 */
	public function get_temps_total_tandem( $id_tandem, $id_user ) {

		$total_time = 0;

		// We have a trigger to manage it
        /*

        drop trigger updateTandemTime;
DELIMITER $$

CREATE TRIGGER updateTandemTime
AFTER UPDATE ON user_tandem_task FOR EACH ROW
BEGIN
	update user_tandem set total_time = (select sum(total_time) from user_tandem_task where id_tandem = NEW.id_tandem and id_user=NEW.id_user) where id_tandem = NEW.id_tandem and id_user=NEW.id_user;

END;
$$
DELIMITER ;
         *
         */
		/*$sql    = 'SELECT TIMESTAMPDIFF(SECOND,created,now()) as total_time FROM user_tandem where id_tandem = ' . $id_tandem . ' and id_user = ' . $id_user;
		$result = $this->consulta( $sql );

		if ( $this->numResultats( $result ) > 0 ) {
			$row        = $this->obteObjecteComArray( $result );
			$total_time = $row['total_time'];
		}*/

		return $total_time;
	}

	/**
	 * Registra l'accio de l'usuari al tandem
	 *
	 * @param $id_tandem
	 * @param $id_user
	 * @param boolean $is_finished
	 * @param int $points
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function registra_user_tandem( $id_tandem, $id_user, $is_finished = false, $points = 0 ) {


		$sql    = 'SELECT * from user_tandem where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user;
		$result = $this->consulta( $sql );
		if ( $result ) {

			$total_time = $this->get_temps_total_tandem( $id_tandem, $id_user );

			if ( $this->numResultats( $result ) == 0 ) {
				$sql = 'INSERT INTO user_tandem (id_tandem, id_user, total_time, points, is_finished, finalized, created) ' .
				       ' VALUES ' .
				       '(' . $id_tandem . ', ' . $id_user . ', ' . $total_time . ', ' . $points . ', ' . ( $is_finished ? 1 : 0 ) . ', ' . ( $is_finished ? 'now()' : 'null' ) . ', now())';
			} else {
				$sql = 'UPDATE user_tandem SET total_time = ' . $total_time . ', points=' . $points . ', is_finished = ' . ( $is_finished ? 1 : 0 ) . ', finalized = ' . ( $is_finished ? 'now()' : 'null' ) . ', updated = now() ' .
				       ' where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user;
			}

			$result = $this->consulta( $sql );

			if ( $result && $is_finished ) {
				$this->finalitza_tandem( $id_tandem );
			}
		}

		return $result;
	}

	/**
	 * Updates created to no
	 *
	 * @param $id_tandem
	 * @param $id_user
	 *
	 * @return bool|mysqli_result|string [type]            [description]
	 */
	public function update_user_access_tandem( $id_tandem, $id_user ) {

		$sql    = 'UPDATE user_tandem SET  created=now() ' .
		          ' where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user;
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Obte el total d'un exercici
	 *
	 * @param $id_tandem
	 * @param $id_user
	 * @param $task_number
	 *
	 * @return number
	 */
	public function get_temps_total_task_tandem( $id_tandem, $id_user, $task_number ) {

		$total_time = 0;

		$sql    = 'SELECT TIMESTAMPDIFF(SECOND,created,now()) as total_time FROM user_tandem_task where  id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number;
		$result = $this->consulta( $sql );

		if ( $this->numResultats( $result ) > 0 ) {
			$row        = $this->obteObjecteComArray( $result );
			$total_time = $row['total_time'];
		}

		return $total_time;
	}

	/**
	 * Registra el temps en una pregunta per un determinat usuari
	 *
	 * @param $id_tandem
	 * @param $id_user
	 * @param $task_number
	 * @param bool $is_finished
	 * @param int $points
	 *
	 * @return resource
	 */
	public function register_task_user_tandem( $id_tandem, $id_user, $task_number, $is_finished = false, $points = 0 ) {


		$sql    = 'SELECT * from user_tandem_task where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number;
		$result = $this->consulta( $sql );
		if ( $result ) {

			$total_time = $this->get_temps_total_task_tandem( $id_tandem, $id_user, $task_number );

			if ( $this->numResultats( $result ) == 0 ) {
				$sql = 'INSERT INTO user_tandem_task (id_tandem, id_user, task_number, total_time, points, is_finished, finalized, created) ' .
				       ' VALUES ' .
				       '(' . $id_tandem . ', ' . $id_user . ', ' . $task_number . ', ' . $total_time . ', ' . $points . ', ' . ( $is_finished ? 1 : 0 ) . ', ' . ( $is_finished ? 'now()' : 'null' ) . ', now())';
			} else {
				$sql = 'UPDATE user_tandem_task SET total_time = ' . $total_time . ', points=' . $points . ', is_finished = ' . ( $is_finished ? 1 : 0 ) . ', finalized = ' . ( $is_finished ? 'now()' : 'null' ) . ' ' .
				       ' where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number;
			}

			$result = $this->consulta( $sql );
		}

		if ( $is_finished ) {
			//if we are finished, then lets fill our partner task time aswell to make sure
			$result2 = $this->consulta( "select id_user_host,id_user_guest from tandem where id = '" . $id_tandem . "' " );
			if ( $this->numResultats( $result2 ) > 0 ) {
				$r = $this->obteComArray( $result2 );
				if ( $r[0]['id_user_host'] == $id_user ) {
					$id_user_partner = $r[0]['id_user_guest'];
				} else {
					$id_user_partner = $r[0]['id_user_host'];
				}

				$this->register_task_user_partner_tandem( $id_tandem, $id_user_partner, $task_number );
			}
		}

		return $result;

	}

	/**
	 * @param $id_tandem
	 * @param $id_user
	 * @param $task_number
	 * @param int $is_finished
	 * @param int $points
	 *
	 * @return bool|mysqli_result|resource
	 */
	private function register_task_user_partner_tandem(
		$id_tandem,
		$id_user,
		$task_number,
		$is_finished = 1,
		$points = 0
	) {


		$sql    = 'SELECT Cast(is_finished As unsigned integer) as is_finished from user_tandem_task where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number;
		$result = $this->consulta( $sql );
		if ( $result ) {

			$total_time = $this->get_temps_total_task_tandem( $id_tandem, $id_user, $task_number );
			$r          = $this->obteComArray( $result );

			if ( $this->numResultats( $result ) == 0 ) {
				$sql = 'INSERT INTO user_tandem_task (id_tandem, id_user, task_number, total_time, points, is_finished, finalized, created) ' .
				       ' VALUES ' .
				       '(' . $id_tandem . ', ' . $id_user . ', ' . $task_number . ', ' . $total_time . ', ' . $points . ', ' . ( $is_finished ? 1 : 0 ) . ', ' . ( $is_finished ? 'now()' : 'null' ) . ', now())';
			} else {

				if ( $r[0]['is_finished'] == 0 ) {
					$sql = 'UPDATE user_tandem_task SET total_time = ' . $total_time . ', points=' . $points . ', is_finished = ' . ( $is_finished ? 1 : 0 ) . ', finalized = ' . ( $is_finished ? 'now()' : 'null' ) . ' ' .
					       ' where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number;
				}
			}

			$result = $this->consulta( $sql );
		}

		return $result;


	}

	/**
	 * Obte el temps total per una pregunta d'una tasca
	 *
	 * @param $id_tandem
	 * @param $id_user
	 * @param $task_number
	 * @param $question_number
	 *
	 * @return int
	 */
	private function get_temps_total_task_question_tandem( $id_tandem, $id_user, $task_number, $question_number ) {

		$total_time = 0;

		$sql    = 'SELECT TIMESTAMPDIFF(SECOND,created,now()) as total_time FROM user_tandem_task_question where  id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number . ' AND question_number = ' . $question_number;
		$result = $this->consulta( $sql );

		if ( $this->numResultats( $result ) > 0 ) {
			$row        = $this->obteObjecteComArray( $result );
			$total_time = $row['total_time'];
		}

		return $total_time;
	}

	/**
	 * Registra el temps en una pregunta per un determinat usuari
	 *
	 * @param $id_tandem
	 * @param $id_user
	 * @param $task_number
	 * @param int $question_number
	 * @param bool $is_finished
	 * @param int $points
	 *
	 * @return resource
	 */
	public function register_task_question_user_tandem(
		$id_tandem,
		$id_user,
		$task_number,
		$question_number,
		$is_finished = false,
		$points = 0
	) {


		$sql    = 'SELECT * from user_tandem_task_question where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number . ' AND question_number = ' . $question_number;
		$result = $this->consulta( $sql );
		if ( $result ) {

			$total_time = $this->get_temps_total_task_question_tandem( $id_tandem, $id_user, $task_number,
				$question_number );

			if ( $this->numResultats( $result ) == 0 ) {
				$sql = 'INSERT INTO user_tandem_task_question (id_tandem, id_user, task_number, question_number,total_time, points, is_finished, finalized, created) ' .
				       ' VALUES ' .
				       '(' . $id_tandem . ', ' . $id_user . ', ' . $task_number . ', ' . $question_number . ', ' . $total_time . ', ' . $points . ', ' . ( $is_finished ? 1 : 0 ) . ', ' . ( $is_finished ? 'now()' : 'null' ) . ', now())';
			} else {
				$sql = 'UPDATE user_tandem_task_question SET total_time = ' . $total_time . ', points=' . $points . ', is_finished = ' . ( $is_finished ? 1 : 0 ) . ', finalized = ' . ( $is_finished ? 'now()' : 'null' ) . ' ' .
				       ' where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number . ' AND question_number = ' . $question_number;
			}

			$result = $this->consulta( $sql );
		}

		return $result;
	}

	/*     * ***************************************************************************************************** */
	/*     * ************************************ L T I  C O N S U M E R ***************************************** */
	/*     * ***************************************************************************************************** */

	/**
	 * Get the data of LTI app
	 *
	 * @param $id
	 *
	 * @return array|null
	 */
	public function loadDadesLTI( $id ) {

		$user_obj = isset( $_SESSION[ CURRENT_USER ] ) ? $_SESSION[ CURRENT_USER ] : false;

		$course_id = $_SESSION[ COURSE_ID ];
		$role_data = $this->get_role_classroom( $course_id, $user_obj->id );
		$role      = $role_data && isset( $role_data['role'] ) ? $role_data['role'] : '';

		$sql    = 'SELECT a.id, a.toolurl as toolurl, a.name, a.description, a.resourcekey, a.resourcekey, a.password, a.preferheight, a.sendname, ' .
		          ' a.sendemailaddr, a.acceptgrades, a.allowroster, a.allowsetting, a.customparameters as customparameters,  ' .
		          'a.allowinstructorcustom, a.organizationid, a.organizationurl, a.launchinpopup, a.debugmode, a.registered, a.updated ' .
		          'from lti_application a ' .
		          'where a.id = ' . $id;
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return $row;
		} else {
			return null;
		}
	}

	/**
	 * Obte el rol en la bd
	 *
	 * @param int $course_id
	 * @param int $user_id
	 *
	 * @return boolean
	 */
	public function get_role_classroom( $course_id, $user_id ) {
		$result = $this->consulta( "SELECT * FROM user_course where id_user = " . $user_id . " AND id_course = " . $course_id );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return $row;
		} else {
			return false;
		}
	}

	/**
	 * Loads data of remote App
	 *
	 * @param $id
	 *
	 * @return array|null
	 */
	public function loadRemoteApp( $id ) {

		$sql    = 'SELECT * from remote_application where id = ' . $id;
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return $row;
		} else {
			return null;
		}
	}

	/*     * ****************************************************************** */
	/*     * ********* M A N A G E   W A I T I N G    R O O M ***************** */
	/*     * ****************************************************************** */

	/**
	 * Actualitzem el tandem insertant el user_guest mitjançant sabent el user_host
	 *
	 * @param $id_tandem
	 * @param $id_user_guest
	 *
	 * @return boolean
	 */
	public function updateUserGuestTandem( $id_tandem, $id_user_guest ) {
		$result = false;
		$sql    = 'UPDATE tandem SET id_user_guest = ' . $id_user_guest . '  WHERE id = ' . $id_tandem;
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Sel.leccionem el usuari que fa mes temps que espera
	 *
	 * @param $id_course
	 * @param $id_exercise
	 * @param $otherLanguage
	 *
	 * @return array
	 */
	public function getFirstUserWaiting( $id_course, $id_exercise, $otherLanguage ) {

		$sql = ' select wru.id_user,wr.id as id_waiting_room, tandem.id from waiting_room_user as wru
          inner join waiting_room as wr on wru.id_waiting_room = wr.id
          inner join tandem as tandem on tandem.id_exercise = wr.id_exercise
          and tandem.id_course = wr.id_course and tandem.id_user_host = wru.id_user
          and coalesce(tandem.finalized,0) = 0 and coalesce(tandem.is_finished,0) = 0
          and tandem.id_user_guest = -1
          where wr.id_course = ' . $id_course . ' and wr.id_exercise = ' . $id_exercise . ' and wr.language = ' . $this->escapeString( $otherLanguage ) . '
          order by wru.created asc
          limit 0, 1 ';

		$result = $this->consulta( $sql );

		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteComArray( $result );

			return $row;
		} else {
			return false;
		}

	}

	/**
	 * Insert User in waiting room
	 *
	 * @param $id_waiting_room
	 * @param $language
	 * @param $idCourse
	 * @param $idExercise
	 * @param $idUser
	 * @param $user_agent
	 *
	 * @return bool
	 */
	public function insertUserIntoWaitingRoom(
		$id_waiting_room,
		$language,
		$idCourse,
		$idExercise,
		$idUser,
		$user_agent
	) {
		$result = false;

		//TODO check if is
		$sql       = 'SELECT * FROM `waiting_room_user` where id_waiting_room =' . $id_waiting_room . ' and id_user = ' . $idUser;
		$resultSQL = $this->consulta( $sql );
		if ( $this->numResultats( $resultSQL ) <= 0 ) {
			//Insert into waiting_room_user
			$sql       = 'INSERT INTO waiting_room_user (id_waiting_room, id_user, user_agent, created) VALUES (' . $id_waiting_room . ',' . $idUser . ',' . $this->escapeString( $user_agent ) . ',now())';
			$resultSQL = $this->consulta( $sql );
			if ( $resultSQL ) {
				$result = $this->addOrRemoveUserToWaitingRoom( $id_waiting_room, + 1 );
			}
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Add or remove User to waiting room
	 *
	 * @param $id_waiting_room
	 * @param $number_user_to_add_or_remove
	 *
	 * @return boolean
	 */
	private function addOrRemoveUserToWaitingRoom( $id_waiting_room, $number_user_to_add_or_remove ) {
		$ok = false;
		//1. Check in waiting room
		$sql    = 'Select * FROM `waiting_room` WHERE `id` = ' . $this->escapeString( $id_waiting_room );
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$object = $this->obteObjecteComArray( $result );
			//$id_user_wating_room = $object['id'];
			$language                = $object['language'];
			$id_course               = $object['id_course'];
			$id_exercise             = $object['id_exercise'];
			$number_user_waiting_old = $object['number_user_waiting'];
			$created                 = $object['created'];
			$number_user_waiting     = $object['number_user_waiting'] + $number_user_to_add_or_remove;
			if ( $number_user_waiting <= 0 ) {
				//2.Insert in history table
				$sqlInsert = 'INSERT INTO `waiting_room_history` (`id`, `id_waiting_room`, `language`, `id_course`, `id_exercise`, `number_user_waiting`, `created`, `created_history`) '
				             . 'VALUES (NULL, ' . $this->escapeString( $id_waiting_room ) . ', ' . $this->escapeString( $language ) . ', ' . $this->escapeString( $id_course ) . ', '
				             . '' . $this->escapeString( $id_exercise ) . ', ' . $this->escapeString( $number_user_waiting_old ) . ', ' . $this->escapeString( $created ) . ', NOW())';
				if ( $this->consulta( $sqlInsert ) ) {
					//3. Delete from waiting_room_user

					$sqlDelete = 'DELETE FROM `waiting_room` WHERE `id` = ' . $this->escapeString( $id_waiting_room );
					$this->debugMessage( "1 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sqlDelete );
					if ( $this->consulta( $sqlDelete ) ) {
						$ok = true;
					}
				}
			} else {

				$ok = true;
			}


		}

		return $ok;
	}

	/**
	 * @param $id_waiting_room
	 *
	 * @return bool
	 */
	private function addToWaitingRoomHistory( $id_waiting_room ) {
		$ok = false;
		//1. Check in waiting room
		$sql    = 'Select * FROM `waiting_room` WHERE `id` = ' . $this->escapeString( $id_waiting_room );
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$object = $this->obteObjecteComArray( $result );
			//$id_user_wating_room = $object['id'];
			$language                = $object['language'];
			$id_course               = $object['id_course'];
			$id_exercise             = $object['id_exercise'];
			$number_user_waiting_old = $object['number_user_waiting'];
			$created                 = $object['created'];
			$number_user_waiting     = $object['number_user_waiting'];
			if ( $number_user_waiting <= 0 ) {
				//2.Insert in history table
				$sqlInsert = 'INSERT INTO `waiting_room_history` (`id`, `id_waiting_room`, `language`, `id_course`, `id_exercise`, `number_user_waiting`, `created`, `created_history`) '
				             . 'VALUES (NULL, ' . $this->escapeString( $id_waiting_room ) . ', ' . $this->escapeString( $language ) . ', ' . $this->escapeString( $id_course ) . ', '
				             . $this->escapeString( $id_exercise ) . ', ' . $this->escapeString( $number_user_waiting_old ) . ', ' . $this->escapeString( $created ) . ', NOW())';
				if ( $this->consulta( $sqlInsert ) ) {
					//3. Delete from waiting_room_user

					$sqlDelete = 'DELETE FROM `waiting_room` WHERE `id` = ' . $this->escapeString( $id_waiting_room );
					$this->debugMessage( "2 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sqlDelete );
					if ( $this->consulta( $sqlDelete ) ) {
						$ok = true;
					}
				}
			} else {

				$ok = true;
			}


		}

		return $ok;
	}

	/**
	 * Move the current user to history of waiting room
	 *
	 * @param $id_waiting_room
	 * @param $id_user
	 * @param string $status
	 * @param int $tandemID
	 *
	 * @return bool
	 */
	public function moveUserToHistory( $id_waiting_room, $id_user, $status = 'give_up', $tandemID = - 1 ) {
		$ok = false;
		//1. Check in waiting room user
		$sql = 'Select * FROM `waiting_room_user` WHERE `id_user` = ' . $this->escapeString( $id_user );
		if ( $id_waiting_room > 0 ) {
			$sql .= ' AND `id_waiting_room` = ' . $this->escapeString( $id_waiting_room );
		}
		$result                   = $this->consulta( $sql );
		$deleted                  = false;
		$previous_id_waiting_room = $id_waiting_room;
		if ( $this->numResultats( $result ) > 0 ) {
			$r = $this->obteComArray( $result );
			foreach ( $r as $object ) {
				$id_user_wating_room = $object['id'];
				$user_agent          = $object['user_agent'];
				$created             = $object['created'];
				$id_waiting_room     = $object['id_waiting_room'];
				$timestamp           = $object['timestamp'];
				//2.Insert in history table
				$sqlInsert = 'INSERT INTO `waiting_room_user_history` (`id`, `id_waiting_room`, `id_user`, `status`, `id_tandem` , `user_agent`, `created`, `created_history`, `timestamp`) VALUES (NULL, ' . $this->escapeString( $id_waiting_room ) . ', ' . $this->escapeString( $id_user ) . ', ' . $this->escapeString( $status ) . ', ' . $this->escapeString( $tandemID ) . ', ' . $this->escapeString( $user_agent ) . ',' . $this->escapeString( $created ) . ', NOW(),' . $this->escapeString( $timestamp ) . ')';
				if ( $this->consulta( $sqlInsert ) ) {
					//3. Delete from waiting_room_user
					$sqlDelete = 'DELETE FROM `waiting_room_user` WHERE `id` = ' . $this->escapeString( $id_user_wating_room );
					$this->debugMessage( "3 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sqlDelete );
					if ( $this->consulta( $sqlDelete ) ) {
						if ( ! $deleted ) {
							$ok = $this->addOrRemoveUserToWaitingRoom( $id_waiting_room, - 1 );
						}
						if ( $previous_id_waiting_room != - 1 ) {
							$deleted = true;
						}
					}
				}

			}
			/*            $object = $this->obteObjecteComArray($result);
                        $id_user_wating_room = $object['id'];
                        $user_agent = $object['user_agent_guest'];
                        $created = $object['created'];
                        //2.Insert in history table
                        $sqlInsert = 'INSERT INTO `waiting_room_user_history` (`id`, `id_waiting_room`, `id_user`, `status`, `id_tandem` , `user_agent`, `created`, `created_history`) VALUES (NULL, ' . $this->escapeString($id_waiting_room) . ', ' . $this->escapeString($id_user) . ', '. $this->escapeString($status) .', ' . $this->escapeString($tandemID) . ', ' . $this->escapeString($user_agent) . ',' . $this->escapeString($created) . ', NOW())';
                        if ($this->consulta($sqlInsert)){
                            //3. Delete from waiting_room_user
                            $sqlDelete = 'DELETE FROM `waiting_room_user` WHERE `id` = ' . $this->escapeString($id_user_wating_room);
                        }*/
		}

		return $ok;
	}

	/**
	 * @param $language
	 * @param $courseID
	 * @param $userID
	 * @param $typeClose
	 * @param $tandemID
	 *
	 * @return bool
	 */
	public function userIsNoWaitingMore( $language, $courseID, $userID, $typeClose, $tandemID ) {
		$ok        = true;
		$sqlSelect = 'select wr.id_exercise , wr.number_user_waiting from waiting_room as wr'
		             . ' inner join waiting_room_user as wru on wru.id_waiting_room = wr.id'
		             . ' where wr.language = ' . $this->escapeString( $language ) . ' and wr.id_course = ' . $courseID . ' and wru.id_user = ' . $userID . ' and wr.number_user_waiting > 0 limit 0,1 ';

		$resultSelect = $this->consulta( $sqlSelect );

		if ( $this->numResultats( $resultSelect ) > 0 ) {

			$resultSelect = $this->obteComArray( $resultSelect );

			$id_exercise = $resultSelect[0]['id_exercise'];

			$number_user_waiting = $resultSelect[0]['number_user_waiting'];

			//si numero de usuaris en espera es mes gran o igual k 1

			if ( $number_user_waiting > 0 ) {

				$sqlUpdateWR    = 'update `waiting_room` set number_user_waiting = number_user_waiting - 1 where id_course = ' . $courseID . ' and id_exercise =  ' . $id_exercise;
				$resultUpdateWR = $this->consulta( $sqlUpdateWR );
				$sqlDeleteTA    = 'delete from tandem where id_exercise = ' . $id_exercise . ' and id_course = ' . $courseID . ' and id_user_host= ' . $userID . ' and id_user_guest = -1';
				$resultDeleteTA = $this->consulta( $sqlDeleteTA );

				$sqlSelectWR    = 'select id,number_user_waiting from waiting_room where id_course = ' . $courseID . ' and id_exercise =  ' . $id_exercise;
				$resultSelectWR = $this->consulta( $sqlSelectWR );

				if ( $this->numResultats( $resultSelectWR ) > 0 ) {

					$resultSelect = $this->obteComArray( $resultSelectWR );

					$id = $resultSelect[0]['id']; //id waiting room

					$number_user_waiting = $resultSelect[0]['number_user_waiting'];


					if ( $typeClose == 'assigned' ) {
						$this->moveUserToHistory( $id, $userID, $typeClose, $tandemID );
					} else {
						$this->moveUserToHistory( $id, $userID, $typeClose, $tandemID = false );
					}

					$sqlDeleteWR = 'delete from waiting_room_user where id_waiting_room = ' . $id . ' and id_user = ' . $userID;
					$this->debugMessage( "4 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sqlDeleteWR );
					$resultDeleteWR = $this->consulta( $sqlDeleteWR );

					if ( $number_user_waiting == 0 ) {

						$this->addToWaitingRoomHistory( $id );

						$sqlDeleteWR = 'delete from waiting_room where id_exercise = ' . $id_exercise . ' and id_course = ' . $courseID . ' and number_user_waiting = 0';
						$this->debugMessage( "5 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sqlDeleteWR );
						$resultDeleteWR = $this->consulta( $sqlDeleteWR );

					}

				}

			} else {
				// $this->addToWaitingRoomHistory($id);

				$sqlDeleteWR = 'delete from waiting_room where id_exercise = ' . $id_exercise . ' and id_course = ' . $courseID . ' and number_user_waiting = 0';
				$this->debugMessage( "6 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sqlDeleteWR );
				$resultDeleteWR = $this->consulta( $sqlDeleteWR );


			}
		}

		return $ok;
	}

	/*
    public function insertUserIntoRoom($language, $idCourse, $idExercise, $idNumberUserWaiting, $idUser) {
        $result = false;
        //echo "<h1>gestorBD!!</h1>";
        //echo $idNumberUserWaiting, $idCourse, $idExercise, $idNumberUserWaiting, gmdate('Y-m-d h:i:s \G\M\T');
        //DATETIME en formato 'YYYY-MM-DD HH:MM:SS' . El rango soportado es de '1000-01-01 00:00:00' a '9999-12-31 23:59:59'.
        $sql = 'INSERT INTO waiting_room (language, id_course, id_exercise,number_user_waiting,created) VALUES (' . $this->escapeString($language) . ',' . $idCourse . ',' . $idExercise . ',' . $idNumberUserWaiting . ',now())';

        $result = $this->consulta($sql);

        if ($result) {
            $waiting_room_id = mysql_insert_id();
            echo "Last it $waiting_room_id";
            //Insert into waiting_room_user
            $sql = 'INSERT INTO waiting_room_user (id_waiting_room, id_user,created) VALUES (' . $waiting_room_id . ',' . $idUser . ',now())';
            $result = $this->consulta($sql);

        }
        return $result;
    }*/

	/**
	 * We selected the exercises that language is different from the user and select the same course.
	 *
	 * @param string $language
	 * @param integer $courseID
	 *
	 * @return array
	 */
	public function check_offered_exercises( $language, $courseID ) {
		$row    = array();
		$result = $this->consulta( "Select id_exercise,id FROM waiting_room WHERE language != " . $this->escapeString( $language ) . " AND id_course = " . $this->escapeString( $courseID ) );
		while ( $fila = $this->obteObjecteComArray( $result ) ) {
			$row[] = $fila['id_exercise'] . '-' . $fila['id'];
		}

		return $row;
	}

	/**
	 * We INSERT a user with a new exercise if don't exists or UPDATE the
	 * existing exercise increasing +1 in the comput global in the waiting_room table
	 *
	 * @param $language
	 * @param $courseID
	 * @param $exerciseID
	 * @param $idUser
	 * @param string $user_agent
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function offer_exercise( $language, $courseID, $exerciseID, $idUser, $user_agent = '' ) {
		$ok = false;
		if ( $user_agent == '' ) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		}

		//return 'Dins offer exercise: '.$language.'-'.$courseID.'-'.$exerciseID;
		//TODO delete it
		$sqlDelete = 'delete from waiting_room where number_user_waiting = 0 and id_course = ' . $courseID . ' and id_exercise = ' . $exerciseID;
		$this->debugMessage( "7 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sqlDelete );
		$resultDelete = $this->consulta( $sqlDelete );


		$sqlSelect       = 'select number_user_waiting, id from waiting_room where id_course = ' . $courseID . ' and id_exercise = ' . $exerciseID;
		$resultSelect    = $this->consulta( $sqlSelect );
		$waiting_room_id = - 1;

		if ( $this->numResultats( $resultSelect ) > 0 ) {

			$resultSelect = $this->obteComArray( $resultSelect );

			$waiting_room_id = $resultSelect[0]['id'];

			$sql = "UPDATE waiting_room SET number_user_waiting = number_user_waiting + 1  WHERE id = " . $waiting_room_id . " and id_course = " . $courseID . " and id_exercise = " . $exerciseID;
			$ok  = $this->consulta( $sql );

		} else {
			$sqlInsert = 'INSERT INTO waiting_room (language, id_course, id_exercise,number_user_waiting,created) VALUES (' . $this->escapeString( $language ) . ',' . $courseID . ',' . $exerciseID . ',1,now())';
			$ok        = $this->consulta( $sqlInsert );
			if ( $ok ) {

				$waiting_room_id = $this->getLastInsertedId();
			}

		}
		if ( $ok ) {
			$this->insertUserIntoWaitingRoom( $waiting_room_id, $language, $courseID, $exerciseID, $idUser,
				$user_agent );
		}

		return $ok;
	}

	/**
	 * @param $language
	 * @param $courseID
	 * @param $exerciseID
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function tandem_exercise( $language, $courseID, $exerciseID ) {
		$ok = false;

		$sqlSelect       = 'select number_user_waiting, id from waiting_room where id_course = ' . $courseID . ' and id_exercise = ' . $exerciseID;
		$resultSelect    = $this->consulta( $sqlSelect );
		$waiting_room_id = - 1;

		if ( $this->numResultats( $resultSelect ) > 0 ) {

			$resultSelect = $this->obteComArray( $resultSelect );

			$waiting_room_id = $resultSelect[0]['id'];

			$sqlPrueba = "UPDATE waiting_room SET number_user_waiting = number_user_waiting - 1  WHERE id_course = " . $courseID . " and id_exercise = " . $exerciseID;
			$ok        = $this->consulta( $sqlPrueba );


			$result = $this->addOrRemoveUserToWaitingRoom( $waiting_room_id, - 1 );

		} else {

			//no hi han usuaris esperant **** en principi MAI ACCEDIREM AQUI

		}

		return $ok;


	}

	/**
	 * @param $language
	 * @param $courseID
	 * @param $exerciseID
	 */
	public function start_tandem( $language, $courseID, $exerciseID ) {
		$resultSelect = false;

		$sqlSelect    = 'select number_user_waiting from waiting_room where id_exercise = ' . $exerciseID . ' and language = ' . $this->escapeString( $language ) . ' and id_course = ' . $courseID;
		$resultSelect = $this->consulta( $sqlSelect );
		if ( $this->numResultats( $resultSelect ) > 0 ) {
			//Si, aparellem fent FIFO
			//Descontem de la waiting Room -1
			//De la waiting room users eliminar registre i afegir-lo al històric
			//Sobre el waiting Room si el camp number_user_waiting = 0 eliminar la fila i passarla al històric.
			//REDIRECCIONAR AL TANDEM
		} else {
			//No podem aparellar
			//Passem parámetre ACTION() i pass1
			$this->check_offered_exercises();
		}
	}

	/**
	 * @param $language
	 * @param $idCourse
	 * @param $idExercise
	 * @param $idUser
	 * @param $idRscLti
	 * @param $onlyExID
	 * @param bool $is_tandem
	 *
	 * @return bool|mysqli_result|resource|string
	 */
	public function updateWaitingDB(
		$language,
		$idCourse,
		$idExercise,
		$idUser,
		$idRscLti,
		$onlyExID,
		$is_tandem = false
	) {

		//$idExercise = str_replace( '%2F', '/', $idExercise );

		$other_language = $language == 'en_US' ? 'es_ES' : 'en_US';

		//1st. Check it there are other language offering
		$tandem_waiting_room_other_lang_offered = $this->getWaitingTandemRoom( $idCourse, $other_language, $onlyExID );
		$tandem_waiting_room                    = true;
		$ok                                     = false;

		if ( ! $tandem_waiting_room_other_lang_offered ) {
			if ( $is_tandem === true ) {
				return "room_taken";
			}
			//2nd if not we get if my language offering (si estem oferint del nostre propi )
			/* $tandem_waiting_room = $this->getWaitingTandemRoom($idCourse, $language, $onlyExID);

         }

         if (!$tandem_waiting_room_other_lang_offered){

             //3.-oferim l'exercici
             if($is_tandem == true){
                 return "room_taken";
                }*/

			$ok = $this->offer_exercise( $language, $idCourse, $onlyExID, $idUser );
		} else {
			//return 'tandem_exercise';
			$array = array();

			//false $user_tandem_host
			//retorna false !!! i salta ... no fa tandem ... xque?¿
			$user_tandem_host = $this->getFirstUserWaiting( $idCourse, $onlyExID, $other_language );
			if ( $user_tandem_host && count( $user_tandem_host ) > 0 ) {

				$id_user_host    = $user_tandem_host[0]['id_user'];
				$id_tandem       = $user_tandem_host[0]['id'];
				$id_waiting_room = $user_tandem_host[0]['id_waiting_room'];

				//insert us on tandem
				$resultUpdate = $this->updateUserGuestTandem( $id_tandem, $idUser );

				//$this->userIsNoWaitingMore($language,$idCourse,$idUser,$type='assigned',$id_tandem);

				$this->debugMessage( "20 " . $_SESSION[ CURRENT_USER ]->fullname . " UPDATING USER Waiting $other_language,$idCourse,$id_user_host,'assigned',$id_tandem" );
				$this->userIsNoWaitingMore( $other_language, $idCourse, $id_user_host, $type = 'assigned', $id_tandem );

				//$ok = $this->tandem_exercise($language, $idCourse, $onlyExID);

			} else {
				if ( $is_tandem === true ) {
					return "room_taken";
				}
			}

		}

		return $ok;
	}

	/**
	 * @param $courseID
	 * @param $language
	 * @param $id_exercise
	 *
	 * @return array|bool
	 */
	public function getWaitingTandemRoom( $courseID, $language, $id_exercise ) {

		$sql = "SELECT ce.id_exercise, e.name, e.relative_path, e.name_xml_file, wr.language, coalesce(wr.number_user_waiting,0) as number_user_waiting FROM `course_exercise` as ce
            inner join exercise as e on ce.id_exercise = e.id
            left join waiting_room as wr on ce.id_course = ce.id_course and ce.id_exercise = wr.id_exercise and wr.number_user_waiting>0
            WHERE ce.id_course = " . $courseID . " and wr.language = " . $this->escapeString( $language ) . " and wr.id_exercise = " . $this->escapeString( $id_exercise ) . "  order by ce.id_exercise, wr.language";


		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteComArray( $result );

			return $row;
		} else {
			return false;
		}
	}


	/***************************************************************************/
	/*   ASPECTE VISUAL DE LA TAULA DEL TANDEM - WAITING ROOM  */
	/****************************************************************************/

	/**
	 * @param $courseID
	 *
	 * @return array|bool
	 */
	public function getWaitingTandemRoomTable( $courseID ) {

		$sql = "SELECT wr.id as waitingRoomId, ce.id_exercise, e.name, e.relative_path, e.name_xml_file, wr.language, coalesce(wr.number_user_waiting,0) as number_user_waiting FROM `course_exercise` as ce
            inner join exercise as e on ce.id_exercise = e.id
            left join waiting_room as wr on ce.id_course = ce.id_course and ce.id_exercise = wr.id_exercise and wr.number_user_waiting>0
            WHERE ce.id_course = " . $courseID . " order by ce.id_exercise, wr.language";


		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteComArray( $result );

			return $row;
		} else {
			return false;
		}
	}

	/****************************
	 * Victor - auto load tandem.
	 ****************************/

	/**
	 * Get all the exercices of the week
	 *
	 * @param $id_course
	 *
	 * @return array
	 */
	public function getExercicesCurrentWeek( $id_course, $allow_fallback_language = false ) {

		//if they passed the custom parameter WEEK with the LTIcall , then we use that week, if not then we use the max week there is.
		if ( ! empty( $_SESSION[ WEEK ] ) && $_SESSION[ WEEK ] > 0 ) {
			$sql = 'SELECT id_exercise from course_exercise  WHERE week ="' . $_SESSION[ WEEK ] . '" and id_course = ' . $id_course;
		} elseif ( ! empty( $_SESSION[ PREVIOUS_WEEK ] ) && $_SESSION[ PREVIOUS_WEEK ] > 0 ) {
			$sql = 'SELECT id_exercise from course_exercise  WHERE week in( select week from course_exercise  where id_course = ' . $id_course . ' order by week desc limit 1 offset 1 ) and id_course = ' . $id_course;
		} elseif ( $_SESSION[ WEEK ] === 0 ) {
			$sql = 'SELECT id_exercise from course_exercise  WHERE id_course = ' . $id_course;
		} else {
			$sql = 'SELECT id_exercise from course_exercise  WHERE week in( select max(week) from course_exercise where id_course = ' . $id_course . ') and id_course = ' . $id_course;
		}

		if ( $allow_fallback_language ) {
			$sql .= ' AND (lang is null or lang = \'all\') ';
		}

		$result = $this->consulta( $sql );
		$ids    = array();

		if ( $this->numResultats( $result ) > 0 ) {
			$ids_exercise = array_values( $this->obteComArray( $result ) );
			foreach ( $ids_exercise as $value ) {
				$ids[] = $value['id_exercise'];
			}
		}

		return $ids;
	}

	/**
	 * Get all the exercices of the week that the user hasnt finished yet.
	 *
	 * @param $id_course
	 * @param $user_id
	 *
	 * @return array
	 */
	public function getExercicesNotDoneWeek( $id_course, $user_id, $allow_fallback_language = false ) {

		$ids = $this->getExercicesCurrentWeek( $id_course, $allow_fallback_language );

		if ( count( $ids ) > 0 ) {

			$sql    = "SELECT distinct(id_exercise) from tandem where
                    id_course = " . $this->escapeString( $id_course ) . " and (id_user_guest = '" . $user_id . "' or id_user_host = '" . $user_id . "') ";
			$result = $this->consulta( $sql );
			if ( $this->numResultats( $result ) > 0 ) {
				$r = $this->obteComArray( $result );
				foreach ( $r as $value ) {
					if ( ( $key = array_search( $value['id_exercise'], $ids ) ) !== false ) {
						unset( $ids[ $key ] );
					}
				}
			}
		} else {


			//there is nothing for that week, lets just grab them all :o

			$and_where = '';
			if ( $allow_fallback_language ) {
				$and_where = ' AND (lang is null or lang = \'all\') ';
			}
			$sql    = 'SELECT id_exercise,created from course_exercise  WHERE  id_course = ' . $id_course . $and_where . ' order by RAND() limit 10';
			$result = $this->consulta( $sql );
			$ids    = array();

			if ( $this->numResultats( $result ) > 0 ) {

				$ids_exercise = array_values( $this->obteComArray( $result ) );
				foreach ( $ids_exercise as $value ) {
					$ids[] = $value['id_exercise'];
				}
			}
		}

		return $ids;
	}

	/**
	 *  We pass an array of exercices the user is waiting or
	 *
	 * @param $exercises_ids
	 * @param $id_course
	 * @param $language
	 * @param $user_id
	 * @param $otherlanguage
	 * @param bool $unique_team
	 * @param bool $fallback_same_language
	 *
	 * @return array|bool
	 */
	public function checkIfAvailableTandemForExercise(
		$exercises_ids,
		$id_course,
		$language,
		$user_id,
		$otherlanguage,
		$unique_team = false,
		$fallback_same_language = false
	) {
		if ( count( $exercises_ids ) == 0 ) {
			//then offer all
			$exercises_ids = $this->getExercicesCurrentWeek( $id_course, $fallback_same_language );
		}
		//lets see if there is someone waiting for one ot these exercises.
		foreach ( $exercises_ids as $id_ex ) {
			$val = $this->checkForTandems( $id_ex, $id_course, $otherlanguage, $user_id, $unique_team );
			if ( empty( $val ) && $fallback_same_language ) {
				$val = $this->checkForTandems( $id_ex, $id_course, $otherlanguage, $user_id, true, false );
			}
			if ( ! empty( $val ) ) {
				// We have someone already waiting for one of the exercises :)
				return $val;
			}
		}

		$this->deleteUserFromWaitingRooms( $user_id, $id_course );
		//if we are here is because there is no one for this exercise, so lets offer them.
		foreach ( $exercises_ids as $id_ex ) {
			$this->offer_exercise_autoassign( $language, $id_course, $id_ex, $user_id, '', $unique_team );
		}

		return false;
	}

	/**
	 * Here we check if there are any tandems available from all the exercises id of the user.
	 *
	 * @param string $exercises_ids deprecated
	 * @param int $id_course
	 * @param string $otherlanguage
	 * @param int $user_id
	 * @param bool $unique_team
	 * @param bool $fallback_language
	 *
	 * @return array|bool
	 */
	public function checkForTandems(
		$exercises_ids,
		$id_course,
		$otherlanguage,
		$user_id,
		$unique_team = false,
		$fallback_language = false
	) {
//         if(strpos($exercises_ids,",") !== false){
//            $exs = explode(",",$exercises_ids);
//         }else
//         {
//           $exs[] = $exercises_ids;
//         }

		// Avoid pairing user with himself!
		$where = ' wru.id_user <> ' . ( (int) $user_id ) . ' ';
		if ( ! $unique_team ) {
			// Try to pair user with any other language user.
			$where .= ' AND wr.language = ' . $this->escapeString( $otherlanguage ) . ' ';
		}

//MODIFIED ******+ abertranb improvement performance only get data

		//1. if not lets check if there is someoe already created a tandem for us and is waiting.
		//check that the waiting room has been created 30 seconds before at most
		$sql    = 'SELECT id AS tandem_id
                  FROM tandem
                 WHERE id_course = ' . ( (int) $id_course ) . '
                   AND id_user_guest = ' . ( (int) $user_id ) . '
                   AND created >= DATE_SUB(NOW(), INTERVAL 30 SECOND)
                   AND is_finished = 0 ';
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$list = $this->obteComArray( $result );
			$this->logAction( $user_id, $id_course, - 1, 'waiting', $sql );
			$this->logAction( $user_id, $id_course, - 1, 'waiting_ok', json_encode( $list ) );

			return $list;
		}

		//2. lets see if there anyone waiting that we can do a tandem with
		//check that the waiting room has been created 10 seconds before at most
		$sql    = 'SELECT wr.*,
                       wru.id_user AS guest_user_id,
                       wru.user_agent AS user_agent
                  FROM waiting_room AS wr
            INNER JOIN waiting_room_user AS wru ON wru.id_waiting_room = wr.id
                 WHERE ' . $where . '
                   AND wr.id_course = ' . ( (int) $id_course ) . '
                   AND wru.created >= DATE_SUB(NOW(), INTERVAL 10 SECOND)
              ORDER BY wru.timestamp, RAND()
                 LIMIT 1 ';
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$list = $this->obteComArray( $result );
			$this->logAction( $user_id, $id_course, - 1, 'waiting', $sql );
			$this->logAction( $user_id, $id_course, - 1, 'waiting_ok', json_encode( $list ) );

			return $list;
		}

		if ( $fallback_language ) {
			return $this->checkForTandems( $exercises_ids, $id_course, $otherlanguage, $user_id, true, false );
		}

		return false;

// ******* ORIGINAL
//         //lets see if there anyone waiting that we can do a tandem with
//         foreach($exs as $id_ex){
//            $sql = "select wr.*,wru.id_user as guest_user_id, wru.user_agent as user_agent from waiting_room as wr
//                    inner join waiting_room_user as wru on wru.id_waiting_room = wr.id
//             where ".$where."
//             and wr.id_course ='".$id_course."'
//             and wr.id_exercise= '".$id_ex."'
//             and wru.created >= DATE_SUB(NOW(), INTERVAL 30 SECOND) order by wru.created asc ";  //check the wr has been created 30 seconds before
//            $result = $this->consulta($sql);
//            if ($this->numResultats($result) > 0) {
//                return $this->obteComArray($result);
//            }
//        }
//
//        //if not lets check if there is someoe already created a tandem for us and is waiting.
//         foreach($exs as $id_ex){
//            $sql = "select id as tandem_id from tandem where id_exercise ='".$id_ex."' and id_course='".$id_course."'
//                    and id_user_guest ='".$user_id."'
//                    and created >= DATE_SUB(NOW(), INTERVAL 30 SECOND)";
//
//           $result = $this->consulta($sql);
//            if ($this->numResultats($result) > 0) {
//               return  $this->obteComArray($result);
//
//             }
//
//         }
//
//        return false;
	}

	/**
	 * Here we update the waiting timestamp
	 *
	 * @param $id_user
	 *
	 * @return bool|mysqli_result|string
	 */
	public function updateMyWaitingTime( $id_user ) {
		$sql = 'update waiting_room_user set  created = NOW() where
        id_user = ' . $this->escapeString( $id_user ); //Don't care about course

		return $this->consulta( $sql );
	}

	/**
	 * Here we delete the old waiting room
	 *
	 * @param $id_user
	 *
	 * @return bool|mysqli_result|string
	 */
	public function cleanMyWaitingRoom( $id_user ) {

		$sql = 'delete from waiting_room_user set  created = NOW() where
        id_user = ' . $this->escapeString( $id_user ); //Don't care about course
		$this->debugMessage( "8 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sql );

		return $this->consulta( $sql );
	}

	/**
	 * Delete the user from the waiting rooms the first time they come
	 * TODO update waiting_room table aswell.
	 *
	 * @param $user_id
	 * @param $course_id
	 */
	public function deleteUserFromWaitingRooms( $user_id, $course_id ) {
		$sql = "delete from waiting_room_user where id_user ='" . $user_id . "'";
		$this->debugMessage( "9 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sql );
		$this->consulta( $sql );
	}

	/**
	 * We INSERT a user with a new exercise if don't exists or UPDATE the
	 * existing exercise increasing +1 in the comput global in the waiting_room table
	 *
	 * @param $language
	 * @param $courseID
	 * @param $exerciseID
	 * @param $idUser
	 * @param string $user_agent
	 * @param bool $unique_team
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function offer_exercise_autoassign(
		$language,
		$courseID,
		$exerciseID,
		$idUser,
		$user_agent = '',
		$unique_team = false
	) {
		$ok = false;
		if ( $user_agent == '' ) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		}

		$sqlDelete = 'delete from waiting_room where created < DATE_SUB(NOW(), INTERVAL 20 MINUTE)';
		$this->debugMessage( "10 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sqlDelete );
		$resultDelete = $this->consulta( $sqlDelete );

		$sqlDelete = 'delete from waiting_room where number_user_waiting = 0 and id_course = ' . $courseID . ' and id_exercise = ' . $exerciseID;
		$this->debugMessage( "10 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . $sqlDelete );
		$resultDelete = $this->consulta( $sqlDelete );

		$sqlSelect       = 'select number_user_waiting, id from waiting_room where id_course = ' . $courseID . ' and id_exercise = ' . $exerciseID . ( $unique_team ? '' : ' and language="' . $language . '"' );
		$resultSelect    = $this->consulta( $sqlSelect );
		$waiting_room_id = - 1;

		if ( $this->numResultats( $resultSelect ) > 0 ) {

			$resultSelect = $this->obteComArray( $resultSelect );

			$waiting_room_id = $resultSelect[0]['id'];

			$sql = 'UPDATE waiting_room SET number_user_waiting = (select count(*)+1 from waiting_room_user where id_waiting_room=waiting_room.id and id_user!=' . $idUser . ') WHERE id = ' . $waiting_room_id . ' and id_course = ' . $courseID . ' and id_exercise = ' . $exerciseID;
			$ok  = $this->consulta( $sql );

		} else {
			$sqlInsert = 'INSERT INTO waiting_room (language, id_course, id_exercise,number_user_waiting,created) VALUES (' . $this->escapeString( $language ) . ',' . $courseID . ',' . $exerciseID . ',1,now())';
			$ok        = $this->consulta( $sqlInsert );
			if ( $ok ) {

				$waiting_room_id = $this->getLastInsertedId();
			}

		}
		if ( $ok ) {
			$this->insertUserIntoWaitingRoom( $waiting_room_id, $language, $courseID, $exerciseID, $idUser,
				$user_agent );
		}

		return $ok;
	}

	/**
	 * Lets delete a user_id from all the waiting rooms and copy this data to the history tables
	 *
	 * @param $user_id
	 * @param $tandem_id
	 */
	public function deleteFromWaitingRoom( $user_id, $tandem_id ) {

		$resultSelect = $this->consulta( "select * from waiting_room_user where id_user =" . $user_id );
		if ( $this->numResultats( $resultSelect ) > 0 ) {
			$resultSelect = $this->obteComArray( $resultSelect );
			foreach ( $resultSelect as $key ) {
				//insert into waiting_room_user_history
				$type = $tandem_id > 0 ? 'assigned' : 'give_up';
				$a    = $this->consulta( "insert into waiting_room_user_history (id_waiting_room,id_user,status,id_tandem,user_agent, created,created_history,timestamp)
                    values('" . $key['id_waiting_room'] . "','" . $key['id_user'] . "','" . $type . "','" . $tandem_id . "'," . $this->escapeString( $key['user_agent'] ) . ",'" . $key['created'] . "',NOW(),'" . $key['timestamp'] . "') " );
				if ( $this->getAffectedRows() > 0 ) {
					//once we have copied it to the history , we delete it.
					$e = $this->consulta( "delete from waiting_room_user where id =" . $key['id'] );
					$this->debugMessage( "11 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . "delete from waiting_room_user where id =" . $key['id'] );
					if ( $this->getAffectedRows() > 0 ) {
						//now lets backup the waiting_room table
						$resultSelect2 = $this->consulta( "select * from waiting_room where id=" . $key['id_waiting_room'] );
						if ( $this->numResultats( $resultSelect2 ) > 0 ) {
							$res = $this->obteComArray( $resultSelect2 );
							if ( $res[0]['number_user_waiting'] == 1 ) {
								$i = $this->consulta( "insert into waiting_room_history(id_waiting_room,language,id_course,id_exercise,number_user_waiting,created,created_history)
                                                        values('" . $res[0]['id'] . "','" . $res[0]['language'] . "','" . $res[0]['id_course'] . "','" . $res[0]['id_exercise'] . "','1','" . $res[0]['created'] . "',NOW()) " );
								if ( $this->getAffectedRows() > 0 ) {
									$e = $this->consulta( "delete from waiting_room where id =" . $res[0]['id'] );
									$this->debugMessage( "12 " . $_SESSION[ CURRENT_USER ]->fullname . " DELETE " . "delete from waiting_room where id =" . $res[0]['id'] );
								}
							} else {
								$e = $this->consulta( "update waiting_room set number_user_waiting = number_user_waiting-1 where id =" . $res[0]['id'] );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * When we find someone to make a tandem, we create the tandem room here and return the id
	 *
	 * @param $response
	 * @param $user_id
	 * @param $id_resource_lti
	 * @param string $user_agent
	 * @param boolean $fallback_language
	 *
	 * @return bool|resource
	 */
	public function createTandemFromWaiting(
		$response,
		$user_id,
		$id_resource_lti,
		$user_agent = "",
		$fallback_language = false
	) {

		$tandem_id = $this->checkForOpenTandemRooms( $user_id, $response['id_exercise'], $response['id_course'],
			$response['guest_user_id'] );
		//if the tandem was already created by the other user, then we are the guests.
		if ( ! empty( $tandem_id ) ) {
			return $tandem_id;
		} else {
			//the tandem is not yet created, lets created it and we will be he host.
			//try to get the not done exercise for 2 users
			$id_exercise     = $response['id_exercise'];
			$id_course       = $response['id_course'];
			$new_id_exercise = $this->intersectExercisesNotDone( $id_course, $user_id, $response['guest_user_id'] );
			if ( $new_id_exercise !== false ) {
				$id_exercise = $new_id_exercise;
			}
			if ( $fallback_language ) {
				// Then check if have to use Spanish or English
				$id_exercise = $this->get_id_exercise_by_fallback_language( $id_exercise, $id_course, $user_id,
					$response['guest_user_id'] );
			}

			//We check again to avoid issues because take some time make the intersect
			$tandem_id = $this->checkForOpenTandemRooms( $user_id, $response['id_exercise'], $response['id_course'],
				$response['guest_user_id'] );
			//if the tandem was already created by the other user, then we are the guests.
			if ( empty( $tandem_id ) ) {
				$tandem_id = $this->register_tandem( $id_exercise, $id_course, $id_resource_lti, $user_id,
					$response['guest_user_id'], "", $user_agent );
				$this->update_user_guest_tandem( $tandem_id, $response['user_agent'] );
			}

			return $tandem_id;
		}
	}

	/**
	 * @param $id_exercise
	 * @param $id_course
	 * @param $user_id
	 * @param $guest_user_id
	 *
	 * @return mixed
	 */
	private function get_id_exercise_by_fallback_language( $id_exercise, $id_course, $user_id, $guest_user_id ) {

		$language = $this->has_distinct_language( $id_course, $user_id, $guest_user_id );
		if ( $language ) {
			$extra_week = $this->get_week_filter( isset( $_SESSION[ WEEK ] ) ? $_SESSION[ WEEK ] : '', $id_course );

			$sql    = 'select id_exercise from course_exercise where id_course = ' . $id_course . ' ' . $extra_week . ' ' .
			          ' AND lang = ' . $this->escapeString( $language ) . ' ORDER by rand() limit 0,1';
			$result = $this->consulta( $sql );

			if ( $this->numResultats( $result ) > 0 ) {
				$ids_exercise = array_values( $this->obteComArray( $result ) );
				$id_exercise  = $ids_exercise[0]['id_exercise'];
			}
		}

		return $id_exercise;
	}

	/**
	 * Check if user has distinct languages or not
	 *
	 * @param $id_course
	 * @param $user_id
	 * @param $guest_user_id
	 *
	 * @return bool
	 */
	private function has_distinct_language( $id_course, $user_id, $guest_user_id ) {
		$language = false;
		if ( $user_id != $guest_user_id ) {
			$sql    = 'select distinct language from user_course where id_course = ' . $id_course . ' and id_user in (' . $user_id . ',' . $guest_user_id . ')';
			$result = $this->consulta( $sql );

			if ( $this->numResultats( $result ) == 1 ) {
				$langs    = array_values( $this->obteComArray( $result ) );
				$language = $langs[0]['language'];
			}
		}

		return $language;
	}

	private function get_week_filter( $week_param, $id_course ) {
		$week = 0;
		if ( ! empty( $week_param ) || $week_param === 0 ) {
			$week = $week_param;
		} else {
			$sql    = 'SELECT  max(week) as week from course_exercise where id_course = ' . $id_course;
			$result = $this->consulta( $sql );

			if ( $this->numResultats( $result ) > 0 ) {
				$max_week = array_values( $this->obteComArray( $result ) );
				$week     = $max_week[0]['week'];
				if ( ! empty( $_SESSION[ PREVIOUS_WEEK ] ) ) {
					if ( $week > 0 ) {
						$week --;
					}
				}
			}
		}
		$extra_week = $week > 0 ? ' and  week = ' . $week . ' ' : '';

		return $extra_week;
	}

	/**
	 * Get all the exercices of the week that the user hasnt finished yet.
	 *
	 * @param $id_course
	 * @param $user_id
	 * @param $user_guest_id
	 *
	 * @return bool
	 */
	private function intersectExercisesNotDone( $id_course, $user_id, $user_guest_id ) {

		//if they passed the custom parameter WEEK with the LTIcall , then we use that week, if not then we use the max week there is.
		$extra_week = $this->get_week_filter( isset( $_SESSION[ WEEK ] ) ? $_SESSION[ WEEK ] : '', $id_course );
		//1st. search for exercises that any of this users had done
		$sql         = 'select id_exercise from course_exercise where id_course = ' . $id_course . ' ' . $extra_week . ' and id_exercise not in
                (
                SELECT id_exercise from tandem where
                    id_course = ' . $id_course . ' and 
                    (id_user_guest = ' . $user_id . ' or id_user_host = ' . $user_id . '
                    OR
                    id_user_guest = ' . $user_guest_id . ' or id_user_host = ' . $user_guest_id . ')
                ) AND (lang is null or lang = \'all\') order by rand() limit 0,1';
		$result      = $this->consulta( $sql );
		$exercise_id = false;

		if ( $this->numResultats( $result ) > 0 ) {
			$ids_exercise = array_values( $this->obteComArray( $result ) );
			$exercise_id  = $ids_exercise[0]['id_exercise'];
		} else {
			//2nd. Search any exercise that user done less
			$sql         = 'SELECT tandem.id_exercise, count(*) from tandem
inner join course_exercise on course_exercise.id_exercise=tandem.id_exercise and course_exercise.id_course=tandem.id_course  
 where
                    tandem.id_course = ' . $id_course . ' ' . $extra_week . ' 
                    and (tandem.id_user_guest = ' . $user_id . ' or tandem.id_user_host = ' . $user_id . '
                    OR
                    tandem.id_user_guest = ' . $user_guest_id . ' or tandem.id_user_host = ' . $user_guest_id . ')
                    AND (course_exercise.lang is null or course_exercise.lang = \'all\')
                    group by tandem.id_exercise order by count(*), rand() 
                    limit 0,1';
			$result      = $this->consulta( $sql );
			$exercise_id = false;

			if ( $this->numResultats( $result ) > 0 ) {
				$ids_exercise = array_values( $this->obteComArray( $result ) );
				$exercise_id  = $ids_exercise[0]['id_exercise'];
			}
		}

		return $exercise_id;
	}

	/**
	 * Here we check if there are any tandems available from all the exercises id of the user.
	 *
	 * @param $user_id
	 * @param $exercises_ids
	 * @param $id_course
	 *
	 * @return bool
	 */
	public function checkForInvitedTandems( $user_id, $exercises_ids, $id_course ) {

		if ( strlen( $exercises_ids ) > 0 ) {
			$sql = "select * from tandem where
               id_exercise in ('" . $exercises_ids . "') "
			       . " and id_course = " . $this->escapeString( $id_course )
			       . " and created >= DATE_SUB(NOW(), INTERVAL 30 SECOND) "//check the wr has been created 30 seconds before
			       . " and (id_user_guest = " . $this->escapeString( $user_id ) . " OR id_user_host = " . $this->escapeString( $user_id ) . ")";

			$result = $this->consulta( $sql );
			if ( $this->numResultats( $result ) > 0 ) {
				$arr = $this->obteComArray( $result );

				return $arr[0]['id'];
			}
		}

		return false;
	}

	/**
	 * @param $user_id
	 * @param $id_exercise
	 * @param $id_course
	 * @param $guest_user_id
	 *
	 * @return bool
	 */
	public function checkForOpenTandemRooms( $user_id, $id_exercise, $id_course, $guest_user_id ) {

		$sql = "select id from tandem where id_exercise = " . $id_exercise . "
            and id_course = " . $id_course . "
            and (id_user_host = " . $guest_user_id . " and id_user_guest = " . $user_id . ")
            and created >= DATE_SUB(NOW(),INTERVAL 30 SECOND)"; //chekc if has 30 seconds if not can be a reload

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );

			return $result[0]['id'];
		}

		return false;
	}

	/**
	 * Creates a new externale tandem
	 *
	 * @param $id_tandem
	 * @param $id_external_tool
	 * @param $id_user
	 * @param $language
	 * @param $id_partner
	 * @param $partner_language
	 *
	 * @return bool|string
	 */
	public function createFeedbackTandem(
		$id_tandem,
		$id_external_tool,
		$id_user,
		$language,
		$id_partner,
		$partner_language
	) {
		//1st. check if it is necessary
		$result = $this->consulta( "select id from feedback_tandem where id_tandem =" . $this->escapeString( $id_tandem ) . "
                         and id_user =" . $this->escapeString( $id_user ) . "
                         and language=" . $this->escapeString( $language ) . "
                         and id_partner =" . $this->escapeString( $id_partner ) . "
                         and partner_language=" . $this->escapeString( $partner_language ) );
		$id     = false;
		if ( $this->numResultats( $result ) > 0 ) {
			$r  = $this->obteComArray( $result );
			$id = $r[0]['id'];
		} else {
			//insert
			$sql    = 'INSERT INTO feedback_tandem (id_tandem, id_external_tool, id_user, language, id_partner, partner_language, created)
                        VALUES (' . $this->escapeString( $id_tandem ) . ',' . $this->escapeString( $id_external_tool ) . ',' . $this->escapeString( $id_user ) . ',' .
			          $this->escapeString( $language ) . ',' . $this->escapeString( $id_partner ) . ', ' . $this->escapeString( $partner_language ) . ', now())';
			$result = $this->consulta( $sql );
			if ( $result ) {
				$id = $this->get_last_inserted_id();
			}

		}

		return $id;
	}

	/**
	 * Get the external tool id
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function getFeedbackExternalIdTool( $id ) {
		//1st. check if it is necessary
		$result           = $this->consulta( "select id_external_tool from feedback_tandem where id =" . $this->escapeString( $id ) );
		$id_external_tool = false;
		if ( $this->numResultats( $result ) > 0 ) {
			$r                = $this->obteComArray( $result );
			$id_external_tool = $r[0]['id_external_tool'];
		}

		return $id_external_tool;
	}

	/**
	 * Updtes the id_external_tool based on id_tandem
	 *
	 * @param $id_tandem
	 * @param $id_external_tool
	 * @param $end_external_service
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function updateExternalToolFeedbackTandemByTandemId( $id_tandem, $id_external_tool, $end_external_service ) {

		return $this->consulta( "update  feedback_tandem set id_external_tool=" . $this->escapeString( $id_external_tool ) .
		                        ", end_external_service=" . $this->escapeString( $end_external_service ) .
		                        " where id_tandem =" . $this->escapeString( $id_tandem ) );

	}

	/**
	 * Insert into history of videoconnection
	 *
	 * @param $id_tandem
	 * @param $user_id
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function insertUserAcceptedConnection( $id_tandem, $user_id ) {

		return $this->consulta( "insert into user_tandem_tandem_videoconnection (id_tandem, id_user, created)   " .
		                        " values (" . $this->escapeString( $id_tandem ) . ", " . $this->escapeString( $user_id ) . ", now())" );

	}


	/**
	 * This functions allows to check if ther partner accepted connection and me not, then raise a message
	 *
	 * @param $id_tandem
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function userAcceptedVideoConnection( $id_tandem, $user_id ) {

		$sql    = "select * from user_tandem_tandem_videoconnection 
          where id_tandem = " . $this->escapeString( $id_tandem );
		$result = $this->consulta( $sql . ' and id_user =' . $this->escapeString( $user_id ) );

		//Check if partner accepted
		return $this->numResultats( $result ) > 0;
	}

	/**
	 * This functions allows to check if ther partner accepted connection and me not, then raise a message
	 *
	 * @param $id_tandem
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function myPartnerAcceptedConnectionAndMeNot( $id_tandem, $user_id ) {
		$myPartnerAcceptedConnectionAndMeNot = false;
		$sql                                 = "select * from user_tandem_tandem_videoconnection 
          where id_tandem = " . $this->escapeString( $id_tandem );
		$result                              = $this->consulta( $sql . ' and id_user !=' . $this->escapeString( $user_id ) );

		//Check if partner accepted
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->consulta( $sql . ' and id_user =' . $this->escapeString( $user_id ) );
			if ( $this->numResultats( $result ) == 0 ) {
				//if the current user doesn't accept return
				$myPartnerAcceptedConnectionAndMeNot = true;
			}
		}

		return $myPartnerAcceptedConnectionAndMeNot;
	}

	/**
	 * This functions allows to check if ther partner accepted connection and me not, then raise a message
	 *
	 * @param $id_tandem
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function meAcceptedConnectionAndMyPartnerNot( $id_tandem, $user_id ) {
		$iAcceptedMyPartnerNot = false;
		$sql                   = "select * from user_tandem_tandem_videoconnection 
          where id_tandem = " . $this->escapeString( $id_tandem );
		$result                = $this->consulta( $sql . ' and id_user =' . $this->escapeString( $user_id ) );

		//Check if partner accepted
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->consulta( $sql . ' and id_user !=' . $this->escapeString( $user_id ) );
			if ( $this->numResultats( $result ) == 0 ) {
				//if the current user doesn't accept return
				$iAcceptedMyPartnerNot = true;
			}
		}

		return $iAcceptedMyPartnerNot;
	}

	/**
	 * Updtes the external_video_url based on id_tandem
	 *
	 * @param $id_tandem
	 * @param $external_video_url
	 *
	 * @return bool|mysqli_result|string [type]                   [description]
	 */
	public function updateDownloadVideoUrlFeedbackTandemByTandemId( $id_tandem, $external_video_url ) {

		return $this->consulta( "update  feedback_tandem set external_video_url=" . $this->escapeString( $external_video_url ) .
		                        " where id_tandem =" . $this->escapeString( $id_tandem ) );

	}

	/**
	 * Creates and can update!
	 *
	 * @param int $id_feedback feedback id
	 * @param string $feedback_form serialized feedback data
	 *
	 * @return bool
	 */
	public function createFeedbackTandemDetail( $id_feedback, $feedback_form ) {
		$sql = 'INSERT INTO feedback_tandem_form (id_feedback_tandem, feedback_form) VALUES ('
		       . $this->escapeString( $id_feedback ) . ',' . $this->escapeString( $feedback_form ) . ')';

		return $this->consulta( $sql );
	}

	/**
	 * Adds the rating partner
	 *
	 * @param $id_feedback
	 * @param $rating_partner_feedback_form
	 *
	 * @return bool|mysqli_result|string [type]                               [description]
	 */
	public function updateRatingPartnerFeedbackTandemDetail( $id_feedback, $rating_partner_feedback_form ) {

		return $this->consulta( "update  feedback_tandem_form set rating_partner_feedback_form=" . $this->escapeString( serialize( $rating_partner_feedback_form ) ) .
		                        " where id_feedback_tandem =" . $this->escapeString( $id_feedback ) );

	}

	/**
	 * Returns the feedback details or false if is not found
	 *
	 * @param $id_feedback
	 *
	 * @return bool|stdClass
	 */
	public function getFeedbackDetails( $id_feedback ) {

		//1st. check if it is necessary
		$result   = $this->consulta( "select feedback_tandem.*, tandem.created as tandem_created, tandem.is_finished as tandem_is_finished,
            tandem.finalized as tandem_finalized,
            user_host.fullname as user_host_fullname, user_host.email as user_host_email,
            user_guest.fullname as user_guest_fullname, user_guest.email as user_guest_email,
            feedback_tandem_form.* from feedback_tandem
            inner join tandem on tandem.id=feedback_tandem.id_tandem
            inner join user as user_host on user_host.id=feedback_tandem.id_user
            inner join user as user_guest on user_guest.id=feedback_tandem.id_partner
            left join feedback_tandem_form on feedback_tandem_form.id_feedback_tandem=feedback_tandem.id
            where feedback_tandem.id =" . $this->escapeString( $id_feedback ) );
		$feedback = false;
		if ( $this->numResultats( $result ) > 0 ) {
			$r                              = $this->obteComArray( $result );
			$feedback                       = new stdClass();
			$feedback->id                   = $r[0]['id'];
			$feedback->id_tandem            = $r[0]['id_tandem'];
			$feedback->id_external_tool     = $r[0]['id_external_tool'];
			$feedback->end_external_service = $r[0]['end_external_service'];
			$feedback->external_video_url   = $r[0]['external_video_url'];
			$feedback->id_user              = $r[0]['id_user'];
			$feedback->language             = $r[0]['language'];
			$feedback->id_partner           = $r[0]['id_partner'];
			$feedback->partner_language     = $r[0]['partner_language'];
			$feedback->created              = $r[0]['created'];
			$feedback->tandem_created       = $r[0]['tandem_created'];
			$feedback->tandem_is_finished   = $r[0]['tandem_is_finished'];
			$feedback->tandem_finalized     = $r[0]['tandem_finalized'];
			$feedback_form                  = $r[0]['feedback_form'];
			if ( $feedback_form && strlen( $feedback_form ) > 0 ) {
				$feedback->feedback_form = unserialize( $feedback_form );
			} else {
				$feedback->feedback_form = false;
			}
			$rating_partner_feedback_form = $r[0]['rating_partner_feedback_form'];
			if ( $rating_partner_feedback_form && strlen( $rating_partner_feedback_form ) > 0 ) {
				$feedback->rating_partner_feedback_form = unserialize( $rating_partner_feedback_form );
			} else {
				$feedback->rating_partner_feedback_form = false;
			}
		}

		return $feedback;
	}

	/**
	 * Check if the partner has sent the feedback
	 *
	 * @param $tandem_id
	 * @param $feedback_id
	 *
	 * @return bool
	 */
	public function checkPartnerFeedback( $tandem_id, $feedback_id ) {
		$sqlQuery = 'SELECT FT.id, FTF.feedback_form, FTF.rating_partner_feedback_form
                     FROM feedback_tandem AS FT
                     INNER JOIN feedback_tandem_form AS FTF ON FTF.id_feedback_tandem = FT.id
                     WHERE FT.id_tandem = ' . $this->escapeString( $tandem_id ) . "
                     AND FTF.feedback_form != '' ";
		$result   = $this->consulta( $sqlQuery );

		if ( $this->numResultats( $result ) == 2 ) {
			$res = $this->obteComArray( $result );

			return ( $res[0]['id'] == $feedback_id ) ? $res[1]['feedback_form'] : $res[0]['feedback_form'];
		}

		return false;
	}

	/**
	 * Get all the user submitted feedbacks and its stats
	 *
	 * @param $user_id
	 * @param $id_course
	 * @param int $showFeedback
	 * @param int $finishedTandem
	 * @param string $dateStart
	 * @param string $dateEnd
	 * @param bool $noTeamsMode
	 * @param int $tandemType
	 *
	 * @return array
	 */
	public function getAllUserFeedbacks(
		$user_id,
		$id_course,
		$showFeedback = - 1,
		$finishedTandem = - 1,
		$dateStart = '',
		$dateEnd = '',
		$noTeamsMode = false,
		$tandemType = - 1
	) {
		$join_type               = 'left';
		$condition_feedback_form = '';
		switch ( $showFeedback ) {
			case 1: // Finished
				$join_type = 'inner';
				break;
			case 2: // Pending
				$condition_feedback_form = ' AND FTF.feedback_form IS NULL ';
				break;
		}

		$extraSQL = '';
		if ( strlen( $dateStart ) > 0 ) {
			$extraSQL .= " AND T.created >= '" . $dateStart . " 00:00:00' ";
		}
		if ( strlen( $dateEnd ) > 0 ) {
			$extraSQL .= " AND T.created <= '" . $dateEnd . " 23:59:59' ";
		}

		$typeSql = '';
		if ( $tandemType === 1 ) {
			$typeSql .= " HAVING TandemResult = 'Roulette Tandem' ";
		} else {
			if ( $tandemType === 2 ) {
				$typeSql .= " HAVING TandemResult = 'YouChoose Tandem' ";
			}
		}

		$sqlQuery = 'SELECT FT.id,
                            FT.id_tandem,
                            FT.id_external_tool,
                            FT.end_external_service,
                            FT.external_video_url,
                            FT.id_user,
                            FT.language,
                            FT.id_partner,
                            FT.partner_language,
       						(select language from feedback_tandem where feedback_tandem.id_tandem = FT.id_tandem and feedback_tandem.id_user != FT.id_user limit 0,1) as other_language,
                            FT.created,
                            FTF.feedback_form, 
                            E.name AS exercise,
                            U.fullname, 
                            U2.fullname AS partner_fullname,
                            CASE WHEN (SELECT COUNT(*) FROM waiting_room_user_history WHERE waiting_room_user_history.id_tandem = T.id) > 0
                                THEN \'Roulette Tandem\'
                                ELSE \'YouChoose Tandem\'
                            END AS TandemResult
                     FROM feedback_tandem AS FT
                     ' . $join_type . ' JOIN feedback_tandem_form AS FTF ON FTF.id_feedback_tandem = FT.id
                     INNER JOIN tandem as T ON T.id = FT.id_tandem
                     LEFT JOIN exercise E ON E.id=T.id_exercise
                     INNER JOIN `user` AS U ON U.id = FT.id_user
                     INNER JOIN `user` AS U2 ON U2.id = FT.id_partner
                     WHERE T.id_course = ' . $this->escapeString( $id_course ) . '
                     ' . $condition_feedback_form . '
                     ' . ( $user_id > 0 ? ' AND FT.id_user = ' . $this->escapeString( $user_id ) . '' : '' ) . '
                     ' . $extraSQL . '
                     ' . $typeSql;

		$result       = $this->consulta( $sqlQuery );
		$resultsCount = $this->numResultats( $result );

		if ( $resultsCount > 0 ) {
			$feedback_tandem = $this->obteComArray( $result );
			$return          = array();
			foreach ( $feedback_tandem as $ft ) {
				// Calculate total tandem duration and sub-tandems duration
				$tandemDurations = $this->getUserTandemDurations( $ft['id_user'], $ft['id_tandem'] );
				$seconds         = isset( $tandemDurations[0]['total_time'] ) ? (int) $tandemDurations[0]['total_time'] : 0;
				switch ( $finishedTandem ) {
					case 1: // Finished
						if ( $seconds < TIME_TO_FAILED_TANDEM ) {
							continue 2;
						}
						break;
					case 2: // Unfinished
						if ( $seconds > TIME_TO_FAILED_TANDEM ) {
							continue 2;
						}
						break;
				}
				$this->addFormattedTandemAndSubtandemsDurations( $seconds, $ft );
				$this->addSkillGrades( $noTeamsMode, $ft );

				$return[] = $ft;
			}

			return $return;
		}

		return array();
	}

	/**
	 * @param $user_id
	 * @param $tandem_id
	 *
	 * @return array|bool
	 */
	public function getUserTandemDurations( $user_id, $tandem_id ) {
		$sql    = 'SELECT * FROM user_tandem
                WHERE id_tandem = ' . $tandem_id . '
                AND id_user = ' . $user_id;
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * @param $user_id
	 * @param $tandem_id
	 *
	 * @return array|bool
	 */
	public function getUserTandemTasksDurations( $user_id, $tandem_id ) {
		$sql    = " select * from user_tandem_task where id_tandem = " . $tandem_id . " and id_user = " . $user_id;
		$result = $this->consulta( $sql );

		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * @param $seconds
	 *
	 * @return string
	 */
	public function minutes( $seconds ) {
		return sprintf( "%02.2d:%02.2d", floor( $seconds / 60 ), $seconds % 60 );
	}

	/**
	 * @param $seconds
	 *
	 * @return false|string
	 */
	public function time_format( $seconds ) {
		return gmdate( "H:i:s", $seconds );
	}

	/**
	 * @param $user_id
	 *
	 * @return string
	 */
	public function getUserName( $user_id ) {
		if ( $user_id > 0 ) {
			$sql    = " select fullname from user where id = " . $this->escapeString( $user_id );
			$result = $this->consulta( $sql );
			if ( $this->numResultats( $result ) > 0 ) {
				$names = $this->obteComArray( $result );

				return $names[0]['fullname'];
			}
		}

		return '';
	}

	/**
	 * Lets update or add the information to the user_ranking table when they filled the feedbacks.
	 */
	/*function insertRankingData($user_id,$course_id,$language,$id_tandem){

            $sql = "select * from user_tandem where id_tandem = ".$this->escapeString($id_tandem)." and id_user = ".$this->escapeString($user_id)." ";
            $result = $this->consulta($sql);
            if ($this->numResultats($result) > 0){
                $data =  $this->obteComArray($result);
                //now we have the tandem
                $result =  $this->consulta("select * from user_ranking where id_user = ".$this->escapeString($user_id)."  and id_course = ".$this->escapeString($course_id)." and language =".$this->escapeString($language)."");
                if ($this->numResultats($result) > 0){
                    //we already this user in the ranking table, lets update the time.
                    $this->consulta("update user_ranking set total_time = total_time + ".$data[0]['total_time']." where id_user = ".$this->escapeString($user_id)." and id_course = ".$this->escapeString($course_id)." and language =".$this->escapeString($language)."");
                }else
                    $this->consulta("insert into user_ranking (id_user,id_course,language,total_time)
                                     values (".$this->escapeString($user_id).",".$this->escapeString($course_id).",".$this->escapeString($language).",'".$data[0]['total_time']."')");
            }
    }*/

	/**
	 * Get ranking by language
	 *
	 * @param int|string $course_id
	 * @param bool|string $lang
	 *
	 * @return array
	 */
	public function getRankingByLang( $course_id, $lang = false ) {
		$r   = array();
		$sql = 'SELECT * FROM user_ranking AS UR
                INNER JOIN user_course AS UC
                ON UC.id_user = UR.user_id 
                AND UC.is_instructor = 0
                AND UC.id_course = UR.course_id
                WHERE UR.course_id = ' . $this->escapeString( $course_id ) . '
                ' . ( $lang ? ' AND UR.lang = ' . $this->escapeString( $lang ) . ' ' : '' ) . '
                AND UC.is_instructor = 0
                ORDER BY points DESC ';

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$data = $this->obteComArray( $result );
			foreach ( $data as $key => $value ) {
				$r[ $value['user_id'] ]['user']              = $this->getUserName( $value['user_id'] );
				$r[ $value['user_id'] ]['points']            = $value['points'];
				$r[ $value['user_id'] ]['total_time']        = $value['total_time'];
				$r[ $value['user_id'] ]['number_of_tandems'] = $value['number_of_tandems'];
				$r[ $value['user_id'] ]['fluency']           = $value['fluency'];
				$r[ $value['user_id'] ]['accuracy']          = $value['accuracy'];
				$r[ $value['user_id'] ]['overall_grade']     = $value['overall_grade'];
			}
		}

		return $r;
	}

	/**
	 * Gets all the users total time ranking for a specific course
	 *
	 * @param $course_id
	 * @param bool $unique_team
	 *
	 * @return array
	 */
	public function getUsersRanking( $course_id, $unique_team = false ) {
		$r = array();
		if ( ! $unique_team ) {
			$r['en'] = $this->getRankingByLang( $course_id, 'en_US' );
			$r['es'] = $this->getRankingByLang( $course_id, 'es_ES' );
		} else {
			$r['all_lang'] = $this->getRankingByLang( $course_id );
		}

		return $r;
	}

	/**
	 * This function gets the position in the ranking of someone
	 *
	 * @param $user_id
	 * @param $language
	 * @param $course_id
	 * @param bool $unique_team
	 *
	 * @return int
	 */
	public function getUserRankingPosition( $user_id, $language, $course_id, $unique_team = false ) {
		$sql = 'SELECT user_id, lang, points, rank
                    FROM (
                        SELECT ur.*, @rank := @rank + 1 AS rank
                        FROM user_ranking AS ur
                        INNER JOIN user_course AS uc
                        ON uc.id_user = ur.user_id
                        AND uc.is_instructor = 0
                        AND uc.id_course = ur.course_id
                        WHERE ur.course_id = ' . $this->escapeString( $course_id ) . '
                        ' . ( $unique_team ? '' : 'AND ur.lang = ' . $this->escapeString( $language ) ) . '
                        ORDER BY ur.points DESC
                    ) ranking
                    WHERE user_id = ' . $this->escapeString( $user_id );

//            $sql = 'SELECT user_ranking_general.user_id, user_ranking_general.lang, user_ranking_general.points, (
//                        SELECT COUNT(*)+1 FROM user_ranking AS user_ranking_pos
//                        INNER JOIN user_course
//                        ON
//                        AND user_course.id_course = user_ranking_pos.course_id
//                        AND
//                        WHERE user_ranking_pos.points > user_ranking_general.points
//                        AND user_ranking_pos.lang = user_ranking_general.lang
//                        AND user_ranking_pos.course_id = user_ranking_general.course_id
//                    ) AS `position`
//                    FROM user_ranking AS user_ranking_general
//                    WHERE user_ranking_general.course_id = ' . $this->escapeString($course_id) . '
//                    AND user_ranking_general.user_id = ' . $this->escapeString($user_id);
//             if (!$unique_team) {
//                 $sql.=" and lang=".$this->escapeString($language);
//             }

		$reset  = $this->consulta( 'SET @rank := 0;' ); // Reset rank counter variable.
		$result = $this->consulta( $sql );
		if ( $reset && $result && $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result )[0]['rank'];
		}

		return 0;
	}

	/**
	 * This function gets the position in the ranking of someone
	 *
	 * @param $user_id
	 * @param $course_id
	 *
	 * @return array|mixed
	 */
	public function getRankingUserData( $user_id, $course_id ) {
		$user_data = array();
		$sql       = "select * " .
		             "from user_ranking where course_id =" . $this->escapeString( $course_id ) . "
                and user_id = " . $this->escapeString( $user_id );

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$data      = $this->obteComArray( $result );
			$user_data = $data[0];
		}

		return $user_data;
	}

	/**
	 * Returns an array with all users
	 *
	 * @param $course_id
	 *
	 * @return array
	 */
	public function getAllUsers( $course_id ) {
		$result = $this->consulta( "select id,fullname from user as U
                                           inner join user_course as UC on UC.id_user = U.id
                                           where UC.id_course = " . $this->escapeString( $course_id ) . "
                                           order by U.fullname" );
		$data   = array();
		if ( $this->numResultats( $result ) > 0 ) {
			$data = $this->obteComArray( $result );
		}

		return $data;
	}

	/**
	 *  Activates a session to start a tandem
	 *
	 * @param $tandem_id
	 *
	 * @return bool|mysqli_result|string
	 */
	public function startTandemSession( $tandem_id ) {

		$result = $this->consulta( "select * from session where id_tandem =" . $this->escapeString( $tandem_id ) . " " );
		$data   = array();
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->updateTandemSession( $tandem_id, 1 );
		} else {
			return $this->consulta( "insert into session(id_tandem,status,created) values(" . $this->escapeString( $tandem_id ) . ",1,NOW()) " );
		}
	}

	/**
	 *  Set as available video session  a tandem (set a 2)
	 *
	 * @param $tandem_id
	 *
	 * @return bool|mysqli_result|string
	 */
	public function updateTandemSessionAvailable( $tandem_id ) {
		return $this->updateTandemSession( $tandem_id, 2 );
	}

	/**
	 *  Set as available video session  a tandem (set a 2)
	 *
	 * @param $tandem_id
	 *
	 * @return bool|mysqli_result|string
	 */
	public function updateTandemSessionNotAvailable( $tandem_id ) {
		return $this->updateTandemSession( $tandem_id, 0 );
	}

	/**
	 * Updates the tandem session
	 *
	 * @param $tandem_id
	 * @param $status
	 *
	 * @return bool|mysqli_result|string [type]            [description]
	 */
	private function updateTandemSession( $tandem_id, $status ) {
		return $this->consulta( "update session set status = " . $this->escapeString( $status ) . " where id_tandem = " . $this->escapeString( $tandem_id ) );
	}

	/**
	 * Check if a session has been activated to start the tandem
	 *
	 * @param $tandem_id
	 *
	 * @return bool
	 */
	public function checkTandemSession( $tandem_id ) {
		$result = $this->consulta( "select * from session where id_tandem =" . $this->escapeString( $tandem_id ) . " and status = 1" );
		$data   = array();
		if ( $this->numResultats( $result ) > 0 ) {
			$res = $this->obteComArray( $result );

			return $res[0]['status'] > 0;
		}

		return false;
	}

	/**
	 * Gets the user_portfolio_profile data
	 *
	 * @param $type
	 * @param $user_id
	 *
	 * @return bool|mixed
	 */
	public function getUserPortfolioProfile( $type, $user_id ) {

		$result = $this->consulta( "select * from user_portfolio_profile where user_id =" . $this->escapeString( $user_id ) . " and type =" . $this->escapeString( $type ) . " " );
		if ( $this->numResultats( $result ) > 0 ) {
			$data            = $this->obteComArray( $result );
			$data[0]['data'] = unserialize( $data[0]['data'] );

			return $data[0];
		}

		return false;

	}

	/**
	 * Save form user data
	 *
	 * @param $form_type
	 * @param $user_id
	 * @param $data
	 * @param $previousForm
	 * @param $portfolio_form_id
	 *
	 * @return mixed
	 */
	public function saveFormUserProfile( $form_type, $user_id, $data, $previousForm, $portfolio_form_id ) {
		//first lets make sure they dont already have filled this formulary
		if ( ! $previousForm || $portfolio_form_id < 0 ) {
			$this->consulta( "insert into user_portfolio_profile(user_id,data,type,created) values ('" . $user_id . "'," . $this->escapeString( $data ) . ",'" . $form_type . "',NOW())" );
			$previousForm['data'] = $data;
		} //if we have this value then we are updating
		else {
			$this->consulta( "update user_portfolio_profile set data =" . $this->escapeString( $data ) . " where id= " . $this->escapeString( $portfolio_form_id ) );
			$previousForm['data'] = $data;
		}

		return $previousForm;
	}

	/**
	 * Checks if the external tool video session is available
	 *
	 * @param $feedback_id
	 *
	 * @return array|bool
	 */
	public function checkExternalToolVideoSession( $feedback_id ) {

		$result = $this->consulta( "select * from feedback_tandem where id_tandem = id_external_tool and external_video_url IS NOT NULL and id =  " . $this->escapeString( $feedback_id ) . " " );
		if ( $this->numResultats( $result ) > 0 ) {

			return $this->obteComArray( $result );
		}

		return false;

	}

	/**
	 * Checks for waiting rooms that are older than the MAX_WAITING_TIME and we delete them.
	 */
	public function tandemMaxWaitingTime() {

		$sql    = "select * from waiting_room_user
            where created <= DATE_SUB(NOW(),INTERVAL " . MAX_WAITING_TIME . " MINUTE)"; //chekc if has 30 seconds if not can be a reload
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );
			foreach ( $result as $key => $value ) {
				$this->deleteFromWaitingRoom( $value['id_user'], '-1' );
			}
		}
	}

	/**
	 * Returns the current active amount of tandems
	 *
	 * @param $id_course
	 *
	 * @return int
	 */
	public function currentActiveTandems( $id_course ) {

		/*$sql= "Select count(id) as total from tandem
               where is_finished = 0 and finalized IS NULL and
               id_course =".$this->escapeString($id_course)." and
            created >= DATE_SUB(NOW(),INTERVAL 1 HOUR)";*/

		$sql = 'Select count(distinct tandem.id) as total from tandem
                    inner join user_tandem on user_tandem.id_tandem=tandem.id
                   where tandem.is_finished = 0 and tandem.finalized IS NULL and
                   tandem.id_course = ' . $this->escapeString( $id_course ) . ' and
                user_tandem.updated >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)';

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );

			return $result[0]['total'];
		}

		return 0;
	}

	/**
	 * Returns all the users waiting on the waiting_room for spanish and english
	 *
	 * @param $course_id
	 * @param string $language
	 * @param bool $unique_team
	 * @param bool $fallback_language
	 *
	 * @return int
	 */
	public function getUsersWaitingByLanguage(
		$course_id,
		$language = 'es_ES',
		$unique_team = false,
		$fallback_language = false
	) {

		$where = $unique_team ? '1=1' : ( 'wr.language=' . $this->escapeString( $language ) );
		$sql   = "select
            count(distinct wru.id_user) as total
            from waiting_room wr
            inner join waiting_room_user as wru on wru.id_waiting_room=wr.id and wru.created >= DATE_SUB(NOW(), INTERVAL 30 SECOND) " .  //check the wr has been created 30 seconds before";
		         "where " . $where . " and wr.id_course = " . $this->escapeString( $course_id );

		$result = $this->consulta( $sql );
		$total  = 0;
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );

			$total = $result[0]['total'];
		}

		if ( $total == 0 && $fallback_language ) {
			return $this->getUsersWaitingByLanguage( $course_id, $language, true, false );
		}

		return $total;

	}

	/**
	 * Set created to now in start tandem of portfolio
	 *
	 * @param [type] $id_tandem [description]
	 *
	 * @return bool
	 */
	public function setCreatedTandemToNow( $id_tandem ) {

		$sql    = 'update tandem set created = now() where  id = ' . $id_tandem;
		$result = $this->consulta( $sql );
		$sql    = 'update user_tandem set created = now() where  id_tandem = ' . $id_tandem;
		$result = $this->consulta( $sql );
		$sql    = 'update user_tandem_task set created = now() where  id_tandem = ' . $id_tandem;
		$result = $this->consulta( $sql );
		$sql    = 'update user_tandem_task_question set created = now() where  id_tandem = ' . $id_tandem;
		$result = $this->consulta( $sql );

		return true;
	}

	/**
	 * Return the number of tandems done by a specific date
	 *
	 * @param $date
	 * @param $course_id
	 *
	 * @return int
	 */
	public function getNumtandemsByDate( $date, $course_id ) {

		$sql    = "select count(*) as total from tandem where is_finished = 1  and date(created) =  " . $this->escapeString( $date ) . " and id_course = " . $this->escapeString( $course_id ) . " ";
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );

			return $result[0]['total'];
		}

		return 0;
	}

	/**
	 * Return the number of failed tandems, the ones that in total time have 5 seconds or less
	 * TODO Improve maybe do it in just 1 whole query
	 *
	 * @param $course_id
	 * @param bool $dateStart
	 * @param bool $dateEnd
	 *
	 * @return array
	 */
	public function getNumOfSuccessFailedTandems( $course_id, $dateStart = false, $dateEnd = false ) {

		$datesql = '';
		if ( $dateStart ) {
			$datesql = "and date(created) >= '" . $dateStart . "' ";
			if ( $dateEnd > $dateStart ) {
				$datesql .= "and date(created) <= '" . $dateEnd . "' ";
			}
		}
		$sql = "select id from tandem where id_course = " . $this->escapeString( $course_id ) . " " . $datesql;

		$result  = $this->consulta( $sql );
		$failed  = 0;
		$success = 0;

		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );
			foreach ( $result as $key => $value ) {
				$result2 = $this->consulta( "select avg(total_time) as total from user_tandem where id_tandem =  " . $this->escapeString( $value['id'] ) . " " );
				if ( $this->numResultats( $result2 ) > 0 ) {
					$result2 = $this->obteComArray( $result2 );
					if ( $result2[0]['total'] <= TIME_TO_FAILED_TANDEM ) {
						$failed ++;
					} else {
						$success ++;
					}
				}
			}
		}

		return array( "success" => $success, "failed" => $failed );
	}

	/**
	 * returns an array with all dates and number of tandems by that date
	 *
	 * @param $course_id
	 *
	 * @return array
	 */
	public function getCountAllTandemsByDate( $course_id ) {

		$sql = "SELECT DATE( created ) as created , COUNT( id ) AS total
                        FROM tandem
                        WHERE is_finished = 1
                        AND finalized IS NOT NULL
                        AND id_course = " . $this->escapeString( $course_id ) . "
                        GROUP BY DATE( created )
                        ORDER BY created asc";

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return array();
	}

	/**
	 * returns an array with all dates and number of tandems by that date
	 *
	 * @param $course_id
	 *
	 * @return array
	 */
	public function getCountAllUnFinishedTandemsByDate( $course_id ) {

		$sql = "SELECT DATE( created ) as created , COUNT( id ) AS total
                        FROM tandem
                        WHERE is_finished = 0
                        AND finalized IS NULL
                        AND id_course = " . $this->escapeString( $course_id ) . "
                        GROUP BY DATE( created )
                        ORDER BY created asc";

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return array();
	}

	/**
	 * Returns how many people have finished a tandem but not submitted their feedback
	 * TODO: Improve queries.
	 *
	 * @param $course_id
	 * @param $dateStart
	 * @param $dateEnd
	 *
	 * @return array
	 */
	public function getFeedbackStats( $course_id, $dateStart = false, $dateEnd = false ) {

		$r = array();

		//All feedback_tandem_forms sent
		$sql = "SELECT COUNT( * ) AS total
                    FROM feedback_tandem AS FT
                    INNER JOIN tandem AS T ON T.id = FT.id_tandem
                    INNER JOIN feedback_tandem_form FTF ON FTF.id_feedback_tandem = FT.id
                    WHERE T.is_finished = 1
                    AND T.finalized IS NOT NULL
                    AND T.id_course = " . $this->escapeString( $course_id ) . " ";

		$dateSQL = '';
		if ( $dateStart ) {
			$dateSQL .= " and date(T.created) >= '" . $dateStart . "' ";
			if ( $dateEnd > $dateStart ) {
				$dateSQL .= " and date(T.created) <= '" . $dateEnd . "' ";
			}
		}

		$result = $this->consulta( $sql . $dateSQL );
		if ( $this->numResultats( $result ) > 0 ) {
			$t                               = $this->obteComArray( $result );
			$r['feedback_tandem_forms_sent'] = $t[0]['total'];
		}

		//all feedback_tandem
		$sql = "SELECT COUNT( * ) AS total
                    FROM feedback_tandem AS FT
                    INNER JOIN tandem AS T ON T.id = FT.id_tandem
                    WHERE T.is_finished = 1
                    AND T.finalized IS NOT NULL
                    AND T.id_course = " . $this->escapeString( $course_id ) . " ";

		$result = $this->consulta( $sql . $dateSQL );
		if ( $this->numResultats( $result ) > 0 ) {
			$t                    = $this->obteComArray( $result );
			$r['feedback_tandem'] = $t[0]['total'];
		}

		//all feedback_tandem_form es_ES sent
		$sql = "SELECT COUNT( * ) AS total
                    FROM feedback_tandem AS FT
                    INNER JOIN tandem AS T ON T.id = FT.id_tandem
                    INNER JOIN feedback_tandem_form FTF ON FTF.id_feedback_tandem = FT.id
                    WHERE T.is_finished = 1
                    AND T.finalized IS NOT NULL
                    AND T.id_course = " . $this->escapeString( $course_id ) . "
                    AND FT.language ='es_ES' ";

		$result = $this->consulta( $sql . $dateSQL );
		if ( $this->numResultats( $result ) > 0 ) {
			$t                            = $this->obteComArray( $result );
			$r['feedback_tandem_form_es'] = $t[0]['total'];
		}

		//all feedback_tandem_form es_ES NOT sent
		$sql = "SELECT COUNT( * ) AS total
                    FROM feedback_tandem AS FT
                    INNER JOIN tandem AS T ON T.id = FT.id_tandem
                    WHERE FT.id not in( select id_feedback_tandem from feedback_tandem_form )
                    AND T.is_finished = 1
                    AND T.finalized IS NOT NULL
                    AND T.id_course = " . $this->escapeString( $course_id ) . "
                    AND FT.language ='es_ES' ";

		$result = $this->consulta( $sql . $dateSQL );
		if ( $this->numResultats( $result ) > 0 ) {
			$t                                     = $this->obteComArray( $result );
			$r['feedback_tandem_form_es_not_sent'] = $t[0]['total'];
		}


		//all feedback_tandem_form en_US sent
		$sql = "SELECT COUNT( * ) AS total
                    FROM feedback_tandem AS FT
                    INNER JOIN tandem AS T ON T.id = FT.id_tandem
                    INNER JOIN feedback_tandem_form FTF ON FTF.id_feedback_tandem = FT.id
                    WHERE T.is_finished = 1
                    AND T.finalized IS NOT NULL
                    AND T.id_course = " . $this->escapeString( $course_id ) . "
                    AND FT.language ='en_US' ";

		$result = $this->consulta( $sql . $dateSQL );
		if ( $this->numResultats( $result ) > 0 ) {
			$t                            = $this->obteComArray( $result );
			$r['feedback_tandem_form_en'] = $t[0]['total'];
		}

		//all feedback_tandem_form en_US NOT sent
		$sql = "SELECT COUNT( * ) AS total
                    FROM feedback_tandem AS FT
                    INNER JOIN tandem AS T ON T.id = FT.id_tandem
                    WHERE FT.id not in( select id_feedback_tandem from feedback_tandem_form )
                    AND T.is_finished = 1
                    AND T.finalized IS NOT NULL
                    AND T.id_course = " . $this->escapeString( $course_id ) . "
                    AND FT.language ='en_US' ";

		$result = $this->consulta( $sql . $dateSQL );
		if ( $this->numResultats( $result ) > 0 ) {
			$t                                     = $this->obteComArray( $result );
			$r['feedback_tandem_form_en_not_sent'] = $t[0]['total'];
		}

		return $r;
	}

	/**
	 * Gets all tandems that have failed and succeded depending if they are webrtc or videochat
	 *
	 * @param $course_id
	 * @param $dateStart
	 * @param $dateEnd
	 *
	 * @return array
	 */
	public function tandemStatsByVideoType( $course_id, $dateStart = false, $dateEnd = false ) {

		$r = array(
			"tandem_ok" => array( "webrtc" => 0, "videochat" => 0 ),
			"tandem_ko" => array( "webrtc" => 0, "videochat" => 0 )
		);

		$sql = "SELECT T.id from tandem as T
                    INNER JOIN feedback_tandem as FT on FT.id_tandem = T.id
                    WHERE
                    FT.id_tandem = FT.id_external_tool
                    AND FT.external_video_url IS NOT NULL
                    AND T.id_course = " . $this->escapeString( $course_id ) . "
                    ";
		if ( $dateStart ) {
			$sql .= "and date(T.created) >= '" . $dateStart . "' ";
			if ( $dateEnd > $dateStart ) {
				$sql .= "and date(T.created) <= '" . $dateEnd . "' ";
			}
		}
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );
			foreach ( $result as $key => $value ) {
				$result2 = $this->consulta( "select sum(total_time) as total from user_tandem where id_tandem =  " . $this->escapeString( $value['id'] ) . " " );
				if ( $this->numResultats( $result2 ) > 0 ) {
					$result2 = $this->obteComArray( $result2 );
					if ( $result2[0]['total'] <= 5 ) {
						$r['tandem_ko']['webrtc'] ++;
					} else {
						$r['tandem_ok']['webrtc'] ++;
					};
				}
			}
		}


		$sql    = "SELECT T.id from tandem as T
                    INNER JOIN feedback_tandem as FT on FT.id_tandem = T.id
                    WHERE  FT.id_tandem != FT.id_external_tool
                    AND FT.end_external_service IS NOT NULL
                    AND T.id_course = " . $this->escapeString( $course_id ) . "
                    ";
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );
			foreach ( $result as $key => $value ) {
				$result2 = $this->consulta( "select sum(total_time) as total from user_tandem where id_tandem =  " . $this->escapeString( $value['id'] ) . " " );
				if ( $this->numResultats( $result2 ) > 0 ) {
					$result2 = $this->obteComArray( $result2 );
					if ( $result2[0]['total'] <= 5 ) {
						$r['tandem_ko']['videochat'] ++;
					} else {
						$r['tandem_ok']['videochat'] ++;
					}
				}
			}
		}

		return $r;
	}

	/**
	 * Get the partners name of a feedback
	 *
	 * @param $feedback_id
	 *
	 * @return string
	 */
	public function getPartnerName( $feedback_id ) {

		$sql    = "select id_partner from feedback_tandem where id = " . $this->escapeString( $feedback_id ) . " ";
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );

			return $this->getUserName( $result['0']['id_partner'] );
		}

		return '';
	}

	/**
	 * Retuns the number of people that were waiting for a tandem but never managet to find a partner
	 * on each language
	 *
	 * @param $course_id
	 * @param $dateStart
	 * @param $dateEnd
	 *
	 * @return array
	 */
	public function peopleWaitedWithoutTandem( $course_id, $dateStart = false, $dateEnd = false ) {

		$r = array( "es" => 0, "en" => 0 );

		$dateSQL = '';
		if ( $dateStart ) {
			$dateSQL .= "and date(WRUH.created) >= '" . $dateStart . "' ";
			if ( $dateEnd > $dateStart ) {
				$dateSQL .= "and date(WRUH.created) <= '" . $dateEnd . "' ";
			}
		}

		$sql = "SELECT  count(distinct 
		concat(YEAR(WRUH.created), 
		       MONTH(WRUH.created), 
		       DAYOFMONTH(WRUH.created), 
		       HOUR(WRUH.created), 
		       MINUTE(WRUH.created))) as total , WRH.language AS lang
                        FROM waiting_room_user_history AS WRUH
                        INNER JOIN waiting_room_history AS WRH ON WRUH.id_waiting_room = WRH.id_waiting_room
                        AND WRH.id_course = " . $this->escapeString( $course_id ) . "
                        and id_tandem = -1
                        $dateSQL
                        GROUP BY WRH.language";

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );
			foreach ( $result as $key => $value ) {
				if ( $value['lang'] == 'es_ES' ) {
					$r['es'] = $value['total'];
				}
				if ( $value['lang'] == 'en_US' ) {
					$r['en'] = $value['total'];
				}
			}
		}

		return $r;
	}

	/**
	 *Updates the table session_user with every call to checksession.php
	 * returns the time that has passed since we are updating the table
	 *
	 * @param $tandem_id
	 * @param $user_id
	 * @param $force_select_room
	 * @param $open_tool_id
	 * @param $sent_url
	 *
	 * @return false|int
	 */
	public function updateSessionUser( $tandem_id, $user_id, $force_select_room, $open_tool_id, $sent_url ) {

		$force_select_room = $force_select_room == '' ? 1 : $force_select_room;
		$sql               = " SELECT * from session_user where tandem_id = " . $this->escapeString( $tandem_id ) . "
                        AND user_id = " . $this->escapeString( $user_id ) . " ";
		$result            = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$this->consulta( "update session_user set last_updated = NOW()  where tandem_id = " . $this->escapeString( $tandem_id ) . "
                        AND user_id = " . $this->escapeString( $user_id ) . " " );
		} else {
			$token = md5( uniqid( rand(), true ) );
			$this->consulta( "insert into session_user(tandem_id,user_id,created,last_updated,select_room,open_tool_id,token,url_sent)
                                     values( " . $this->escapeString( $tandem_id ) . "," . $this->escapeString( $user_id ) . ",NOW(),NOW()," . $this->escapeString( $force_select_room ) . "," . $this->escapeString( $open_tool_id ) . "," . $this->escapeString( $token ) . "," . $this->escapeString( $sent_url ) . " ) " );
		}

		//now lets see how long it has been since our partner has updated the last_updated time
		$timePassed = 0; //in seconds

		//ok first we need to get the partner user_id
		$sql             = " SELECT * FROM tandem where id = " . $this->escapeString( $tandem_id ) . "  ";
		$result          = $this->consulta( $sql );
		$partner_user_id = 0;
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );
			if ( $result[0]['id_user_host'] == $user_id ) {
				$partner_user_id = $result[0]['id_user_guest'];
			} else {
				$partner_user_id = $result[0]['id_user_host'];
			}
		}

		$result = $this->consulta( "select * from session_user where user_id = " . $this->escapeString( $partner_user_id ) . " and tandem_id =  " . $this->escapeString( $tandem_id ) . "  " );
		if ( $this->numResultats( $result ) > 0 ) {
			$result              = $this->obteComArray( $result );
			$timeFirst           = strtotime( $result[0]['last_updated'] );
			$timeSecond          = strtotime( date( "Y-m-d H:i:s" ) );
			$differenceInSeconds = $timeSecond - $timeFirst;
			$timePassed          = $differenceInSeconds;
		} else {
			//if we get here it means that the partner has not reached the session_user table , so we start to count from our own
			$result              = $this->consulta( "select * from session_user where user_id = " . $this->escapeString( $user_id ) . " and tandem_id =  " . $this->escapeString( $tandem_id ) . "  " );
			$result              = $this->obteComArray( $result );
			$timeFirst           = strtotime( $result[0]['created'] );
			$timeSecond          = strtotime( date( "Y-m-d H:i:s" ) );
			$differenceInSeconds = $timeSecond - $timeFirst;
			$timePassed          = $differenceInSeconds;
		}

		return $timePassed;

	}

	/**
	 * Returns all the info for a user from the session_user table
	 *
	 * @param $user_id
	 * @param $tandem_id
	 *
	 * @return array|mixed
	 */
	public function getSessionUserData( $user_id, $tandem_id ) {
		$sql    = " select * from session_user where user_id = " . $this->escapeString( $user_id ) . " and tandem_id = " . $this->escapeString( $tandem_id ) . " ";
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );

			return $result[0];
		}

		return array();
	}

	/**
	 * Returns all the info for a user_id
	 *
	 * @param $user_id
	 *
	 * @return array|mixed
	 */
	public function getUserData( $user_id ) {
		if ( $user_id > 0 ) {
			$sql    = " select * from user where id = " . $this->escapeString( $user_id );
			$result = $this->consulta( $sql );
			if ( $this->numResultats( $result ) > 0 ) {
				$result = $this->obteComArray( $result );

				return $result[0];
			}
		}

		return array();
	}

	/**
	 * @param $tandem_id
	 * @param $user_id
	 * @param $token
	 *
	 * @return array|mixed
	 */
	public function getSessionData( $tandem_id, $user_id, $token ) {
		$sql = "SELECT  *
                        FROM session_user
                        WHERE tandem_id = " . $this->escapeString( $tandem_id ) . "
                        AND user_id = " . $this->escapeString( $user_id ) . "
                        AND token = " . $this->escapeString( $token );

		$array  = array();
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$array = $this->obteComArray( $result );
			$array = $array[0];
		}

		return $array;
	}

	/**
	 * @param $tandem_id
	 * @param $user_id
	 * @param $force_select_room
	 * @param $open_tool_id
	 * @param $sent_url
	 *
	 * @return array
	 */
	public function createSessionUser( $tandem_id, $user_id, $force_select_room, $open_tool_id, $sent_url ) {

		$r      = array();
		$sql    = " SELECT * from session_user where tandem_id = " . $this->escapeString( $tandem_id ) . "
                        AND user_id = " . $this->escapeString( $user_id ) . " ";
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) == 0 ) {
			$token = md5( uniqid( rand(), true ) );
			$now   = date( "Y-m-d H:i:s" );
			$this->consulta( "insert into session_user(tandem_id,user_id,created,last_updated,select_room,open_tool_id,token,url_sent)
                                     values( " . $this->escapeString( $tandem_id ) . "," . $this->escapeString( $user_id ) . "," . $this->escapeString( $now ) . "," . $this->escapeString( $now ) . "," . $this->escapeString( $force_select_room ? 1 : 0 ) . "," . $this->escapeString( $open_tool_id ? 1 : 0 ) . "," . $this->escapeString( $token ) . "," . $this->escapeString( $sent_url ) . " ) " );

			$r['tandem_id']    = $tandem_id;
			$r['user_id']      = $user_id;
			$r['created']      = $now;
			$r['last_updated'] = $now;
			$r['select_room']  = $force_select_room;
			$r['open_tool_id'] = $open_tool_id;
			$r['token']        = $token;
			$r['url_sent']     = $sent_url;

		} else {
			$result = $this->obteComArray( $result );
			$r      = $result[0];
		}

		return $r;
	}

	/**
	 * @param $user_id
	 * @param $course_id
	 * @param bool $debug
	 * @param bool $filter_dates
	 *
	 * @return stdClass|string
	 */
	public function getUserRankingPoints( $user_id, $course_id, $debug = false, $filter_dates = true ) {
		// Get student and partner's comments for all those tandems (made by this student in this course)
		// in which the student has provided some feedback.
		$sql = 'SELECT UT.id_user,
                       FT.id_partner,
                       UT.total_time,
                       UT.id_tandem,
                       UT.finalized,
                       UT.is_finished,
                       FTF.feedback_form,
                       FTF.rating_partner_feedback_form,
                       sFTF.feedback_form AS user_feedback_form,
                       sFTF.rating_partner_feedback_form AS the_partner_rating_my_feedback
                  FROM user_tandem AS UT
             LEFT JOIN feedback_tandem AS FT ON FT.id_tandem = UT.id_tandem AND FT.id_user = UT.id_user
             LEFT JOIN feedback_tandem_form AS FTF ON FTF.id_feedback_tandem = FT.id
             LEFT JOIN feedback_tandem as sFT ON sFT.id_tandem = UT.id_tandem AND sFT.id_user = FT.id_partner
             LEFT JOIN feedback_tandem_form AS sFTF ON sFTF.id_feedback_tandem = sFT.id
            INNER JOIN tandem AS T ON T.id = UT.id_tandem
                 WHERE ((COALESCE(UT.finalized, 0) = 0 AND UT.total_time > 60) OR (UT.finalized IS NOT NULL AND UT.is_finished = 1))
                   AND FT.id_partner IS NOT NULL
                   AND T.id_course = ' . $this->escapeString( $course_id ) . ' 
                   AND UT.id_user = ' . $this->escapeString( $user_id ) . ' ';

		if ( $filter_dates ) {
			$course = $this->get_course_by_id( $course_id );
			if ( $course['startDateRanking'] ) {
				$hour = intval( $course['startHourRanking'] );
				$sql  .= ' AND T.created >= ' . $this->escapeString( $course['startDateRanking'] . ' ' . $hour . ':0:0' );
			}
			if ( $course['endDateRanking'] ) {
				$hour = intval( $course['endHourRanking'] );
				$sql  .= ' AND T.created <= ' . $this->escapeString( $course['endDateRanking'] . ' ' . $hour . ':59:59' );
			}
		}

		$ret                             = new stdClass();
		$points                          = 0;
		$total_time                      = 0;
		$number_of_tandems               = 0;
		$number_of_tandems_with_feedback = 0;
		$user_fluency                    = 0;
		$user_accuracy                   = 0;
		$user_overall_grade              = 0;

		// Add points per each pre, post survey (Moodle questionnaire) completed.
		$pointspersurvey = defined( 'POINTS_PER_SURVEY_COMPLETED' ) ? POINTS_PER_SURVEY_COMPLETED : 50;
		// TODO
		$pointsper2ndsurvey = 0; //defined( 'POINTS_PER_SECOND_SURVEY_COMPLETED' ) ? POINTS_PER_SECOND_SURVEY_COMPLETED : 200;
		$surveystatus       = $this->get_user_survey_status( $user_id, $course_id );
		$first_survey       = true;
		foreach ( $surveystatus as $completed ) {

			if ( $completed ) {
				if ( $first_survey ) {
					$points += $pointspersurvey;
				} else {
					$points += $pointsper2ndsurvey;
				}
			}
			$first_survey = false;
		}

		// Set default return data.
		$ret->points                          = $points;
		$ret->total_time                      = $total_time;
		$ret->number_of_tandems               = $number_of_tandems;
		$ret->number_of_tandems_with_feedback = $number_of_tandems_with_feedback;
		$ret->user_fluency                    = $user_fluency;
		$ret->user_accuracy                   = $user_accuracy;
		$ret->user_overall_grade              = $user_overall_grade;

		if ( $debug ) {
			echo "<p>$sql</p>";
		}

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );
			if ( $debug ) {
				/** @noinspection ForgottenDebugOutputInspection */
				var_dump( $result );
			}

			$total_stars_received_about_my_ratings = 0;
			$number_of_ratings_about_my_feedback   = 0;

//            $pointspertandem = defined('POINTS_PER_TANDEM_DONE') ? POINTS_PER_TANDEM_DONE : 10;
			$pointspergivenfeedback        = defined( 'POINTS_PER_GIVEN_FEEDBACK' ) ? POINTS_PER_GIVEN_FEEDBACK : 10;
			$pointsperspeakingminute       = defined( 'POINTS_PER_SPEAKING_MINUTE' ) ? POINTS_PER_SPEAKING_MINUTE : 1;
			$pointsperratedpartnerfeedback = defined( 'POINTS_PER_RATED_PARTNER_FEEDBACK' ) ? POINTS_PER_RATED_PARTNER_FEEDBACK : 5;
			$pointsperfeedbackstarreceived = defined( 'POINTS_PER_FEEDBACK_STAR_RECEIVED' ) ? POINTS_PER_FEEDBACK_STAR_RECEIVED : 2;

			foreach ( $result as $key => $value ) {
				if ( $debug ) {
					echo "<p>Key $key current $points</p>";
				}

				if ( ! empty( $value['feedback_form'] ) ) {
                    if ( $value['total_time'] < 60 ) {
                        continue;
                    }
					// If they have sent the feedback we give 10 points :D
					$points += $pointspergivenfeedback;
					if ( $debug ) {
						echo "<p>Adding {$pointspergivenfeedback} points for feedback {$points}</p>";
					}

					// For each minute (60 seconds) of the total time within tandems, we add points :P
                    $points += ceil( $value['total_time'] / 60 ) * $pointsperspeakingminute;
					if ( $debug ) {
						echo "<p>Total time {$value['total_time']}</p>";
					}

					// If we have rated the other person feedback then we give 5 points :)
					if ( ! empty( $value['rating_partner_feedback_form'] ) ) {
						$points += $pointsperratedpartnerfeedback;
						if ( $debug ) {
							echo "<p>Rating partner form {$value['rating_partner_feedback_form']}</p>";
						}
					}

					// Find out if our partner has rated our feedback-form and sum points per each star received.
					if ( ! empty( $value['the_partner_rating_my_feedback'] ) ) {
						$unserialize = unserialize( $value['the_partner_rating_my_feedback'] );
						if ( ! empty( $unserialize->partner_rate ) ) {
							$points += $unserialize->partner_rate * $pointsperfeedbackstarreceived;
							if ( $debug ) {
								echo "<p>Starts partner {$unserialize->partner_rate}</p>";
							}
							$total_stars_received_about_my_ratings += (int) $unserialize->partner_rate;
							++ $number_of_ratings_about_my_feedback;
						}
					}
				} else {
					if ( $debug ) {
						echo '<p>No gived feedback </p>';
						/** @noinspection ForgottenDebugOutputInspection */
						print_r( $value );
					}
				}

				$total_time += $value['total_time'];
				// Check if user has received evaluation from the partner
				if ( ! empty( $value['user_feedback_form'] ) ) {
					$user_evaluation = unserialize( $value['user_feedback_form'] );
					if ( ! empty( $user_evaluation->fluency ) ) {
						$user_fluency += $user_evaluation->fluency;
					}
					if ( ! empty( $user_evaluation->accuracy ) ) {
						$user_accuracy += $user_evaluation->accuracy;
					}
					if ( ! empty( $user_evaluation->grade ) ) {
						$user_overall_grade += getOverallAsNumber( $user_evaluation->grade );
					}
					$number_of_tandems_with_feedback ++;
				}

				$number_of_tandems ++;
			} // End foreach

			// Update return with calculated points.
			$ret->points                          = $points;
			$ret->number_of_tandems               = $number_of_tandems;
			$ret->number_of_tandems_with_feedback = $number_of_tandems_with_feedback;
			if ( $number_of_tandems_with_feedback > 0 ) {
				$ret->user_fluency       = $user_fluency / $number_of_tandems_with_feedback;
				$ret->user_accuracy      = $user_accuracy / $number_of_tandems_with_feedback;
				$ret->user_overall_grade = $user_overall_grade / $number_of_tandems_with_feedback;
			}
			if ( $number_of_ratings_about_my_feedback > 0 ) {
				$ret->user_feedback_stars = round( $total_stars_received_about_my_ratings / $number_of_ratings_about_my_feedback,
					1 );
			}
			$ret->total_time = $total_time;
		}

		return $ret;
	}

	/**
	 * Updates the user ranking stats with the formula on https://tresipunt.atlassian.net/browse/MOOCTANDEM-42
	 *
	 * @param $user_id
	 * @param $course_id
	 * @param $lang
	 * @param bool $debug
	 *
	 * @return string
	 */
	public function updateUserRankingPoints( $user_id, $course_id, $lang = false, $debug = false ) {
		$result = 'NOT UPDATED';

		if ( $lang == false ) {
			$lang = $this->get_user_language( $course_id, $user_id );
		}
		if ($this->get_user_is_instructor( $course_id, $user_id )) {
			return false;
		}

		$ret = $this->getUserRankingPoints( $user_id, $course_id, $debug, true );

		$ret->points += $this->calculateBadges( $user_id, $course_id, $lang, $ret->number_of_tandems, $ret->total_time,
			$debug );

		if ( $ret->points ) {
			$points            = $ret->points;
			$number_of_tandems = $ret->number_of_tandems;

			$user_fluency       = 0;
			$user_accuracy      = 0;
			$user_overall_grade = 0;
			if ( $ret->number_of_tandems_with_feedback > 0 ) {
				$user_fluency       = $ret->user_fluency ? $ret->user_fluency : 0;
				$user_accuracy      = $ret->user_accuracy ? $ret->user_accuracy : 0;
				$user_overall_grade = $ret->user_overall_grade ? $ret->user_overall_grade : 0;
			}
			$total_time = $ret->total_time ? $ret->total_time : 0;

			$sql    = 'SELECT * FROM user_ranking WHERE user_id = ' . $this->escapeString( $user_id )
			          . ' AND course_id = ' . $this->escapeString( $course_id ) . ' ';
			$result = $this->consulta( $sql );
			if ( $this->numResultats( $result ) > 0 ) {
//                $result = $this->obteComArray($result);
				$sql = 'UPDATE user_ranking SET '
				       . 'points = ' . $this->escapeString( $points )
				       . ', lang =' . $this->escapeString( $lang )
				       . ', total_time =' . $this->escapeString( $total_time )
				       . ', number_of_tandems = ' . $this->escapeString( $number_of_tandems )
				       . ', fluency = ' . $this->escapeString( $user_fluency )
				       . ', accuracy = ' . $this->escapeString( $user_accuracy )
				       . ', overall_grade = ' . $this->escapeString( $user_overall_grade )
				       . ' WHERE user_id  = ' . $this->escapeString( $user_id )
				       . ' AND course_id = ' . $this->escapeString( $course_id );
				$this->consulta( $sql );
				$result = 'UPDATED';
			} else {
				$sql = 'INSERT INTO user_ranking (user_id, course_id, points, lang, total_time, '
				       . ' number_of_tandems, fluency, accuracy, overall_grade) VALUES ('
				       . $this->escapeString( $user_id ) . ','
				       . $this->escapeString( $course_id ) . ','
				       . $points . ','
				       . $this->escapeString( $lang ) . ','
				       . $this->escapeString( $total_time ) . ','
				       . $this->escapeString( $number_of_tandems ) . ','
				       . $this->escapeString( $user_fluency ) . ','
				       . $this->escapeString( $user_accuracy ) . ','
				       . $this->escapeString( $user_overall_grade ) . ')';
				$this->consulta( $sql );
				$result = 'INSERTED';
			}
			if ( $debug ) {
				echo "<p>SQL $sql</p>";
			}
		}

		return "{$result} {$user_id} and lang {$lang} in course {$course_id} Points = {$ret->points}";
	}

	/**
	 * @param $course_id
	 * @param bool $start_date
	 * @param bool $end_date
	 *
	 * @return array
	 */
	public function updateAllUsersRankingPoints( $course_id, $start_date = false, $end_date = false ) {

		$arrayReturn = array();
		$sql         = "select distinct U.id,UC.language from user  as U
                    inner join user_course as UC on UC.id_user = U.id
                    where UC.is_instructor = 0  and UC.id_course = " . $this->escapeString( $course_id );
		$result2     = $this->consulta( $sql );
		$user_points = array();
		if ( $this->numResultats( $result2 ) > 0 ) {
			$result2 = $this->obteComArray( $result2 );
			foreach ( $result2 as $key => $value2 ) {
				$user_id       = $value2['id'];
				// $language      = $value2['language'];
				$ret = $this->updateUserRankingPoints( $user_id, $course_id );
				if ($ret) {
					$arrayReturn[] = $ret;
				}
			}
		}

		return $arrayReturn;
	}

	/**
	 * Returns the id_external_tool from the feedback_tandem table
	 *
	 * @param $id_tandem
	 *
	 * @return int
	 */
	public function checkExternalToolField( $id_tandem ) {

		$sql    = "select id_external_tool from feedback_tandem where id_tandem = " . $this->escapeString( $id_tandem ) . "";
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );

			return $result[0]['id_external_tool'];
		}

		return 0;

	}

	/**
	 * @param $feedback_id
	 *
	 * @return array|mixed
	 */
	public function getUserFeedback( $feedback_id ) {

		$result = $this->consulta( "select FT.id,FT.id_tandem,FT.id_external_tool,FT.end_external_service,FT.external_video_url,FT.id_user,FT.language,FT.id_partner,FT.partner_language,FT.created,FTF.feedback_form, E.name as exercise, U.fullname from feedback_tandem as FT
           left join feedback_tandem_form as FTF on FTF.id_feedback_tandem = FT.id
           inner join tandem as T on T.id = FT.id_tandem
           inner join exercise E on E.id=T.id_exercise
           inner join user as U on U.id = FT.id_user
           where  FT.id = " . $this->escapeString( $feedback_id ) . " " );

		if ( $this->numResultats( $result ) > 0 ) {
			$feedback_tandem = $this->obteComArray( $result );
			$return          = array();

			foreach ( $feedback_tandem as $ft ) {

				$tandemDurations = $this->getUserTandemDurations( $ft['id_user'], $ft['id_tandem'] );
				$seconds         = isset( $tandemDurations[0]['total_time'] ) ? $tandemDurations[0]['total_time'] : 0;
				// switch ($finishedTandem) {
				//      case 1: //Finished
				//          if (intval($seconds)<TIME_TO_FAILED_TANDEM) {
				//              continue 2;
				//          }
				//          break;
				//      case 2: //UnFinished
				//          if (intval($seconds)>TIME_TO_FAILED_TANDEM) {
				//              continue 2;
				//          }
				//          break;
				//      //default:
				//      //nothing continue

				// }
				$minutes    = $this->minutes( $seconds );
				$total_time = $this->time_format( $seconds );
				$subTP      = explode( ":", $total_time );
				if ( $subTP[0] > 0 ) {
					$subTimerP = substr( $subTP[0], 1 ) . ":" . $subTP[1] . ":" . $subTP[2];
				} else {
					$subTimerP = $subTP[1] . ":" . $subTP[2];
				}
				$task_tandemsSubTime = $this->getUserTandemTasksDurations( $ft['id_user'], $ft['id_tandem'] );
				$subTimer            = array();
				$j                   = 0;
				$i                   = 0;
				if ( ! empty( $task_tandemsSubTime ) ) {
					foreach ( $task_tandemsSubTime as $question ) {
						$secondsSt = isset( $question['total_time'] ) ? $question['total_time'] : 0;
						$obj       = secondsToTime( $secondsSt );
						$time      = '';
						if ( $obj['h'] > 0 ) {
							$time .= ( $obj['h'] < 10 ? '0' : '' ) . $obj['h'] . ':';
						}
						$time           .= ( $obj['m'] < 10 ? '0' : '' ) . $obj['m'] . ':';
						$time           .= ( $obj['s'] < 10 ? '0' : '' ) . $obj['s'];
						$subTimer[ $i ] = $time;
						$i ++;
					}
				}
				$ft['total_time']       = $subTimerP;
				$ft['total_time_tasks'] = $subTimer;

				$overall_grade     = $this->checkPartnerFeedback( $ft['id_tandem'], $ft['id'] );
				$overall_grade_tmp = "";
				if ( ! empty( $overall_grade ) ) {
					$overall_grade     = unserialize( $overall_grade );
					$overall_grade_tmp = $overall_grade->grade;
				}

				$ft['overall_grade'] = $overall_grade_tmp;

				$return[] = $ft;
			}

			return $return[0];
		} else {
			return array();
		}

	}

	/**
	 * Get feedback Rating details
	 *
	 * @param $id_feedback
	 * @param $id_partner
	 * @param $id_tandem
	 *
	 * @return bool|stdClass
	 */
	public function getPartnerFeedbackRatingDetails( $id_feedback, $id_partner, $id_tandem ) {

		$result   = $this->consulta( 'select
            feedback_tandem_form.rating_partner_feedback_form
            from feedback_tandem
            inner join feedback_tandem_form on feedback_tandem_form.id_feedback_tandem=feedback_tandem.id
            where feedback_tandem.id !=' . $this->escapeString( $id_feedback ) . ' and feedback_tandem.id_tandem =' . $this->escapeString( $id_tandem )
		                             . ' and feedback_tandem.id_user =' . $this->escapeString( $id_partner ) );
		$feedback = false;
		if ( $this->numResultats( $result ) > 0 ) {
			$r                            = $this->obteComArray( $result );
			$feedback                     = new stdClass();
			$rating_partner_feedback_form = $r[0]['rating_partner_feedback_form'];
			if ( $rating_partner_feedback_form && strlen( $rating_partner_feedback_form ) > 0 ) {
				$feedback->rating_partner_feedback_form = unserialize( $rating_partner_feedback_form );
			} else {
				$feedback->rating_partner_feedback_form = false;
			}
		}

		return $feedback;
	}

	/**
	 * Sums all the time and number of tandems, the return is like row['total_time'] and $row['count']
	 *
	 * @param $userid
	 * @param $course_id
	 *
	 * @return array|bool|null
	 */
	public function getTotalTimeAndCountUserCourse( $userid, $course_id ) {

		$sql    = 'select sum(UT.total_time) as total_time,count(UT.id_tandem) as count from user_tandem as UT
                             left join feedback_tandem as FT on FT.id_tandem = UT.id_tandem and FT.id_user = UT.id_user
                             left join feedback_tandem_form as FTF on FTF.id_feedback_tandem = FT.id
                             left join feedback_tandem as sFT on sFT.id_user = FT.id_partner and sFT.id_tandem = UT.id_tandem
                             left join feedback_tandem_form as sFTF on sFTF.id_feedback_tandem = sFT.id
                             inner join tandem as T on T.id = UT.id_tandem
                            where
                            T.id_course = ' . $this->escapeString( $course_id ) . ' and UT.id_user = ' . $this->escapeString( $userid );
		$row    = false;
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );
		}

		return $row;

	}

	/**
	 * Get Ranking data
	 *
	 * @param $userid
	 * @param $course_id
	 *
	 * @return array|bool|null
	 */
	public function getRankingData( $userid, $course_id ) {

		$sql    = 'select * from user_ranking as UR
                                            inner join user_course as UC on UC.id_user = UR.user_id
                    where UR.course_id = ' . $this->escapeString( $course_id ) . ' and UR.user_id = ' . $this->escapeString( $userid );
		$row    = false;
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );
		}

		return $row;

	}

	/**
	 * @param $user_id
	 *
	 * @return string
	 */
	public function getUserEmail( $user_id ) {
		if ( $user_id > 0 ) {
			$sql    = 'SELECT email FROM `user`
                    WHERE id = ' . $this->escapeString( $user_id );
			$result = $this->consulta( $sql );
			if ( $this->numResultats( $result ) > 0 ) {
				$names = $this->obteComArray( $result );

				return $names[0]['email'];
			}
		}

		return '';
	}

	/**
	 * @param $id_tandem
	 * @param $id_user
	 * @param $mood
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function setMoodToUser( $id_tandem, $id_user, $mood ) {
		$sql    = 'update user_tandem
         set user_mood = ' . $mood . ' where id_tandem = ' . $id_tandem . ' and id_user = ' . $id_user;
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * @param $id_tandem
	 * @param $id_user
	 * @param $task_number
	 * @param $enjoyed
	 * @param $nervous
	 * @param $task_valoration
	 * @param $comment
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function setTaskEvaluation(
		$id_tandem,
		$id_user,
		$task_number,
		$enjoyed,
		$nervous,
		$task_valoration,
		$comment
	) {
		$sql = 'UPDATE user_tandem_task
                SET task_enjoyed = ' . $this->escapeString( $enjoyed ) . ', 
                    task_nervous = ' . $this->escapeString( $nervous ) . ',
                    task_comment = ' . $this->escapeString( $comment ) . ',
                    task_valoration = ' . $this->escapeString( $task_valoration ) . ' 
                WHERE id_tandem = ' . $this->escapeString( $id_tandem ) . '
                AND id_user = ' . $this->escapeString( $id_user ) . '
                AND task_number = ' . $this->escapeString( $task_number ) . ' ';

		return $this->consulta( $sql );
	}

	/**
	 * Updates the list of users of the current course
	 *
	 * @param $id_course
	 *
	 * @return resource
	 */
	public function delete_user_from_course( $id_course ) {
		$sql    = 'update user_course set id_user = (-1*id_user) where id_course= ' . $id_course;
		$result = $this->consulta( $sql );
		$sql    = 'update tandem set id_user_host = (-1*id_user_host), id_user_guest = (-1*id_user_guest) where id_course= ' . $id_course;
		$result = $this->consulta( $sql );

		return $result;
	}

	//@ybilbao 3iPunt -> Get course rubrics

	/**
	 * @param $course_id
	 *
	 * @return array|bool
	 */
	public function get_course_rubrics( $course_id ) {
		$sql = $this->get_rubrics_sql( $course_id );

		$result     = $this->consulta( $sql );
		$numResults = $this->numResultats( $result );
		if ( $numResults > 0 ) {
			$rubrics = array();
			while ( $rubric = $this->get_mysql_fetch_array( $result ) ) {
				$rubrics[] = $rubric;
			}

			return $rubrics;
		} else {
			$course_id = 0;
			$sql       = $this->get_rubrics_sql( $course_id );
			$result    = $this->consulta( $sql );
			if ( ! empty( $result ) ) {
				$rubrics = array();
				while ( $rubric = $this->get_mysql_fetch_array( $result ) ) {
					$rubrics[] = $rubric;
				}

				return $rubrics;
			} else {
				return false;
			}
		}
	}

	/**
	 * @param $course_id
	 *
	 * @return string
	 */
	private function get_rubrics_sql( $course_id ) {
		$sql = 'SELECT id,name,description,field_name
                FROM feedback_rubric
                LEFT JOIN feedback_rubric_def_items
                ON feedback_rubric_def_items.item_id = feedback_rubric.id
                LEFT JOIN feedback_rubric_course_def
                ON feedback_rubric_course_def.id_feedback_definition = feedback_rubric_def_items.def_id
                WHERE id_course = ' . $course_id . " AND lang = '" . $_SESSION['lang'] . "'";

		return $sql;
	}

	/**
	 * Returns the tandem per day of week 1=> Sunday, 7 => Saturday
	 *
	 * @param $course_id
	 * @param $dateStart
	 * @param $dateEnd
	 *
	 * @return array with arrays for per_day_of_week_finalized, per_day_of_week, per_hour_finalized and per_hour
	 */
	public function get_stats_tandem_by_date( $course_id, $dateStart = false, $dateEnd = false ) {
		$datesql = '';
		if ( $dateStart ) {
			$datesql = "and date(tandem.created) >= '" . $dateStart . "' ";
			if ( $dateEnd > $dateStart ) {
				$datesql .= "and date(tandem.created) <= '" . $dateEnd . "' ";
			}
		}
		$stats = array(/*'per_day_of_week_finalized' => false,*/
			'per_day_of_week' => false,
			//'per_hour_finalized' => false,
			'per_hour'        => false,
			'per_user_status' => false,
			'exercises'       => false
		);

		$sql_per_day_of_week = 'select count(id) total_tandems, hour(created) as hour, DAYOFWEEK(created) as day 
                from tandem where id_course = ' . $this->escapeString( $course_id ) . $datesql . ' GROUP BY hour(created), DAYOFWEEK(created)
                order by DAYOFWEEK(created) , hour(created)';

		$result = $this->consulta( $sql_per_day_of_week );
		if ( ! empty( $result ) ) {
			$stats['per_day_of_week'] = $this->obteComArray( $result );
			$stats['per_day_of_week'] = $this->fill_data_hours_days( $stats['per_day_of_week'], true );
		}
		/*$sql_per_day_of_week_finalized = 'select count(id) total_tandems, hour(created) as hour, DAYOFWEEK(created) as day, is_finished
                from tandem where id_course = '.$this->escapeString($course_id).$datesql.' GROUP BY hour(created), DAYOFWEEK(created), is_finished
                order by DAYOFWEEK(created) , hour(created)';

        $result = $this->consulta($sql_per_day_of_week_finalized);
        if(!empty($result)){
            $stats['per_day_of_week'] = $this->obteComArray($result);
        }

        $sql_per_hour_finalized = 'select count(id) total_tandems, hour(created) as hour, is_finished
                  from tandem where id_course  = '.$this->escapeString($course_id).$datesql.' GROUP BY hour(created),is_finished
                  order by DAYOFWEEK(created) , hour(created)';
        $result = $this->consulta($sql_per_hour_finalized);
        if(!empty($result)){
            $stats['per_hour_finalized'] = $this->obteComArray($result);
        }*/

		$sql_per_hour = 'select count(id) total_tandems, hour(created) as hour 
                from tandem where id_course  = ' . $this->escapeString( $course_id ) . $datesql . ' GROUP BY hour(created)
                order by DAYOFWEEK(created) , hour(created)';
		$result       = $this->consulta( $sql_per_hour );
		if ( ! empty( $result ) ) {
			$stats['per_hour'] = $this->obteComArray( $result );
			$stats['per_hour'] = $this->fill_data_hours_days( $stats['per_hour'], false );
		}

		$sql_per_user_status = 'select user_mood, count(*) as total from user_tandem where id_tandem in ' .
		                       '(select id from tandem where id_course=' . $this->escapeString( $course_id ) . $datesql . ') ' .
		                       'group by user_mood ';
		$result              = $this->consulta( $sql_per_user_status );
		if ( ! empty( $result ) ) {
			$array                    = $this->obteComArray( $result );
			$total                    = 0;
			$stats['per_user_status'] = array( 'smilies' => 0, 'neutral' => 0, 'sad' => 0, 'total' => 0 );
			foreach ( $array as $k ) {
				switch ( $k['user_mood'] ) {
					case 1:
						$stats['per_user_status']['smilies'] = intval( $k['total'] );
						break;
					case 2:
						$stats['per_user_status']['neutral'] = intval( $k['total'] );
						break;
					case 3:
						$stats['per_user_status']['sad'] = intval( $k['total'] );
						break;
				}
			}
			$total = $stats['per_user_status']['smilies'] + $stats['per_user_status']['neutral'] + $stats['per_user_status']['sad'];
			if ( $total == 0 ) {
				$total = 1;
			}
			$stats['per_user_status']['total'] = $total;
		}

//        $sql_exercises = 'select task_enjoyed, total_task_enjoyed, table_task_enjoyed.id, table_task_enjoyed.task_number, table_task_enjoyed.name, table_task_enjoyed.week, task_nervous, total_task_nervous, task_valoration, total_task_valoration
//                          from (
//                          select user_tandem_task1.task_enjoyed , count(*) total_task_enjoyed , exercise.id, task_number, exercise.name, course_exercise.week
//                          from tandem as tandem
//                          inner join exercise on tandem.id_exercise=exercise.id
//                          inner join course_exercise on course_exercise.id_course=tandem.id_course and course_exercise.id_exercise=exercise.id
//                          inner join user as user_host on user_host.id=tandem.id_user_host
//                          inner join user_course uc1 on uc1.id_user=user_host.id and uc1.is_instructor=0
//                          inner join user_tandem_task as user_tandem_task1 on user_tandem_task1.id_user=uc1.id_user and user_tandem_task1.id_tandem=tandem.id
//                          where tandem.id_course=' . $this->escapeString($course_id) . $datesql . '
//                          group by exercise.id, exercise.name, course_exercise.week, task_number, user_tandem_task1.task_enjoyed) as table_task_enjoyed,
//                          (select user_tandem_task1.task_nervous , count(*) total_task_nervous, exercise.id, task_number, exercise.name, course_exercise.week
//                          from tandem as tandem
//                          inner join exercise on tandem.id_exercise=exercise.id
//                          inner join course_exercise on course_exercise.id_course=tandem.id_course and course_exercise.id_exercise=exercise.id
//                          inner join user as user_host on user_host.id=tandem.id_user_host
//                          inner join user_course uc1 on uc1.id_user=user_host.id and uc1.is_instructor=0
//                          inner join user_tandem_task as user_tandem_task1 on user_tandem_task1.id_user=uc1.id_user and user_tandem_task1.id_tandem=tandem.id
//                          where tandem.id_course=' . $this->escapeString($course_id) . $datesql . '
//                          group by exercise.id, exercise.name, course_exercise.week, task_number, user_tandem_task1.task_nervous) as table_task_nervous,
//                          (select user_tandem_task1.task_valoration , count(*) total_task_valoration, exercise.id, task_number, exercise.name, course_exercise.week
//                          from tandem as tandem
//                          inner join exercise on tandem.id_exercise=exercise.id
//                          inner join course_exercise on course_exercise.id_course=tandem.id_course and course_exercise.id_exercise=exercise.id
//                          inner join user as user_host on user_host.id=tandem.id_user_host
//                          inner join user_course uc1 on uc1.id_user=user_host.id and uc1.is_instructor=0
//                          inner join user_tandem_task as user_tandem_task1 on user_tandem_task1.id_user=uc1.id_user and user_tandem_task1.id_tandem=tandem.id
//                          where tandem.id_course=' . $this->escapeString($course_id) . $datesql . '
//                          group by exercise.id, exercise.name, task_number, course_exercise.week, user_tandem_task1.task_nervous) as table_task_valoration
//                          where table_task_enjoyed.id=table_task_nervous.id and table_task_enjoyed.id=table_task_valoration.id and
//                          table_task_enjoyed.task_enjoyed=table_task_nervous.task_nervous and table_task_enjoyed.task_enjoyed=table_task_valoration.task_valoration';

		/*$result = $this->consulta($sql_exercises);
        if(!empty($result)) {
            $stats['exercises'] = $this->obteComArray($result);
        }*/

		return $stats;
	}

	/**
	 * @param $array
	 * @param $per_day
	 *
	 * @return array
	 */
	private function fill_data_hours_days( $array, $per_day ) {
		$hours = array(
			0  => 0,
			1  => 0,
			2  => 0,
			3  => 0,
			4  => 0,
			5  => 0,
			6  => 0,
			7  => 0,
			8  => 0,
			9  => 0,
			10 => 0,
			11 => 0,
			12 => 0,
			13 => 0,
			14 => 0,
			15 => 0,
			16 => 0,
			17 => 0,
			18 => 0,
			19 => 0,
			20 => 0,
			21 => 0,
			22 => 0,
			23 => 0
		);
		if ( $per_day ) {
			$normalized_array = array(
				'Sunday'    => $hours,
				'Monday'    => $hours,
				'Tuesday'   => $hours,
				'Wednesday' => $hours,
				'Thursday'  => $hours,
				'Friday'    => $hours,
				'Saturday'  => $hours
			);
		} else {
			$normalized_array = $hours;
		}
		foreach ( $array as $value ) {


			if ( $per_day ) {
				switch ( $value['day'] ) {
					case 1: //Sunday
						$key = 'Sunday';
						break;
					case 2: //Monday
						$key = 'Monday';
						break;
					case 3: //Tuesday
						$key = 'Tuesday';
						break;
					case 4: //Wednesday
						$key = 'Wednesday';
						break;
					case 5: //Thursday
						$key = 'Thursday';
						break;
					case 6: //Friday
						$key = 'Friday';
						break;
					case 7: //Saturday
						$key = 'Saturday';
						break;
				}
				if ( ! $normalized_array[ $key ][ $value['hour'] ] ) {
					$normalized_array[ $key ][ $value['hour'] ] = $value['total_tandems'];
				}
			} else {
				$normalized_array[ $value['hour'] ] = $value['total_tandems'];
			}
		}

		return $normalized_array;
	}

	/**
	 * @param $id_course
	 * @param $startDateRanking
	 * @param $endDateRanking
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function set_course_start_end_date_ranking(
		$id_course,
		$startDateRanking,
		$endDateRanking,
		$startHourRanking,
		$endHourRanking
	) {
		$result = $this->consulta( "UPDATE course SET startDateRanking = " .
		                           $this->escapeString( $startDateRanking ) . ", endDateRanking = " .
		                           $this->escapeString( $endDateRanking ) . ", startHourRanking = " .
		                           $this->escapeString( $startHourRanking ) . ", endHourRanking = " .
		                           $this->escapeString( $endHourRanking ) . " WHERE id = " . $this->escapeString( $id_course ) );

		return $result;
	}

	/**
	 * Deletes previous user ranking points
	 *
	 * @param $id_course
	 *
	 * @return bool|mysqli_result|resource
	 */
	public function delete_previous_ranking( $id_course ) {
		$result = $this->consulta( "delete from user_ranking  WHERE course_id = " . $this->escapeString( $id_course ) );

		return $result;
	}

	/**
	 * This function is executed to set time to zero when both participants accepted connection
	 *
	 * @param $id_tandem
	 *
	 * @return bool|mixed|mysqli_result|string
	 */
	public function set_time_to_zero( $id_tandem ) {

		$sql = 'update user_tandem set total_time=0, created=now() where id_tandem = ' . $id_tandem;
		$this->consulta( $sql );
		$sql = 'update user_tandem_task set total_time=0, created=now() where id_tandem = ' . $id_tandem;
		$this->consulta( $sql );
		$sql    = 'update user_tandem_task_question set total_time=0, created=now() where id_tandem = ' . $id_tandem;
		$result = $this->consulta( $sql );

		return $result;
	}

	/**
	 * Return the list of user waiting by language team
	 *
	 * @param $course_id
	 * @param $language
	 *
	 * @return array
	 */
	public function getUsersDetailsWaitingByLang( $course_id, $language ) {
		$sql    = 'SELECT DISTINCT `user`.fullname
                FROM waiting_room
                INNER JOIN waiting_room_user
                ON waiting_room.id=waiting_room_user.id_waiting_room
                INNER JOIN `user`
                ON `user`.id = waiting_room_user.id_user
                WHERE waiting_room.id_course=' . $this->escapeString( $course_id ) . '
                AND waiting_room.language = ' . $this->escapeString( $language ) . ' ';
		$result = $this->consulta( $sql );
		$rows   = array();
		if ( $this->numResultats( $result ) > 0 ) {
			$rows = $this->obteComArray( $result );
		}

		return $rows;
	}

	/**
	 * Return the list of user waiting
	 *
	 * @param $course_id
	 *
	 * @return array
	 */
	public function getUsersDetailsWaiting( $course_id ) {
		$sql    = 'SELECT DISTINCT `user`.fullname
                FROM waiting_room
                INNER JOIN waiting_room_user
                ON waiting_room.id=waiting_room_user.id_waiting_room
                INNER JOIN `user`
                ON `user`.id = waiting_room_user.id_user
                WHERE waiting_room.id_course=' . $this->escapeString( $course_id ) . ' ';
		$result = $this->consulta( $sql );
		$rows   = array();
		if ( $this->numResultats( $result ) > 0 ) {
			$rows = $this->obteComArray( $result );
		}

		return $rows;
	}

	/**
	 * Returns if user completed the questionnaire
	 *
	 * @param $id_course
	 * @param $id_user
	 *
	 * @return bool
	 */
	public function theUserCompletedFinalQuestionnaire( $id_course, $id_user ) {
		$sql       = 'select count(*) as total from user_course ' .
		             'inner join user on user.id = user_course.id_user ' .
		             'inner join course on course.id = user_course.id_course ' .
		             'inner join questionnaire_certificate on questionnaire_certificate.useremail = user.email ' .
		             'and questionnaire_certificate.courseKey = course.courseKey ' .
		             'where user_course.id_user = ' . $this->escapeString( $id_user ) . ' ' .
		             'and user_course.id_course = ' . $this->escapeString( $id_course ) . ' ' .
		             'and questionnaire_certificate.postsurvey = 1';
		$completed = false;
		$result    = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result    = $this->obteComArray( $result );
			$completed = $result[0]['total'] > 0;
		}

		return $completed;
	}

	/**
	 * @param $noTeamsMode
	 * @param $ft
	 */
	private function addSkillGrades( $noTeamsMode, &$ft ) {
		$overall_grade = $this->checkPartnerFeedback( $ft['id_tandem'], $ft['id'] );

		if ( $noTeamsMode ) {
			$grammaticalresource_tmp      = '';
			$lexicalresource_tmp          = '';
			$discoursemangement_tmp       = '';
			$pronunciation_tmp            = '';
			$interactivecommunication_tmp = '';
			if ( ! empty( $overall_grade ) ) {
				$overall_grade                = unserialize( $overall_grade );
				$grammaticalresource_tmp      = $overall_grade->grammaticalresource;
				$lexicalresource_tmp          = $overall_grade->lexicalresource;
				$discoursemangement_tmp       = $overall_grade->discoursemangement;
				$pronunciation_tmp            = $overall_grade->pronunciation;
				$interactivecommunication_tmp = $overall_grade->interactivecommunication;
			}
			$ft['grammaticalresource']      = $grammaticalresource_tmp;
			$ft['lexicalresource']          = $lexicalresource_tmp;
			$ft['discoursemangement']       = $discoursemangement_tmp;
			$ft['pronunciation']            = $pronunciation_tmp;
			$ft['interactivecommunication'] = $interactivecommunication_tmp;
		} else {
			$overall_grade_tmp      = '';
			$fluency_tmp            = '';
			$accuracy_tmp           = '';
			$pronunciation_tmp      = '';
			$vocabulary_tmp         = '';
			$grammar_tmp            = '';
			$other_observations_tmp = '';

			if ( ! empty( $overall_grade ) ) {
				$overall_grade          = unserialize( $overall_grade );
				$overall_grade_tmp      = $overall_grade->grade;
				$fluency_tmp            = $overall_grade->fluency;
				$accuracy_tmp           = $overall_grade->accuracy;
				$pronunciation_tmp      = $overall_grade->pronunciation;
				$vocabulary_tmp         = $overall_grade->vocabulary;
				$grammar_tmp            = $overall_grade->grammar;
				$other_observations_tmp = $overall_grade->other_observations;
			}
			$ft['overall_grade']      = $overall_grade_tmp;
			$ft['fluency']            = $fluency_tmp;
			$ft['accuracy']           = $accuracy_tmp;
			$ft['pronunciation']      = $pronunciation_tmp;
			$ft['vocabulary']         = $vocabulary_tmp;
			$ft['grammar']            = $grammar_tmp;
			$ft['other_observations'] = $other_observations_tmp;
		}
	}

	/**
	 * @param int $totalTimeInSeconds
	 * @param $ft
	 */
	private function addFormattedTandemAndSubtandemsDurations( $totalTimeInSeconds, &$ft ) {
		$total_time = $this->time_format( $totalTimeInSeconds );
		$subTP      = explode( ':', $total_time );
		if ( $subTP[0] > 0 ) {
			$subTimerP = substr( $subTP[0], 1 ) . ':' . $subTP[1] . ':' . $subTP[2];
		} else {
			$subTimerP = $subTP[1] . ':' . $subTP[2];
		}
		$task_tandemsSubTime = $this->getUserTandemTasksDurations( $ft['id_user'], $ft['id_tandem'] );
		$subTimer            = array();
		$i                   = 0;
		if ( ! empty( $task_tandemsSubTime ) ) {
			foreach ( $task_tandemsSubTime as $question ) {
				$secondsSt = isset( $question['total_time'] ) ? $question['total_time'] : 0;
				$obj       = secondsToTime( $secondsSt );
				$time      = '';
				if ( $obj['h'] > 0 ) {
					$time .= ( $obj['h'] < 10 ? '0' : '' ) . $obj['h'] . ':';
				}
				$time           .= ( $obj['m'] < 10 ? '0' : '' ) . $obj['m'] . ':';
				$time           .= ( $obj['s'] < 10 ? '0' : '' ) . $obj['s'];
				$subTimer[ $i ] = $time;
				$i ++;
			}
		}
		$ft['total_time']       = $subTimerP;
		$ft['total_time_tasks'] = $subTimer;
	}

	/*public function removeUserFromWaiting($id_course,$id_exercises) {
        $ret = true;
        if (count($id_exercises)>0) {
            $exercisesNotDone_str = implode(",",$id_exercises);
            $sql = 'UPDATE waiting_room set number_user_waiting=number_user_waiting-1 where id_course = ' . $id_course . '
            and id_exercise in (' . $exercisesNotDone_str . ')';
            $this->consulta($sql);
            $sqlDelete = 'DELETE from waiting_room where number_user_waiting<=0 and id_course = ' . $id_course;
            $ret = $this->consulta($sqlDelete);

        }
        return $ret;
    }*/

	/**
	 * @param int $courseid
	 *
	 * @return bool|array
	 */
	public function get_coursekey_from_courseid( $courseid ) {
		$sql    = 'SELECT courseKey FROM course WHERE id = ' . $this->escapeString( $courseid );
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return $row['courseKey'];
		}

		return false;
	}

	/**
	 * @param int $userId
	 * @param int $courseId
	 *
	 * @return bool[]
	 */
	public function get_user_survey_status( $userId, $courseId ) {
		require_once __DIR__ . '/moodleServicesConsumer.php';

		$preSurveyCompleted  = false;
		$postSurveyCompleted = false;

		if ( ! $userEmail = $this->getUserEmail( $userId ) ) {
			/** @noinspection ForgottenDebugOutputInspection */
			error_log( 'Unable to fetch user email for user ' . $userId );

			return [ $preSurveyCompleted, $postSurveyCompleted ];
		}

		if ( ! $courseKey = $this->get_coursekey_from_courseid( $courseId ) ) {
			/** @noinspection ForgottenDebugOutputInspection */
			error_log( 'Unable to fetch user survey status: courseKey not found for course ' . $courseId );

			return [ $preSurveyCompleted, $postSurveyCompleted ];
		}

		// Get recorded surveys status (if the questionnaire certificate record exists)
		if ( $questionnaire = $this->get_questionnaire_certificate_by_useremail_and_coursekey( $userEmail,
			$courseKey ) ) {
			$preSurveyCompleted  = $questionnaire['presurvey'];
			$postSurveyCompleted = $questionnaire['postsurvey'];
		} else {
			// Create a new questionnaire certificate record for this user
			$now                  = date( 'Y-m-d H:i:s' );
			$newQuestionnaireData = [ 'useremail' => $userEmail, 'courseKey' => $courseKey, 'created' => $now ];
			if ( ! $questionnaire = $this->create_questionnaire_certificate( $newQuestionnaireData ) ) {
				/** @noinspection ForgottenDebugOutputInspection */
				error_log( 'Unable to create new questionnaire record for user ' . $userEmail . ' and course ' . $courseKey );

				return [ $preSurveyCompleted, $postSurveyCompleted ];
			}
		}

		// If surveys are already recorded as completed, no need to request updated status to external Moodle services.
		if ( $preSurveyCompleted && $postSurveyCompleted ) {
			return [ $preSurveyCompleted, $postSurveyCompleted ];
		}

		// Request updated status to external Moodle service.
		$moodleCourseId = $this->getMoodleCourseIdFromCourseKey( $courseKey );
		if ( $moodleCourseId && $status = MoodleServicesConsumer::get_user_survey_status( $userEmail,
				$moodleCourseId ) ) {
			$updatedPreSurveyCompleted  = $status['presurveycompleted'];
			$updatedPostSurveyCompleted = $status['postsurveycompleted'];

			// Update status if any of the surveys has been completed.
			if ( ! $preSurveyCompleted && $updatedPreSurveyCompleted ) {
				/** @noinspection NestedPositiveIfStatementsInspection */
				if ( $this->set_user_survey_status_to_completed( $questionnaire['id'], 'pre' ) ) {
					$preSurveyCompleted = $updatedPreSurveyCompleted;
				}
			}
			if ( ! $postSurveyCompleted && $updatedPostSurveyCompleted ) {
				/** @noinspection NestedPositiveIfStatementsInspection */
				if ( $this->set_user_survey_status_to_completed( $questionnaire['id'], 'post' ) ) {
					$postSurveyCompleted = $updatedPostSurveyCompleted;
				}
			}
		}

		return [ $preSurveyCompleted, $postSurveyCompleted ];
	}

	/**
	 * @param string $surveyType Tandem survey type: 'pre', 'post'
	 * @param int $questionnaireId
	 *
	 * @return bool
	 */
	private function set_user_survey_status_to_completed( $questionnaireId, $surveyType = 'pre' ) {
		$targetField     = 'pre' === $surveyType ? 'presurvey' : 'postsurvey';
		$targetDateField = $targetField . '_date';
		$now             = date( 'Y-m-d H:i:s' );

		$sql = 'UPDATE questionnaire_certificate
                SET ' . $targetField . ' = 1,
                ' . $targetDateField . ' = ' . $this->escapeString( $now ) . '
                WHERE id = ' . $questionnaireId;

		return (bool) $this->consulta( $sql );
	}

	/**
	 * @param string $userEmail
	 * @param string $courseKey
	 *
	 * @return array|bool
	 */
	private function get_questionnaire_certificate_by_useremail_and_coursekey( $userEmail, $courseKey ) {
		$sql    = 'SELECT * FROM questionnaire_certificate
                WHERE useremail = ' . $this->escapeString( $userEmail ) . '
                AND courseKey = ' . $this->escapeString( $courseKey );
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return [
				'id'              => (int) $row['id'],
				'useremail'       => $row['useremail'],
				'courseKey'       => $row['courseKey'],
				'presurvey'       => (bool) $row['presurvey'],
				'presurvey_date'  => $row['presurvey_date'],
				'postsurvey'      => (bool) $row['postsurvey'],
				'postsurvey_date' => $row['postsurvey_date'],
				'created'         => $row['created'],
			];
		}

		return false;
	}

	/**
	 * @param array $data
	 *
	 * @return int|false
	 */
	private function create_questionnaire_certificate( $data ) {
		$sql = 'INSERT INTO questionnaire_certificate (useremail, courseKey, created)
                VALUES (' . $this->escapeString( $data['useremail'] ) . ',
                ' . $this->escapeString( $data['courseKey'] ) . ',
                ' . $this->escapeString( $data['created'] ) . ')';
		if ( $this->consulta( $sql ) && $id = $this->get_last_inserted_id() ) {
			return (int) $id;
		}

		return false;
	}

	/**
	 * Tries to obtain the Moodle course ID from the different types of course keys. Ex.:
	 * trainingspeakapps:3_117 => 3
	 * speakApps:244 => 244
	 * rug:e007a6a8f3dd4cecba2ec0e545ab4855 => false
	 * speakApps:184_speakApps:1680 => 184
	 *
	 * @param $courseKey
	 *
	 * @return bool
	 */
	public function getMoodleCourseIdFromCourseKey( $courseKey ) {
		$explodedCourseKey = explode( ':', $courseKey, 2 );
		if ( count( $explodedCourseKey ) !== 2 ) {
			return false;
		}

		$identifier = $explodedCourseKey[1];
		// If the identifier is a numeric string like '244', it should be the course id.
		if ( ctype_digit( $identifier ) ) {
			return (int) $identifier;
		}

		// If the identifier is an string like '3_117' or '184_speakApps:1680', the 1st number should be the course id.
		if ( strpos( $identifier, '_' ) !== false ) {
			$explodedIdentifier = explode( '_', $identifier, 2 );
			if ( ctype_digit( $explodedIdentifier[0] ) ) {
				return (int) $explodedIdentifier[0];
			}
		}

		// Any other case... No course id info :(
		return false;
	}

	/**
	 * @param $courseId
	 *
	 * @return array|false
	 */
	public function userTandemResults( $courseId ) {
		$sql    = "SELECT COUNT(tandem.id) AS `Number`, Fullname, Email, 
                CASE
                    WHEN (SELECT count(*) FROM waiting_room_user_history WHERE id_tandem=tandem.id) > 0
                    THEN 'Roulette Tandem'
                    ELSE 'YouChoose Tandem'
                END AS TandemType,
                CASE
                    WHEN (SELECT AVG(total_time) FROM user_tandem WHERE id_tandem=tandem.id) >60
                    THEN 'Success'
                    ELSE 'Failed'
                END AS TandemResult
                FROM tandem 
                INNER JOIN `user` ON (`user`.id = tandem.id_user_host OR `user`.id = tandem.id_user_guest)
                WHERE id_course = " . $this->escapeString( $courseId ) . '
                GROUP BY TandemType, TandemResult, fullname, email
                ORDER BY fullname ';
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * @param $course_id
	 *
	 * @return array|false
	 */
	public function resultWaitingRoom( $course_id ) {
		$sql    = "SELECT COUNT(*) total, u.email, u.fullname, DAY(w.created) AS `day`, MONTH(w.created) AS `month`, YEAR(w.created) AS `year`,
                CASE WHEN status = 'assigned' THEN 'Assigned'
                ELSE 'Give up'
                END AS Result
                FROM waiting_room_user_history AS uh
                INNER JOIN waiting_room_history w ON w.id_waiting_room = uh.id_waiting_room
                INNER JOIN `user` u ON u.id = uh.id_user
                WHERE w.id_course = " . $this->escapeString( $course_id ) . '
                GROUP BY status, DAY(w.created), MONTH(w.created), YEAR(w.created), u.email, u.fullname
                ORDER BY w.created ';
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * @param $tandem_id
	 *
	 * @return array|bool
	 */
	public function get_waiting_room_user_history_by_tandem( $tandem_id ) {
		$sql    = "select wruh.status, wruh.created_history, wrh.language, wrh.number_user_waiting, e.name, wrh.id_exercise from waiting_room_user_history wruh " .
		          "INNER JOIN waiting_room_history wrh on wrh.id_waiting_room=wruh.id_waiting_room " .
		          "left outer join exercise e on e.id = wrh.id_exercise " .
		          "where id_tandem =" . $this->escapeString( $tandem_id );
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * @param $tandem_id
	 *
	 * @return array|bool
	 */
	public function get_log_actions_by_tandem( $tandem_id ) {
		$sql    = "select l.page, l.params, l.ts, u.fullname, u.email from tandem_logs l " .
		          "INNER JOIN user u on u.id=l.id_user " .
		          "where l.id_tandem =" . $this->escapeString( $tandem_id );
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * @param $tandem_id
	 * @param $user_id
	 *
	 * @return array|bool
	 */
	public function partner_has_accessed_to_tandem( $tandem_id, $user_id ) {
		$sql = "select count(*) as total from tandem_logs l " .
		       "where l.id_tandem =" . $this->escapeString( $tandem_id ) . ' and l.id_user != ' . $this->escapeString( $user_id );

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$result = $this->obteComArray( $result );

			return $result[0]['total'] > 0;
		}

		return false;
	}

	/**
	 * @param $user_id_guest
	 * @param $user_id_host
	 * @param bool $date_before
	 * @param bool $date_after
	 * @param int $interval_to_subtract
	 * @param int $interval_to_add
	 *
	 * @return array|bool
	 */
	public function get_log_actions_by_users(
		$user_id_guest,
		$user_id_host,
		$date_before = false,
		$date_after = false,
		$interval_to_subtract = 15,
		$interval_to_add = 15
	) {
		$sql = 'select l.page, l.params, l.ts, u.fullname, u.email from tandem_logs l ' .
		       'INNER JOIN user u on u.id=l.id_user ' .
		       'where l.id_user in (' . $this->escapeString( $user_id_host ) . ', ' . $this->escapeString( $user_id_guest ) . ')';
		if ( $date_before ) {
			$sql .= ' AND l.ts >= ' . $this->escapeString( $date_before ) . ' - INTERVAL ' . $interval_to_subtract . ' MINUTE';
		}
		if ( $date_before ) {
			$sql .= ' AND l.ts <= ' . $this->escapeString( $date_after ) . ' + INTERVAL ' . $interval_to_add . ' MINUTE';
		}
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * @param $user_id
	 * @param $date_before
	 * @param $date_after
	 * @param $interval_to_subtract
	 * @param $interval_to_add
	 *
	 * @return array|bool
	 */
	public function get_log_actions_by_user(
		$user_id,
		$date_before = false,
		$date_after = false,
		$interval_to_subtract = 15,
		$interval_to_add = 15
	) {
		$sql = "select l.page, l.params, l.ts from tandem_logs l " .
		       "where l.id_user =" . $this->escapeString( $user_id );
		if ( $date_before ) {
			$sql .= ' AND l.ts >= ' . $this->escapeString( $date_before ) . ' - INTERVAL ' . $interval_to_subtract . ' MINUTE';
		}
		if ( $date_before ) {
			$sql .= ' AND l.ts <= ' . $this->escapeString( $date_after ) . ' + INTERVAL ' . $interval_to_add . ' MINUTE';
		}
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * Logs an user action
	 *
	 * @param $user_id
	 * @param $course_register_id
	 * @param $id_register_tandem
	 * @param $page
	 * @param $params
	 *
	 * @return bool|mysqli_result|string
	 */
	public function logAction( $user_id, $course_register_id, $id_register_tandem, $page, $params ) {
		$sql = 'INSERT INTO tandem_logs (id_user, id_course, id_tandem, page, params)
                VALUES (' . $this->escapeString( $user_id ) . ',
                ' . $this->escapeString( $course_register_id ) . ',
                ' . $this->escapeString( $id_register_tandem ) . ',
                ' . $this->escapeString( $page ) . ',
                ' . $this->escapeString( $params ) . ')';

		$this->update_last_action_time( $user_id, $course_register_id );

		return $this->consulta( $sql );
	}

	/**
	 * @param $user_id
	 * @param $lang
	 *
	 * @return array|false
	 */
	public function get_questions_quiz( $user_id, $lang ) {
		$sql    = "SELECT *
                FROM tandem_quiz
                WHERE language in (" . $this->escapeString( $lang ) . ", 'all') 
                AND active=1 
                ORDER BY RAND()";
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * @param $user_id
	 * @param $lang
	 *
	 * @return array|false
	 */
	public function get_questions_quiz_manage( $question_id = 0 ) {
		$where = '';
		if ( $question_id > 0 ) {
			$where = ' AND id = ' . $this->escapeString( $question_id );
		}
		$sql    = "SELECT *
                FROM tandem_quiz
                WHERE 1=1 $where
                ORDER BY language, active";
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$arr = $this->obteComArray( $result );
			if ( $question_id > 0 ) {
				return count( $arr ) > 0 ? $arr[0] : false;
			} else {
				return $arr;
			}
		}

		return false;
	}

	/**
	 * @param $question_id
	 *
	 * @return array|bool
	 */
	public function get_questions_answers( $question_id ) {
		$sql    = "SELECT *
                FROM tandem_quiz_answer
                WHERE quiz_id = " . $this->escapeString( $question_id ) . " 
                ORDER BY answer";
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	/**
	 * @param $question_id
	 * @param $title
	 * @param $language
	 * @param $question
	 * @param $correctAnswer
	 * @param $correctAnswerText
	 * @param $falseAnswerText
	 * @param $active
	 * @param $category
	 * @param $answersTags
	 *
	 * @return mixed
	 */
	public function saveQuizQuestion(
		$question_id,
		$title,
		$language,
		$question,
		$correctAnswer,
		$correctAnswerText,
		$falseAnswerText,
		$active,
		$category,
		$answersTags
	) {
		if ( $question_id > 0 ) {
			$sql    = 'UPDATE tandem_quiz set title=' . $this->escapeString( $title ) . ', language=' .
			          $this->escapeString( $language ) . ', question=' . $this->escapeString( $question ) .
			          ', correctAnswer=' . $this->escapeString( $correctAnswer ) .
			          ', correctAnswerText=' . $this->escapeString( $correctAnswerText ) .
			          ', falseAnswerText = ' . $this->escapeString( $falseAnswerText ) .
			          ', category = ' . $this->escapeString( $category ) .
			          ', active=' . $this->escapeString( $active ) .
			          ',modified=now() WHERE id = ' . $question_id;
			$result = $this->consulta( $sql );
			if ( ! $result ) {
				die( "Error updating!! " . print_r( $this->conn->error, true ) );
			}
			$result = $this->consulta( 'DELETE FROM tandem_quiz_answer where quiz_id = ' . $question_id );
		} else {
			$sql    = 'INSERT INTO tandem_quiz (title, language, question, correctAnswer, correctAnswerText, falseAnswerText, category, active, created, modified)
                                                                    VALUES (' . $this->escapeString( $title ) . ',' . $this->escapeString( $language ) . ',' . $this->escapeString( $question ) . ',' . $this->escapeString( $correctAnswer ) . ',' . $this->escapeString( $correctAnswerText ) . ',' .
			          $this->escapeString( $falseAnswerText ) . ',' . $this->escapeString( $category ) . ',' . $this->escapeString( $active ) . ', ' .
			          ' now(),  now())';
			$result = $this->consulta( $sql );
			if ( ! $result ) {
				die( "Error!! " . print_r( $this->conn->error, true ) );
			}
			$question_id = $this->get_last_inserted_id();
		}
		foreach ( $answersTags as $key => $answersTag ) {
			if ( ! empty( $answersTag ) ) {
				$sql    = 'INSERT INTO tandem_quiz_answer (quiz_id, answer, answerText, created, modified)
                                                                        VALUES (' . $question_id . ',' . $this->escapeString( $key ) . ',' . $this->escapeString( $answersTag ) . ', ' .
				          ' now(),  now())';
				$result = $this->consulta( $sql );
				if ( ! $result ) {
					die( "Error!! " . print_r( $this->conn->error, true ) . '<p>' . $sql . '</p>' );
				}
			}

		}

		return $question_id;
	}

	/**
	 * @param $id_tandem
	 * @param $id_user
	 *
	 * @return array|bool
	 */
	public function get_task_valoration( $id_tandem, $id_user ) {
		$sql    = 'select * from user_tandem_task where  id_user = ' . $this->escapeString( $id_user ) . '  and id_tandem=' . $this->escapeString( $id_tandem );
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	public function get_all_my_feedback_ratings( $id_user, $course_id ) {
		$sql    = 'select rating_partner_feedback_form from feedback_tandem_form where ' .
		          'id_feedback_tandem in 
		       (select id from feedback_tandem where id_partner =  ' . $this->escapeString( $id_user ) . ' and id_tandem in ' .
		          '(
					select id from tandem t
			                    inner join  user_tandem AS UT on UT.id_tandem = t.id and UT.id_user = ' . $this->escapeString( $id_user ) . ' and ((COALESCE(UT.finalized, 0) = 0 AND UT.total_time > 600) OR (UT.finalized IS NOT NULL AND UT.is_finished = 1))
					where 
					(
						(id_user_host = ' . $this->escapeString( $id_user ) . ' or id_user_guest = ' . $this->escapeString( $id_user ) . ') and id_course = ' . $this->escapeString( $course_id ) . '
					)
				 )' .
		          ') and rating_partner_feedback_form is not null';
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}

	public function get_num_tandems_by_person( $course_id, $user_id ) {
		$sql    = 'SELECT FT.id_partner, count(FT.id_partner) as total
                  FROM user_tandem AS UT
             LEFT JOIN feedback_tandem AS FT ON FT.id_tandem = UT.id_tandem AND FT.id_user = UT.id_user
             LEFT JOIN feedback_tandem_form AS FTF ON FTF.id_feedback_tandem = FT.id
             LEFT JOIN feedback_tandem as sFT ON sFT.id_tandem = UT.id_tandem AND sFT.id_user = FT.id_partner
             LEFT JOIN feedback_tandem_form AS sFTF ON sFTF.id_feedback_tandem = sFT.id
            INNER JOIN tandem AS T ON T.id = UT.id_tandem
                 WHERE ((COALESCE(UT.finalized, 0) = 0 AND UT.total_time > 600) OR (UT.finalized IS NOT NULL AND UT.is_finished = 1))
                   AND FT.id_partner IS NOT NULL
                   AND T.id_course = ' . $this->escapeString( $course_id ) . ' 
                   AND FTF.feedback_form is not null
                    AND UT.id_user = ' . $this->escapeString( $user_id ) . ' 
                    group by FT.id_partner';
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return array();
	}


	/**
	 * Get previous winners
	 *
	 * @param $course_id
	 *
	 * @return array
	 */
	public function getWinnersPreviousWeeksUsersRanking( $course_id ) {

		$winners = array();
		$sql     = 'select * from user_ranking where badge_week_ranking = 1 and course_id = ' . $this->escapeString( $course_id );
		$result  = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$data = $this->obteComArray( $result );
			foreach ( $data as $key => $value ) {
				$winners[ $value['user_id'] ] = $value['lang'];
			}
		}

		return $winners;
	}

	public function setWinnersBadge( $course_id, $winners ) {
		foreach ( $winners as $user_id => $lang ) {
			$ranking           = $this->getRankingUserData( $user_id, $course_id );
			$points            = 0;
			$total_time        = 0;
			$number_of_tandems = 0;
			if ( ! $ranking ) {

				$sql = 'INSERT INTO user_ranking (user_id, course_id, badge_week_ranking, points, lang, total_time, '
				       . ' number_of_tandems) VALUES ('
				       . $this->escapeString( $user_id ) . ','
				       . $this->escapeString( $course_id ) . ','
				       . '1,'
				       . $points . ','
				       . $this->escapeString( $lang ) . ','
				       . $this->escapeString( $total_time ) . ','
				       . $this->escapeString( $number_of_tandems ) . ')';
				$this->consulta( $sql );
			} else {
				$sql = 'UPDATE user_ranking SET '
				       . 'badge_week_ranking = 1'
				       . ' WHERE user_id  = ' . $this->escapeString( $user_id )
				       . ' AND course_id = ' . $this->escapeString( $course_id );
				$this->consulta( $sql );

			}
		}

	}


	/**
	 * @param $user_id
	 * @param $course_id
	 * @param $lang
	 * @param $number_of_tandems
	 * @param $total_time
	 * @param $debug
	 */
	private function calculateBadges( $user_id, $course_id, $lang, $number_of_tandems, $total_time, $debug ) {
		$ranking = $this->getRankingUserData( $user_id, $course_id );
		if ( $debug ) {
			var_dump( $ranking );
		}
		$points = 0;
		if ( ! $ranking ) {

			$sql = 'INSERT INTO user_ranking (user_id, course_id, points, lang, total_time, '
			       . ' number_of_tandems) VALUES ('
			       . $this->escapeString( $user_id ) . ','
			       . $this->escapeString( $course_id ) . ','
			       . $points . ','
			       . $this->escapeString( $lang ) . ','
			       . $this->escapeString( $total_time ) . ','
			       . $this->escapeString( $number_of_tandems ) . ')';
			$this->consulta( $sql );
			$ranking = $this->getRankingUserData( $user_id, $course_id );
		}
		if ( $ranking ) {

			$done_intro_quiz = true;

			$ret_total = $this->getUserRankingPoints( $user_id, $course_id, $debug, false );

			$number_of_tandems_total = 0;
			$total_time_total        = 0;

			if ( $ret_total->points ) {
				$total_time_total        = $ret_total->total_time;
				$number_of_tandems_total = $ret_total->number_of_tandems;
			}

			$level = TandemBadges::get_level_badge( $done_intro_quiz, $number_of_tandems_total,
				$total_time_total / 60 );
			if ( $ranking['badge_level'] != $level ) {
				$sql = 'UPDATE user_ranking SET '
				       . 'badge_level = ' . $level
				       . ' WHERE user_id  = ' . $this->escapeString( $user_id )
				       . ' AND course_id = ' . $this->escapeString( $course_id );
				$this->consulta( $sql );
			}
			if ( $this->badgeUpdate( $ranking, 'badge_feedback_expert', $user_id, $course_id, $debug ) ) {
				$points += 50;
			}
			if ( $this->badgeUpdate( $ranking, 'badge_loyalty', $user_id, $course_id, $debug ) ) {
				$points += 50;
			}
			if ( $this->badgeUpdate( $ranking, 'badge_social', $user_id, $course_id, $debug ) ) {
				$points += 50;
			}
			if ( $this->badgeUpdate( $ranking, 'badge_forms', $user_id, $course_id, $debug ) ) {
				$points += 50;
			}
		}

		return $points;
	}

	private function badgeUpdate(
		$ranking,
		$field,
		$user_id,
		$course_id,
		$debug = false
	) {

		$add_points = $ranking[ $field ] == 1;
		if ( ! isset( $ranking[ $field ] ) || $ranking[ $field ] != 1 ) {
			if ( TandemBadges::check_badge_criteria( $field, $this, $user_id, $course_id ) ) {
				$sql = 'UPDATE user_ranking SET '
				       . $field . ' = 1 '
				       . ' WHERE user_id  = ' . $this->escapeString( $user_id )
				       . ' AND course_id = ' . $this->escapeString( $course_id );
				$this->consulta( $sql );
				$add_points = true;

			} elseif ( $debug ) {
				echo "<p>No has $field</p>";
			}

		} elseif ( $debug ) {
			echo "<p>Has $field we don't do anything</p>";
		}

		return $add_points;
	}

	/**
	 * Get pending recordings to upload to S3
	 *
	 * @param $course_id
	 *
	 * @return array|bool
	 */
	public function get_possible_recordings_to_be_processed( $course_id ) {
		$sql    = 'select ft.id_tandem from feedback_tandem ft ' .
		          'inner join tandem t on t.id = ft.id_tandem and t.id_course=' . $this->escapeString( $course_id ) . ' ' .
		          'where ft.external_video_url is null   
		          and ft.id_tandem > 0 and ft.created <= DATE_SUB(NOW(), INTERVAL 3 HOUR) and ft.created >= DATE_SUB(NOW(), INTERVAL 6 DAY)  group by ft.id_tandem';
		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			return $this->obteComArray( $result );
		}

		return false;
	}


	/**
	 * Store recording information
	 *
	 * @param $recordingId
	 * @param $tandemId
	 * @param $name
	 * @param $isPublished
	 * @param $state
	 * @param $startTime
	 * @param $endTime
	 * @param $playbackType
	 * @param $playbackUrl
	 * @param $presentationUrl
	 * @param $podcastUrl
	 * @param $statisticsUrl
	 * @param $playbackLength
	 * @param $metas
	 *
	 * @return bool|mysqli_result|string
	 */
	public function storeRecordInformation(
		$recordingId,
		$tandemId,
		$name,
		$isPublished,
		$state,
		$startTime,
		$endTime,
		$playbackType,
		$playbackUrl,
		$presentationUrl,
		$podcastUrl,
		$statisticsUrl,
		$playbackLength,
		$metas
	) {
		$sql    = false;
		$result = $this->consulta( "SELECT * FROM recording_info where recordingId = " . $this->escapeString( $recordingId ) );
		if ( $this->numResultats( $result ) == 0 ) {

			$sql = 'INSERT INTO recording_info 
    			(recordingId, tandemId, name, isPublished, state, startTime, endTime, playbackType, playbackUrl, presentationUrl, podcastUrl, statisticsUrl, playbackLength, metas, created, udpated) 
    			VALUES ('
			       . $this->escapeString( $recordingId ) . ',' . $this->escapeString( $tandemId ) .
			       ' ,' . $this->escapeString( $name ) .
			       ' ,' . $this->escapeString( $isPublished ) .
			       ' ,' . $this->escapeString( $state ) .
			       ' ,' . $this->escapeString( $startTime ) .
			       ' ,' . $this->escapeString( $endTime ) .
			       ' ,' . $this->escapeString( $playbackType ) .
			       ' ,' . $this->escapeString( $playbackUrl ) .
			       ' ,' . $this->escapeString( $presentationUrl ) .
			       ' ,' . $this->escapeString( $podcastUrl ) .
			       ' ,' . $this->escapeString( $statisticsUrl ) .
			       ' ,' . $this->escapeString( $playbackLength ) .
			       ' ,' . $this->escapeString( json_encode( $metas ) ) .
			       ' , now(), now())';
		} else {
			$row = $this->obteObjecteComArray( $result );

			$set = '';
			if ( $playbackType != null && $row['playbackType'] == null ) {
				$set .= ( strlen( $set ) == 0 ? '' : ',' ) . ' playbackType = ' . $this->escapeString( $playbackType );
			}
			if ( $playbackUrl != null && $row['playbackUrl'] == null ) {
				$set .= ( strlen( $set ) == 0 ? '' : ',' ) . ' playbackUrl = ' . $this->escapeString( $playbackUrl );
			}
			if ( $presentationUrl != null && $row['presentationUrl'] == null ) {
				$set .= ( strlen( $set ) == 0 ? '' : ',' ) . ' presentationUrl = ' . $this->escapeString( $presentationUrl );
			}
			if ( $podcastUrl != null && $row['podcastUrl'] == null ) {
				$set .= ( strlen( $set ) == 0 ? '' : ',' ) . ' podcastUrl = ' . $this->escapeString( $podcastUrl );
			}
			if ( $statisticsUrl != null && $row['statisticsUrl'] == null ) {
				$set .= ( strlen( $set ) == 0 ? '' : ',' ) . ' statisticsUrl = ' . $this->escapeString( $statisticsUrl );
			}
			if ( $playbackLength != null && $row['playbackLength'] == null ) {
				$set .= ( strlen( $set ) == 0 ? '' : ',' ) . ' playbackLength = ' . $this->escapeString( $playbackLength );
			}
			if ( strlen( $set ) > 0 ) {
				$sql = 'UPDATE recording_info set ' . $set . ' WHERE recordingId = ' . $this->escapeString( $recordingId );
			}
		}

		return $sql ? $this->consulta( $sql ) : true;
	}

	/**
	 * @param $tandem_id
	 *
	 * @return array
	 */
	public function getRecordingsByTandemId( $tandem_id ) {
		$sql        = 'select * from recording_info where tandemId = ' . $this->escapeString( $tandem_id );
		$result     = $this->consulta( $sql );
		$recordings = array();
		if ( $result ) {
			$recordings = $this->obteComArray( $result );
		}

		return $recordings;

	}


	/**
	 * Updtes the id_external_tool based on id_tandem
	 *
	 * @param $id_tandem
	 * @param $id_external_tool
	 * @param $end_external_service
	 *
	 * @return bool|mysqli_result|resource
	 */
	/**
	 *
	 * @param $id_tandem
	 * @param $id_user
	 * @param $id_course
	 * @param $has_accepted_audio
	 * @param $has_accepted_video
	 *
	 * @return bool|mysqli_result|string
	 */
	public function updateAcceptedVideochat(
		$id_tandem,
		$id_user,
		$has_accepted_audio = false,
		$has_accepted_video = false
	) {

		// 1st get data
		if ( $has_accepted_audio || $has_accepted_video ) {

			$row = $this->getFeedbackByIdTandemIdUser( $id_tandem, $id_user );
			if ( $row ) {
				$updateSQL = "UPDATE feedback_tandem SET FIELD_TO_UPDATE = now() where id_tandem = " . $this->escapeString( $id_tandem ) .
				             " AND id_user = " . $this->escapeString( $id_user );

				$id_course = false;
				if ( $has_accepted_audio && empty( $row['accepted_audio_datetime'] ) ) {
					$this->consulta( str_replace( 'FIELD_TO_UPDATE', 'accepted_audio_datetime', $updateSQL ) );
					$id_course = $this->getCourseIdFromTandem( $id_tandem );
					$this->logAction( $id_user, $id_course, $id_tandem, 'accepted_audio', '' );
				}
				if ( $has_accepted_video && empty( $row['accepted_video_datetime'] ) ) {
					$this->consulta( str_replace( 'FIELD_TO_UPDATE', 'accepted_video_datetime', $updateSQL ) );
					$id_course = $id_course ? $id_course : $this->getCourseIdFromTandem( $id_tandem );
					$this->logAction( $id_user, $id_course, $id_tandem, 'accepted_video', '' );
				}

			}
		}


	}

	public function getFeedbackByIdTandemIdUser( $id_tandem, $id_user ) {
		$result = $this->consulta( "SELECT * FROM feedback_tandem where id_tandem = " . $this->escapeString( $id_tandem ) .
		                           " AND id_user = " . $this->escapeString( $id_user ) );
		if ( $this->numResultats( $result ) > 0 ) {


			$row = $this->obteObjecteComArray( $result );

			return $row;
		}

		return false;
	}

	private function getCourseIdFromTandem( $id ) {
		$sql       = 'select id_course from tandem where id = ' . $this->escapeString( $id );
		$course_id = false;
		$result    = $this->consulta( $sql );
		if ( $result ) {
			$result    = $this->obteObjecteComArray( $result );
			$course_id = $result['id_course'];
		}

		return $course_id;

	}

	/**
	 * Register subscrition
	 *
	 * @param $user_id
	 * @param $subscriber_id
	 *
	 * @return bool|mysqli_result|string
	 */
	public function register_push_subscription( $user_id, $subscriber_id ) {
		$sql = 'INSERT INTO notification_registration 
    			(subscriber_id, user_id, created) 
    			VALUES (' .
		       ' ' . $this->escapeString( $subscriber_id ) .
		       ' ,' . $this->escapeString( $user_id ) .
		       ' , now())';

		return $this->consulta( $sql );
	}

	public function log_push_notification(
		$user_id,
		$tandem_id,
		$title,
		$message,
		$url,
		$extra_params,
		$external_notification_id
	) {
		$sql = 'INSERT INTO notification_log 
    			(user_id, tandem_id, title, message, url, extra_params, external_notification_id, created) 
    			VALUES (' .
		       ' ' . $this->escapeString( $user_id ) .
		       ' ,' . $this->escapeString( $tandem_id ) .
		       ' ,' . $this->escapeString( $title ) .
		       ' ,' . $this->escapeString( $message ) .
		       ' ,' . $this->escapeString( $url ) .
		       ' ,' . $this->escapeString( $extra_params ) .
		       ' ,' . $this->escapeString( $external_notification_id ) .
		       ' , now())';

		return $this->consulta( $sql );
	}

	public function get_push_subscription_by_user_id( $user_id ) {
		$sql           = 'select subscriber_id from notification_registration where user_id = ' . $this->escapeString( $user_id );
		$result        = $this->consulta( $sql );
		$subscriptions = array();
		if ( $result ) {
			$subscriptions = $this->obteComArray( $result );

		}

		return $subscriptions;
	}

	public function get_push_subscription_by_lang( $course_id, $lang ) {
		$sql = 'select n.subscriber_id from user_course  uc
inner join notification_registration n on n.user_id = uc.id_user
where uc.id_course = ' . $this->escapeString( $course_id ) . ' and uc.inTandem = 0 ';
		if ( ! empty( $lang ) ) {
			$sql .= ' and uc.language != ' . $this->escapeString( $lang );
		}
		$result        = $this->consulta( $sql );
		$subscriptions = array();
		if ( $result ) {
			$subscriptions = $this->obteComArray( $result );
		}

		return $subscriptions;
	}

	/**
	 * Return last information connection
	 *
	 * @param $id_user
	 * @param $id_course
	 *
	 * @return array|bool|null
	 */
	public function get_last_action_time( $id_user, $id_course ) {
		$sql = "select min(minutesDiff) as minutesDiff, max(lastActionTime) as lastActionTime , now() as now from
                                                                                                  (
select TIMESTAMPDIFF(MINUTE, lastActionTime	, NOW()) as minutesDiff, lastActionTime as lastActionTime from user_course  
		       where id_user = " . $this->escapeString( $id_user ) . "
		        AND id_course =  " . $this->escapeString( $id_course ) . "
		        union		        
		        select TIMESTAMPDIFF(MINUTE, us.updated	, NOW()) as minutesDiff, us.updated as lastActionTime from  user_tandem as us
		        inner join tandem as t on t.`id` = us.id_tandem		        
		        		       where us.id_user = " . $this->escapeString( $id_user ) . "
		        		       AND t.id_course =  " . $this->escapeString( $id_course ) . "
		        		       order by minutesDiff asc limit 1
	)	        as a";

		$result = $this->consulta( $sql );
		if ( $this->numResultats( $result ) > 0 ) {
			$row = $this->obteObjecteComArray( $result );

			return $row;
		}

		return false;
	}

	public function update_last_action_time( $id_user, $id_course ) {
		$sql = "UPDATE user_course  set lastActionTime=now() " .
		       "where id_user =" . $this->escapeString( $id_user ) .
		       " AND id_course =" . $this->escapeString( $id_course );

		$result = $this->consulta( $sql );

		return $result;
	}

	public function update_user_course_language( $id_user, $id_course, $language ) {
		$sql = "UPDATE user_course  set language= " . $this->escapeString( $language ) . " " .
		       "where id_user =" . $this->escapeString( $id_user ) .
		       " AND id_course =" . $this->escapeString( $id_course );

		$result = $this->consulta( $sql );

		return $result;
	}

	public function get_access_per_15minuts( $id_course ) {
		$sql = "SELECT DATE_FORMAT( FROM_UNIXTIME( period * ( 15 *60 ) ) , '%Y-%m-%d' ) AS Date, TIME( FROM_UNIXTIME( period * ( 15 *60 ) ) ) AS Time, online_users
FROM (SELECT ROUND( UNIX_TIMESTAMP(ts) / ( 15 *60 ) ) AS period , COUNT( DISTINCT id_user ) AS online_users
FROM tandem_logs 
where id_course = " . $this->escapeString( $id_course ) . "
GROUP BY period) AS concurrent_users_report ";

		$result = $this->consulta( $sql );

		$rows = array();
		if ( $result ) {
			$rows = $this->obteComArray( $result );
		}

		return $rows;
	}


	/**
	 * Get user language
	 *
	 * @param $id_course
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function get_user_language( $id_course, $user_id ) {
		$language = false;

		$sql    = 'select language from user_course where id_course = ' . $id_course . ' and id_user = ' . $user_id;
		$result = $this->consulta( $sql );

		if ( $this->numResultats( $result ) == 1 ) {
			$langs    = array_values( $this->obteComArray( $result ) );
			$language = $langs[0]['language'];
		}


		return $language;
	}
	/**
	 * Get if user is instructor
	 *
	 * @param $id_course
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function get_user_is_instructor( $id_course, $user_id ) {
		$is_instructor = false;

		$sql    = 'select is_instructor from user_course where id_course = ' . $id_course . ' and id_user = ' . $user_id;
		$result = $this->consulta( $sql );

		if ( $this->numResultats( $result ) == 1 ) {
			$langs    = array_values( $this->obteComArray( $result ) );
			$is_instructor = $langs[0]['is_instructor'] == 1;
		}


		return $is_instructor;
	}


} // End of class.

