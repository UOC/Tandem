<?php

// Include the SDK
require_once 'constants.php';
require_once(dirname(__FILE__) . '/../config.inc.php');

class GestorBD {

    private $conn;
    private $_debug = false;

    public function __construct() {
        $this->conectar();
    }

    public function conectar() {
        $this->conn = mysql_connect(BD_HOST, BD_USERNAME, BD_PASSWORD);
        if (!$this->conn) {
            die('No se pudo conectar: ' . mysql_error());
            return false;
        } else {
            mysql_select_db(BD_NAME, $this->conn);
            return true;
        }
    }

    public function enableDebug() {
        $this->_debug = true;
    }

    public function disableDebug() {
        $this->_debug = false;
    }

    public function debugMessage($msg) {
        if ($this->_debug) {
            echo "<p>DEBUG: " . $msg . "</p>";
        }
    }

    public function desconectar() {
        mysql_close($this->conn);
    }

    public function escapeString($str) {
        return "'" . mysql_real_escape_string($str, $this->conn) . "'";
    }

    public function consulta($query) {
        $this->debugMessage($query);
        $result = mysql_query($query, $this->conn);
        if (!$result) {
            error_log("Error BD " . mysql_error() . $query);
        }
        return $result;
    }

    // Let's do it private -> public cmoyas
    public function obteObjecteComArray($result) {
        return mysql_fetch_assoc($result);
    }

    public function numResultats($result) {
        return mysql_num_rows($result);
    }

    public function obteComArray($result) {
        $rows = array();
        while ($row = mysql_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 
     * Obte si existeix l'usuari 
     * @param unknown_type $userKey
     * @return array|boolean
     */
    public function get_user_by_username($userKey) {
        $row = false;
        $result = $this->consulta("SELECT * FROM user where username = " . $this->escapeString($userKey));
        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
        }
        return $row;
    }

    /**
     * 
     * Register user in database
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
     */
    public function register_user($username, $name, $surname, $fullname, $email, $image, $icq = '', $skype = '', $yahoo = '', $msn = '') {
        $result = false;
        $sql = 'INSERT INTO user (username, firstname, surname, fullname, email, image, icq, skype, yahoo, msn, last_session,  blocked, created) 
                												VALUES (' . $this->escapeString($username) . ',' . $this->escapeString($name) . ',' . $this->escapeString($surname) . ',' . $this->escapeString($fullname) . ',' . $this->escapeString($email) . ',' .
                $this->escapeString($image) . ',' . $this->escapeString($icq) . ', ' . $this->escapeString($skype) . ', ' . $this->escapeString($yahoo) . ', ' . $this->escapeString($msn) . ',' .
                ' now(), 0, now())';
        $result = $this->consulta($sql);
        return $result;
    }

    /**
     * 
     * Updates user data
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
     * @param boolean $update_profile
     * @return resource
     */
    public function update_user($username, $name, $surname, $fullname, $email, $image = '', $icq = '', $skype = '', $yahoo = '', $msn = '', $update_profile = true) {
        $result = false;
        $sql = 'UPDATE user SET firstname = ' . $this->escapeString($name) . ', surname = ' . $this->escapeString($surname) . ', fullname = ' . $this->escapeString($fullname) . ', email = ' . $this->escapeString($email) . ', last_session=now(), image = ' . $this->escapeString($image) . ' ';

        if ($update_profile)
            $sql .= ',icq = ' . $this->escapeString($icq) . ', skype = ' . $this->escapeString($skype) . ', yahoo = ' . $this->escapeString($yahoo) . ', msn = ' . $this->escapeString($msn);

        $sql .= ' WHERE username = ' . $this->escapeString($username);
        $result = $this->consulta($sql);
        return $result;
    }

    /**
     *
     * Obte si existeix el curs
     * @param unknown_type $courseKey
     * @return array|boolean
     */
    public function get_course_by_id($id) {
        $result = $this->consulta("SELECT * FROM course where id = " . $this->escapeString($id));
        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            return $row;
        } else {
            return false;
        }
    }

    /**
     *
     * Obte si existeix el curs
     * @param unknown_type $courseKey
     * @return array|boolean
     */
    public function get_course_by_courseKey($courseKey) {
        $result = $this->consulta("SELECT * FROM course where courseKey = " . $this->escapeString($courseKey));
        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            return $row;
        } else {
            return false;
        }
    }

    /**
     *
     * Obte si usuari está comunicant
     * @param unknown_type $user 
     * @param unknown_type $id_course
     * @return int
     */
    public function get_userInTandem($user, $id_course) {
        
        $result = $this->consulta("SELECT * FROM user_course WHERE id_user=" . $this->escapeString($user) . " AND id_course=" . $this->escapeString($id_course));
        $row = mysql_fetch_object($result);
        return $row->inTandem;
    }

    /**
     *
     * Set si usuari está comunicant
     * @param unknown_type $id_user 
     * @param unknown_type $id_course 
     * @param unknown_type $status 
     * @return sql
     */
    public function set_userInTandem($user, $id_course, $status) {
        $result = $this->consulta("UPDATE user_course SET inTandem = " . $status . " WHERE id_user = " . $this->escapeString($user) . " AND id_course=" . $this->escapeString($id_course));
        return $result;
    }

    /**
     *
     * Set date des de que usuari está comunicant
     * @param unknown_type $id_user 
     * @param unknown_type $id_course 
     * @param unknown_type $date 
     * @return sql
     */
    public function set_userLastAccess($user, $id_course, $date) {
        $result = $this->consulta("UPDATE user_course SET lastAccessTandem = " . $this->escapeString($date) . " WHERE id_user = " . $this->escapeString($user) . " AND id_course=" . $this->escapeString($id_course));
        return $result;
    }

    /**
     *
     * Get date des de que usuari está comunicant
     * @param unknown_type $id_user 
     * @param unknown_type $id_course 
     * @return sql
     */
    public function get_lastAccessTandemStatus($user, $id_course) {
        $result = $this->consulta("SELECT * FROM user_course WHERE id_user=" . $this->escapeString($user) . " AND id_course=" . $this->escapeString($id_course));
        $row = mysql_fetch_object($result);
        return $row->lastAccessTandem;
    }

    /**
     *
     * Register courseKey in database
     * @param unknown_type $courseKey
     * @param unknown_type $title
     * @return boolean
     */
    public function register_course($courseKey, $title) {
        $result = false;
        $sql = 'INSERT INTO course (courseKey, title, created)
                				VALUES (' . $this->escapeString($courseKey) . ',' . $this->escapeString($title) . ', now())';
        $result = $this->consulta($sql);
        return $result;
    }

    /**
     *
     * Updates course data
     * @param unknown_type $courseKey
     * @param unknown_type $title
     * @return unknown
     */
    public function update_course($courseKey, $title) {
        $result = false;
        $sql = 'UPDATE course SET title = ' . $this->escapeString($title) . ' ' .
                'WHERE courseKey = ' . $this->escapeString($courseKey);
        $result = $this->consulta($sql);
        return $result;
    }

    /**
     * 
     * Obte el rol en la bd
     * @param int $course_id
     * @param int $user_id
     * @return boolean
     */
    public function obte_rol($course_id, $user_id) {
        $result = $this->consulta("SELECT * FROM user_course where id_user = " . $user_id . " AND id_course = " . $course_id);
        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 
     * Afegeix l'usuari al curs si no esta
     * @param unknown_type $course_id
     * @param unknown_type $user_id
     * @param unknown_type $isInstructor
     * @param string $lis_result_sourceid
     * @return unknown
     */
    public function join_course($course_id, $user_id, $isInstructor, $lis_result_sourceid) {
        $result = false;
        if (!$this->obte_rol($course_id, $user_id)) {
            $sql = 'INSERT INTO user_course (id_user, id_course, is_instructor, lis_result_sourceid, lastAccessTandem) 
                												VALUES (' . $this->escapeString($user_id) . ',' . $this->escapeString($course_id) . ',' . ($isInstructor ? 1 : 0) . ', ' . $this->escapeString($lis_result_sourceid) . ', ' . $this->escapeString(date('Y-m-d H:i:s')) . ')';
        } else {
            $sql = 'UPDATE user_course SET is_instructor =' . ($isInstructor ? 1 : 0) . ',lis_result_sourceid=' . $this->escapeString($lis_result_sourceid) . ' where id_user = ' . $user_id . ' AND id_course = ' . $course_id;
        }
        $result = $this->consulta($sql);
        return $result;
    }

    /**
     * 
     * Afegeix l'usuari al curs si no esta
     * @param unknown_type $course_id
     * @param unknown_type $username
     * @return unknown
     */
    public function afegeixUsuari($course_id, $username, $name, $surname, $fullname, $email, $image, $lis_result_sourceid = null) {
        $isInstructor = 0;
        $result = false;
        $user = $this->get_user_by_username($username);
        if (!$user) {
            if ($this->register_user($username, $name, $surname, $fullname, $email, $image)) {
                $user = $this->get_user_by_username($username);
            }
        } else {
            if ($this->update_user($username, $name, $surname, $fullname, $email, $image, '', '', '', '', false)) {
                $user = $this->get_user_by_username($username);
            }
        }
        if ($user) {
            $user_id = $user['id'];
            $result = $this->join_course($course_id, $user_id, $isInstructor, $lis_result_sourceid);
        }
        return $result;
    }

    /**
     * 
     * Gets the object of tandem if exists, if not this user is the host
     * @param unknown_type $id_user
     * @param unknown_type $id_resource_lti
     */
    public function is_invited_to_join($id_user, $id_resource_lti, $id_course) {

        return $this->get_invited_to_join($id_user, $id_resource_lti, $id_course, false);
    }

    /**
     *
     * Gets the object of tandem if exists, if not this user is the host
     * @param unknown_type $id_user
     * @param unknown_type $id_resource_lti
     * @param String $id_course
     * @param boolean $return_array
     */
    public function get_invited_to_join($id_user, $id_resource_lti, $id_course, $return_array) {
        $sql = 'SELECT t.*, e.name, e.name_xml_file, e.relative_path, user.firstname, user.surname from tandem as t ' .
                'inner join exercise e on e.id=t.id_exercise and e.enabled=1 ' .
                'LEFT outer join user on user.id=t.id_user_host ' .
                'where id_course = ' . $id_course . ' AND id_resource_lti = ' . $this->escapeString($id_resource_lti) . ' and id_user_guest = ' . $id_user
                . ' and is_guest_user_logged=0 and is_finished=0 order by t.created desc ';
        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            if ($return_array) {
                $row = $this->obteComArray($result);
            } else {
                $row = $this->obteObjecteComArray($result);
            }
            return $row;
        } else {
            return false;
        }
    }

    /**
     *
     * Gets the last id 
     * @param unknown_type $id_user
     * @param unknown_type $id_resource_lti
     * @param String $id_course
     * @param boolean $return_array
     */
    public function get_lastid_invited_to_join($id_user, $id_resource_lti, $id_course) {
        $sql = 'SELECT t.id from tandem as t ' .
                'where t.id_course = ' . $id_course . ' AND t.id_resource_lti = ' . $this->escapeString($id_resource_lti) . ' and t.id_user_guest = ' . $id_user
                . ' and t.is_guest_user_logged=0 and t.is_finished=0 order by t.created desc limit 0,1 ';
        $result = $this->consulta($sql);
        $id = 0;
        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            if (isset($row) && isset($row['id']))
                $id = intval($row['id']);
        }
        return $id;
    }

    /**
     *
     * Gets the new tandems object of tandem if exists, if not this user is the host
     * @param unknown_type $id_last_insert
     * @param unknown_type $id_user
     * @param unknown_type $id_resource_lti
     * @param String $id_course
     * @param boolean $return_array
     */
    public function get_new_invited_to_join($id_last_insert, $id_user, $id_resource_lti, $id_course, $return_array) {
        $sql = 'SELECT t.*, e.name, e.name_xml_file, e.relative_path, user.firstname, user.surname from tandem as t ' .
                'inner join exercise e on e.id=t.id_exercise and e.enabled=1 ' .
                'LEFT outer join user on user.id=t.id_user_host ' .
                'where t.id>' . $id_last_insert . ' AND id_course = ' . $id_course . ' AND id_resource_lti = ' . $this->escapeString($id_resource_lti) . ' and id_user_guest = ' . $id_user
                . ' and is_guest_user_logged=0 and is_finished=0 order by t.created desc limit 0,1 ';
        $result = $this->consulta($sql);
        
        if ($this->numResultats($result) > 0) {
            if ($return_array) {
                $row = $this->obteComArray($result);
            } else {
                $row = $this->obteObjecteComArray($result);
            }
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 
     * Retorna el llista d'estudiants
     * @param int $course_id
     * #param int $current_user_id (check the user not to be this one, if not pass then there show all users of course) 
     * @return Ambigous <boolean, multitype:multitype: >
     */
    public function obte_llistat_usuaris($course_id, $current_user_id = -1) {
        $row = false;
        $result = $this->consulta('SELECT * FROM user_course uc inner join `user` u on u.id = uc.id_user 
                	WHERE uc.id_course = ' . $this->escapeString($course_id) . ' and uc.id_user != ' . $this->escapeString($current_user_id)
                . ' order by u.surname');
        if ($this->numResultats($result) > 0) {
            $row = $this->obteComArray($result);
        }
        return $row;
    }

    /**
     * 
     * Returns the where clause to filter by created and finalized_type
     * @param unknown_type $created_date
     * @param unknown_type $finalized_date
     * @param unknown_type $table
     */
    private function add_start_finish_date_where($created_date = '', $finalized_date = '', $table) {
        $moreWhere = '';
        if (strlen($created_date) > 0) {

            $moreWhere .= ' AND ' . $table . '.created >= ' . $this->escapeString($created_date) . ' ';
        }
        if (strlen($finalized_date) > 0) {
            $moreWhere .= ' AND ' . $table . '.finalized <= ' . $this->escapeString($finalized_date) . ' ';
        }
        return $moreWhere;
    }

    /**
     * 
     * Adds finsished to where clause
     * @param unknown_type $finished
     * @param string $table
     * @return string
     */
    private static function add_finished_where($finished = -1, $table) {
        $moreWhere = '';
        switch ($finished) {
            case 2:
                if ($table == 'tandem')
                    $moreWhere .= ' AND ' . $table . '.date_guest_user_logged is null AND ' . $table . '.is_finished = 0 ';
                else
                    $moreWhere .= ' AND ' . $table . '.is_finished = 0 ';
                break;
            case 1:
                $moreWhere .= ' AND ' . $table . '.is_finished = 1 ';
                break;
            case 0:
                if ($table == 'tandem')
                    $moreWhere .= ' AND ' . $table . '.date_guest_user_logged is not null AND ' . $table . '.is_finished = 0 ';
                else
                    $moreWhere .= ' AND ' . $table . '.is_finished = 0 ';
                break;
        }
        return $moreWhere;
    }

    /**
     * 
     * 
     * @param unknown_type $course_id
     * @param unknown_type $user_id
     * @param unknown_type $exercise
     * @param int $id_tandem
     * @param int $order 
     * @param int $order_dir 
     * @return Ambigous <boolean, multitype:multitype: >
     */
    public function obte_llistat_tandems($course_id, $user_id, $exercise, $id_tandem = -1, $order = 0, $order_dir = 0, $created_date = '', $finalized_date = '', $finished = -1) {
        $rows = null;
        $moreWhere = '';
        if ($exercise > 0) {
            $moreWhere .= ' AND tandem.id_exercise = ' . $exercise;
        }
        if ($user_id > 0) {
            $moreWhere .= 'AND (tandem.id_user_host = ' . $user_id . ' OR tandem.id_user_guest  = ' . $user_id . ')';
        }
        if ($id_tandem > 0) {
            $moreWhere .= 'AND tandem.id = ' . $id_tandem . ' ';
        }
        $moreWhere .= $this->add_start_finish_date_where($created_date, $finalized_date, 'tandem');
        $moreWhere .= self::add_finished_where($finished, 'tandem');

        $order_by = '';
        switch ($order) {
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
        if ($order_dir == 1) {
            $order_by.= ' DESC';
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
                ' LEFT outer join user_tandem on user_tandem.id_tandem=tandem.id ' . (($user_id > 0) ? ' AND user_tandem.id_user = ' . $user_id . ' ' : ' AND user_tandem.id_user = tandem.id_user_host') .
                ' left join user on user_tandem.id_user = user.id ' .
                ' left join user as user_host on tandem.id_user_host = user_host.id ' .
                ' left join user as user_guest on tandem.id_user_guest = user_guest.id ' .
                ' WHERE tandem.id_course = ' . $course_id . ' ' .
                $moreWhere .
                ' ORDER BY ' . $order_by;

        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $rows = $this->obteComArray($result);
            /* foreach ($rows as $key => $row) {
              $rows[$key]['other_user'] = $this->getUserRelatedTandem($row['id_user_host']!=$user_id?$row['id_user_host']:$row['id_user_guest']);
              } */
        }
        return $rows;
    }

    /**
     *
     *
     * @param unknown_type $course_id
     * @param unknown_type $user_id
     * @param unknown_type $exercise
     * @param int $id_tandem
     * @param int $id_task
     * @param int $order 
     * @param int $order_dir 
     * @return Ambigous <boolean, multitype:multitype: >
     */
    public function obte_task_tandems($course_id, $user_id, $exercise, $id_tandem, $id_task, $order = 0, $order_dir = 0, $created_date = '', $finalized_date = '', $finished = -1) {
        $rows = null;
        $moreWhere = '';
        if ($exercise > 0) {
            $moreWhere .= ' AND tandem.id_exercise = ' . $exercise;
        }
        if ($user_id > 0) {
            $moreWhere .= ' AND user_tandem_task.id_user = ' . $user_id;
        }
        if ($id_task > 0) {
            $moreWhere .= ' AND user_tandem_task.task_number = ' . $id_task;
        }
        $moreWhere .= $this->add_start_finish_date_where($created_date, $finalized_date, 'user_tandem_task');
        $moreWhere .= self::add_finished_where($finished, 'user_tandem_task');

        $order_by = '';
        switch ($order) {
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
        if ($order_dir == 1) {
            $order_by.= ' ASC';
        }

        $sql = 'SELECT user_tandem_task.*, tandem.id_exercise, tandem.is_finished as is_finished_tandem, tandem.finalized as finalized_tandem, exercise.name as exercise, ' .
                ' user.fullname as user ' .
                ' FROM user_tandem_task ' .
                ' inner join tandem on user_tandem_task.id_tandem = tandem.id ' .
                ' inner join exercise on tandem.id_exercise = exercise.id ' .
                ' left join user  on user_tandem_task.id_user = user.id ' .
                ' WHERE tandem.id_course = ' . $course_id . ' AND user_tandem_task.id_tandem = ' . $id_tandem . ' '
                . $moreWhere . ' ORDER BY ' . $order_by;

        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $rows = $this->obteComArray($result);
        }
        return $rows;
    }

    /**
     * 
     * 
     * @param unknown_type $course_id
     * @param unknown_type $user_id
     * @param unknown_type $exercise
     * @param unknown_type $id_tandem
     * @param unknown_type $id_task
     * @param int $id_question
     * @param int $order 
     * @param int $order_dir 
     * @return Ambigous <boolean, multitype:multitype: >
     */
    public function obte_questions_task_tandems($course_id, $user_id, $exercise, $id_tandem, $id_task, $id_question, $order = 0, $order_dir = 0, $created_date = '', $finalized_date = '', $finished = -1) {
        $rows = null;
        $moreWhere = '';
        if ($exercise > 0) {
            $moreWhere .= ' AND tandem.id_exercise = ' . $exercise;
        }
        if ($user_id > 0) {
            $moreWhere .= ' AND user_tandem_task_question.id_user = ' . $user_id;
        }
        if ($id_question > 0) {
            $moreWhere .= ' AND user_tandem_task_question.question_number = ' . $id_question;
        }
        $moreWhere .= $this->add_start_finish_date_where($created_date, $finalized_date, 'user_tandem_task_question');
        $moreWhere .= self::add_finished_where($finished, 'user_tandem_task_question');

        $order_by = '';
        switch ($order) {
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
        if ($order_dir == 1) {
            $order_by.= ' ASC';
        }

        $sql = 'SELECT user_tandem_task_question.*, tandem.id_exercise, tandem.is_finished as is_finished_tandem, tandem.finalized as finalized_tandem, exercise.name as exercise, ' .
                ' user.fullname as user ' .
                ' FROM user_tandem_task_question ' .
                ' inner join tandem on user_tandem_task_question.id_tandem = tandem.id ' .
                ' inner join exercise on tandem.id_exercise = exercise.id ' .
                ' left join user on user_tandem_task_question.id_user = user.id ' .
                ' WHERE tandem.id_course = ' . $course_id . ' AND user_tandem_task_question.id_tandem = ' . $id_tandem .
                ' AND user_tandem_task_question.task_number = ' . $id_task
                . $moreWhere . ' ORDER BY ' . $order_by;
        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $rows = $this->obteComArray($result);
        }
        return $rows;
    }

    /**
     * 
     * Enter description here ...
     * @param unknown_type $user_id
     * @return Ambigous <>|string
     */
    private function getUserRelatedTandem($user_id) {
        $sql = 'SELECT fullname from user where id = ' . $user_id;
        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            return $row['fullname'];
        } else {
            return '';
        }
    }

    /**
     *
     * Obte tots els exercicis relacionats amb el curs
     * @param unknown_type $userKey
     * @return array|boolean
     */
    public function get_tandem_exercises($course_id, $enabled = 1) {
        $row = false;
        $where = 'where (e.name_xml_file is not null and e.name_xml_file!=\'\' )';
        if ($enabled == 1) {
            $where .= 'and e.enabled=1 ';
        }
        $result = $this->consulta('SELECT e.*, ce.id_course FROM exercise e  ' .
                'inner join course_exercise ce on ce.id_exercise=e.id AND (ce.id_course = ' . $course_id . ' OR ce.id_course=-1) ' .
                $where
        );
        if ($this->numResultats($result) > 0) {
            $row = $this->obteComArray($result);
        }
        return $row;
    }

    /**
     * 
     * Gets data of exercise
     * @param unknown_type $exercise_id
     * @return boolean
     */
    public function get_exercise($exercise_id) {
        $row = false;
        $where = 'where e.id = ' . $exercise_id;
        $result = $this->consulta('SELECT e.* FROM exercise e  ' .
                $where
        );
        if ($this->numResultats($result) > 0) {
            $row = $this->obteComArray($result);
        }
        return $row;
    }

    /** 	
     *
     * Deletes the exercise and course relactions 
     * @param unknown_type $course_id
     * @param unknown_type $exercise_id
     * @return resource
     */
    public function delete_exercise($course_id, $exercise_id) {
        $row = $this->get_exercise($exercise_id);
        if ($row) {
            $result = $this->consulta('DELETE FROM exercise  where id = ' . $this->escapeString($exercise_id));
            if ($result)
                $result = $this->consulta('DELETE FROM course_exercise where id_exercise = ' . $this->escapeString($exercise_id));
            else {
                $row = false;
            }
        }
        return $row;
    }

    /**
     * 
     * Enables or disabled an exercise
     * @param unknown_type $course_id
     * @param unknown_type $exercise_id
     * @return resource
     */
    public function enable_exercise($exercise_id) {
        $result = $this->consulta('update exercise SET enabled = IF( enabled=\'0\',\'1\',\'0\')  where id = ' . $this->escapeString($exercise_id));
        return $result;
    }

    /**
     *
     * Obte tots els exercicis relacionats amb el curs que no estan deshabilitats
     * @param unknown_type $userKey
     * @return array|boolean
     * @deprecated
     */
    public function get_tandem_exercises_v1($course_id) {
        $row = false;
        $result = $this->consulta('SELECT e.* FROM exercise e  ' .
                'where e.enabled=1 and e.id not in ' .
                '(select ce.id_exercise from course_exercise_disabled ce where ce.id_course = ' . $course_id . ')');
        if ($this->numResultats($result) > 0) {
            $row = $this->obteComArray($result);
        }
        return $row;
    }

    /**
     * 
     * Register a new exercise to a course
     * @param unknown_type $id_course
     * @param unknown_type $id_exercise
     * @param unknown_type $id_user
     * @param unknown_type $name
     * @param unknown_type $name_xml_file
     * @param unknown_type $enabled
     */
    public function register_tandem_exercise($id_course, $id_exercise, $id_user, $name, $name_xml_file, $enabled, $week=0) {
        $sql = '';
        if ($id_exercise <= 0) {
            $sql = 'INSERT INTO exercise (name, name_xml_file, enabled, created, created_user_id, modified, modified_user_id, week)  ' .
                    'VALUES ' .
                    '(' . $this->escapeString($name) . ', ' . $this->escapeString($name_xml_file) . ', ' . ($enabled ? 1 : 0) . ', NOW(), ' . $id_user . ', NOW(), ' . $id_user . ', ' . $this->escapeString($week) . ')';
        } else {
            $sql = 'UPDATE exercise SET name=' . $this->escapeString($name) . ', name_xml_file=' . $this->escapeString($name_xml_file) . ', enabled=' . ($enabled ? 1 : 0) . ', modified = NOW(), modified_user_id = ' . $id_user . ' , week = ' . $this->escapeString($name) . ' ' .
                    ' WHERE id = ' . $id_exercise;
        }
        $result = $this->consulta($sql);
        if ($result) {
            if ($id_exercise <= 0) {
                $id_exercise = $this->get_last_inserted_id();
                $relative_path = '/' . $id_exercise;
                $sql = 'UPDATE exercise SET relative_path=' . $this->escapeString($relative_path) .
                        ' WHERE id = ' . $id_exercise;
                $result = $this->consulta($sql);

                //Relacionem amb el curs
                $sql = 'INSERT INTO course_exercise (id_course, id_exercise, created, created_user_id) ' .
                        'VALUES ' .
                        '(' . $id_course . ', ' . $id_exercise . ', NOW(), ' . $id_user . ')';
                $result = $this->consulta($sql);
            }
        }
        return $id_exercise;
    }

    /**
     * 
     * Registra un nou tandem a l'espera que l'altre usuari es connecti
     * @param unknown_type $id_exercise
     * @param unknown_type $id_course
     * @param unknown_type $id_resource_lti
     * @param unknown_type $id_user_host
     * @param unknown_type $id_user_guest
     * @param unknown_type $message
     * @return resource
     */
    
    
    public function register_tandem($id_exercise, $id_course, $id_resource_lti, $id_user_host, $id_user_guest, $message, $user_agent) {
        $result = false;
        $sql = 'INSERT INTO tandem (id_exercise, id_course, id_resource_lti, id_user_host, id_user_guest, message, is_guest_user_logged, is_finished, created, user_agent_host)
        	        VALUES (' . $id_exercise . ' ,' . $id_course . ' ,' . $this->escapeString($id_resource_lti) . ',' . $id_user_host . ',' . $id_user_guest . ' ,' . $this->escapeString($message) . ', 0, 0, now(), ' . $this->escapeString($user_agent) . ')';
        $result = $this->consulta($sql);
        if ($result) {
            $result = $this->get_last_inserted_id();
        }
        return $result;
    }

    /**
     *
     * Registra un nou tandem a l'espera que l'altre usuari es connecti
     * @param unknown_type $id_exercise
     * @param unknown_type $id_course
     * @param unknown_type $id_resource_lti
     * @param unknown_type $id_current_user
     * @param unknown_type $id_other_user
     * @return resource
     */
    public function has_invited_to_tandem($id_exercise, $id_course, $id_resource_lti, $id_current_user, $id_other_user) {
        $ret = -1;
        //Mirem si ens ha invitat
        $sql = 'SELECT id FROM tandem 
                WHERE id_exercise = ' . $id_exercise . ' AND id_course = ' . $id_course . ' AND id_resource_lti = ' . $this->escapeString($id_resource_lti) . '
                AND id_user_host = ' . $id_other_user . ' AND id_user_guest = ' . $id_current_user . ' AND is_guest_user_logged!=1 AND TIMESTAMPDIFF(HOUR,created,now()) <= 24 order by created desc ';
        $result = $this->consulta($sql);
        if ($result && $this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            if ($row) {
                $ret = $row['id'];
            }
        }
        return $ret;
    }

    /**
     *
     * Actualitza la connexio de l'altre usuari
     * @param int $id
     * @return resource
     */
    public function update_user_guest_tandem($id, $user_agent) {
        $result = false;
        $sql = 'UPDATE tandem SET is_guest_user_logged = 1, date_guest_user_logged = now(), user_agent_guest = ' . $this->escapeString($user_agent) . ' ' .
                'WHERE id = ' . $id;
        $result = $this->consulta($sql);
        return $result;
    }
    
    /**
     * 
     * @param type $id
     * @param type $user_id
     * @return type
     */
    public function assign_partner_user_tandem($id, $user_id) {
        $result = false;
        $sql = 'UPDATE tandem SET id_user_guest = '.$user_id.
                ' WHERE id = ' . $id;
        $result = $this->consulta($sql);
        return $result;
    }

    /**
     *
     * Actualitza la connexio de l'altre usuari
     * @param int $id
     * @return resource
     */
    public function update_user_guest_tandem_others($tandem) {
        $result = false;
        $sql = 'UPDATE tandem SET is_guest_user_logged = 1 ' .
                'WHERE id_course = ' . $tandem['id_course'] .
                ' AND id_user_host = ' . $tandem['id_user_host'] .
                ' AND id_user_guest = ' . $tandem['id_user_guest'] .
                ' AND is_guest_user_logged=0 AND is_finished=0';
        $result = $this->consulta($sql);
        return $result;
    }

    /**
     * 
     * Gets the last inserted id
     * @return resource
     */
    public function get_last_inserted_id() {
        $sql = 'select last_insert_id() as id ';
        $result = $this->consulta($sql);
        if ($result) {
            $row = $this->obteObjecteComArray($result);
            if ($row) {
                $result = $row['id'];
            }
        }
        return $result;
    }

    /**
     * 
     * Retorna el id de l'exercici segons el nom que li passen de l'xml
     * @param unknown_type $name_xml_file
     * @return resource
     */
    public function getExerciseByXmlName($name_xml_file, $id_course = -1) {
        $sql = 'select * from exercise e inner join course_exercise c on c.id_exercise=e.id where e.name_xml_file =' . $this->escapeString($name_xml_file) . ' and c.id_course =' . $this->escapeString($id_course);
        $result = $this->consulta($sql);
        if ($result) {
            $row = $this->obteObjecteComArray($result);
            if ($row) {
                $result = $row['id'];
            }
        }
        return $result;
    }

    /**
     * 
     * Retorna el tandem per id
     * @param int $id
     * @return resource
     */
    public function obteTandem($id) {
        $sql = 'select tandem.*, exercise.name_xml_file, exercise.relative_path, exercise.name as name_exercise from tandem ' .
                'inner join exercise on exercise.id = tandem.id_exercise ' .
                'where tandem.id =' . $this->escapeString($id);
        $result = $this->consulta($sql);
        if ($result) {
            $result = $this->obteObjecteComArray($result);
        }
        return $result;
    }

    /**
     * Obté el usuari guest pel waiting for user
     * 
     */
    public function getUserB($id) {
        $sql = 'select * from user ' .
                'where id =' . $id;
        $result = $this->consulta($sql);
        return mysql_fetch_object($result);
    }

    /**
     * 
     * Actualitza l'xml del tandem
     * @param int $id_tandem
     * @param string $xml
     */
    public function registra_xml_tandem($id_tandem, $xml) {
        $result = false;
        $sql = 'UPDATE tandem SET xml = ' . $this->escapeString($xml) .
                ' WHERE id = ' . $id_tandem .
                ' AND is_finished=0';
        $result = $this->consulta($sql);
        return $result;
    }

    /**
     *
     * Finalitza el tandem
     * @param int $id_tandem
     * @param string $xml
     */
    public function finalitza_tandem($id_tandem) {
        $result = false;
        $sql = 'UPDATE tandem SET is_finished = 1, finalized = now() ' .
                ' WHERE id = ' . $id_tandem .
                ' AND is_finished=0';
        $result = $this->consulta($sql);
        return $result;
    }

    /**
     *
     * Obte el temps total al tandem
     * @param int $id_tandem
     */
    public function get_temps_total_tandem($id_tandem) {

        $total_time = 0;

        $sql = 'SELECT TIMESTAMPDIFF(SECOND,created,now()) as total_time FROM tandem where id = ' . $id_tandem;
        $result = $this->consulta($sql);

        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            $total_time = $row['total_time'];
        }

        return $total_time;
    }

    /**
     * 
     * Registra l'accio de l'usuari al tandem
     * @param unknown_type $id_tandem
     * @param unknown_type $id_user
     * @param boolean $is_finished
     * @param float $points
     */
    public function registra_user_tandem($id_tandem, $id_user, $is_finished = false, $points = 0) {


        $sql = 'SELECT * from user_tandem where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user;
        $result = $this->consulta($sql);
        if ($result) {

            $total_time = $this->get_temps_total_tandem($id_tandem);

            if ($this->numResultats($result) == 0) {
                $sql = 'INSERT INTO user_tandem (id_tandem, id_user, total_time, points, is_finished, finalized, created) ' .
                        ' VALUES ' .
                        '(' . $id_tandem . ', ' . $id_user . ', ' . $total_time . ', ' . $points . ', ' . ($is_finished ? 1 : 0) . ', ' . ($is_finished ? 'now()' : 'null') . ', now())';
            } else {
                $sql = 'UPDATE user_tandem SET total_time = ' . $total_time . ', points=' . $points . ', is_finished = ' . ($is_finished ? 1 : 0) . ', finalized = ' . ($is_finished ? 'now()' : 'null') . ' ' .
                        ' where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user;
            }

            $result = $this->consulta($sql);

            if ($result && $is_finished) {
                $this->finalitza_tandem($id_tandem);
            }
        }
        return $result;
    }

    /**
     * 
     * Obte el total d'un exercici
     * @param unknown_type $id_tandem
     * @param unknown_type $id_user
     * @param unknown_type $task_number
     * @return number
     */
    public function get_temps_total_task_tandem($id_tandem, $id_user, $task_number) {

        $total_time = 0;

        $sql = 'SELECT TIMESTAMPDIFF(SECOND,created,now()) as total_time FROM user_tandem_task where  id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number;
        $result = $this->consulta($sql);

        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            $total_time = $row['total_time'];
        }

        return $total_time;
    }

    /**
     * 
     * Registra el temps en una pregunta per un determinat usuari
     * @param unknown_type $id_tandem
     * @param unknown_type $id_user
     * @param unknown_type $task_number
     * @param unknown_type $is_finished_tasca
     * @param float $points
     * @return resource
     */
    public function register_task_user_tandem($id_tandem, $id_user, $task_number, $is_finished = false, $points = 0) {


        $sql = 'SELECT * from user_tandem_task where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number;
        $result = $this->consulta($sql);
        if ($result) {

            $total_time = $this->get_temps_total_task_tandem($id_tandem, $id_user, $task_number);

            if ($this->numResultats($result) == 0) {
                $sql = 'INSERT INTO user_tandem_task (id_tandem, id_user, task_number, total_time, points, is_finished, finalized, created) ' .
                        ' VALUES ' .
                        '(' . $id_tandem . ', ' . $id_user . ', ' . $task_number . ', ' . $total_time . ', ' . $points . ', ' . ($is_finished ? 1 : 0) . ', ' . ($is_finished ? 'now()' : 'null') . ', now())';
            } else {
                $sql = 'UPDATE user_tandem_task SET total_time = ' . $total_time . ', points=' . $points . ', is_finished = ' . ($is_finished ? 1 : 0) . ', finalized = ' . ($is_finished ? 'now()' : 'null') . ' ' .
                        ' where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number;
            }

            $result = $this->consulta($sql);
        }
        return $result;
    }

    /**
     * 
     * Obte el temps total per una pregunta d'una tasca
     * @param unknown_type $id_tandem
     * @param unknown_type $id_user
     * @param unknown_type $task_number
     * @param unknown_type $question_number
     */
    public function get_temps_total_task_question_tandem($id_tandem, $id_user, $task_number, $question_number) {

        $total_time = 0;

        $sql = 'SELECT TIMESTAMPDIFF(SECOND,created,now()) as total_time FROM user_tandem_task_question where  id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number . ' AND question_number = ' . $question_number;
        $result = $this->consulta($sql);

        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            $total_time = $row['total_time'];
        }

        return $total_time;
    }

    /**
     *
     * Registra el temps en una pregunta per un determinat usuari
     * @param unknown_type $id_tandem
     * @param unknown_type $id_user
     * @param unknown_type $task_number
     * @param int $question_number
     * @param unknown_type $is_finished_tasca
     * @param float $points
     * @return resource
     */
    public function register_task_question_user_tandem($id_tandem, $id_user, $task_number, $question_number, $is_finished = false, $points = 0) {


        $sql = 'SELECT * from user_tandem_task_question where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number . ' AND question_number = ' . $question_number;
        $result = $this->consulta($sql);
        if ($result) {

            $total_time = $this->get_temps_total_task_question_tandem($id_tandem, $id_user, $task_number, $question_number);

            if ($this->numResultats($result) == 0) {
                $sql = 'INSERT INTO user_tandem_task_question (id_tandem, id_user, task_number, question_number,total_time, points, is_finished, finalized, created) ' .
                        ' VALUES ' .
                        '(' . $id_tandem . ', ' . $id_user . ', ' . $task_number . ', ' . $question_number . ', ' . $total_time . ', ' . $points . ', ' . ($is_finished ? 1 : 0) . ', ' . ($is_finished ? 'now()' : 'null') . ', now())';
            } else {
                $sql = 'UPDATE user_tandem_task_question SET total_time = ' . $total_time . ', points=' . $points . ', is_finished = ' . ($is_finished ? 1 : 0) . ', finalized = ' . ($is_finished ? 'now()' : 'null') . ' ' .
                        ' where id_tandem = ' . $id_tandem . ' AND id_user = ' . $id_user . ' AND task_number = ' . $task_number . ' AND question_number = ' . $question_number;
            }

            $result = $this->consulta($sql);
        }
        return $result;
    }

    /*     * ***************************************************************************************************** */
    /*     * ************************************ L T I  C O N S U M E R ***************************************** */
    /*     * ***************************************************************************************************** */

    /**
     * 
     * Get the data of LTI app
     * @param unknown_type $id
     */
    public function loadDadesLTI($id) {

        $user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;

        $course_id = $_SESSION[COURSE_ID];
        $role_data = $this->get_role_classroom($course_id, $user_obj->id);
        $role = $role_data && isset($role_data['role']) ? $role_data['role'] : '';

        $sql = 'SELECT a.id, a.toolurl as toolurl, a.name, a.description, a.resourcekey, a.resourcekey, a.password, a.preferheight, a.sendname, ' .
                ' a.sendemailaddr, a.acceptgrades, a.allowroster, a.allowsetting, a.customparameters as customparameters,  ' .
                'a.allowinstructorcustom, a.organizationid, a.organizationurl, a.launchinpopup, a.debugmode, a.registered, a.updated ' .
                'from lti_application a ' .
                'where a.id = ' . $id;
        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            return $row;
        } else {
            return null;
        }
    }

    /**
     * 
     * Obte el rol en la bd
     * @param int $course_id
     * @param int $user_id
     * @return boolean
     */
    public function get_role_classroom($course_id, $user_id) {
        $result = $this->consulta("SELECT * FROM user_course where id_user = " . $user_id . " AND id_course = " . $course_id);
        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 
     * Loads data of remote App
     * @param unknown_type $id
     */
    public function loadRemoteApp($id) {

        $sql = 'SELECT * from remote_application where id = ' . $id;
        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $row = $this->obteObjecteComArray($result);
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
     * @param type $id_tandem
     * @param type $user_agent
     * @return boolean
     */
    
     public function updateUserGuestTandem($id_tandem,$id_user_guest){
        $result = false;
        $sql = 'UPDATE tandem SET id_user_guest = '.$id_user_guest.'  WHERE id = '.$id_tandem;
        $result = $this->consulta($sql);
        return $result;
    }
    
    /**
     * Sel.leccionem el usuari que fa mes temps que espera
     * @param type $id_course
     * @param type $id_exercise
     * @return array
     */
    public function getFirstUserWaiting($id_course,$id_exercise,$otherLanguage){
   
     $sql =' select wru.id_user,wr.id as id_waiting_room, tandem.id from waiting_room_user as wru   
          inner join waiting_room as wr on wru.id_waiting_room = wr.id
          inner join tandem as tandem on tandem.id_exercise = wr.id_exercise
          and tandem.id_course = wr.id_course and tandem.id_user_host = wru.id_user 
          and coalesce(tandem.finalized,0) = 0 and coalesce(tandem.is_finished,0) = 0
          and tandem.id_user_guest = -1
          where wr.id_course = '.$id_course.' and wr.id_exercise = '.$id_exercise.' and wr.language = '.$this->escapeString($otherLanguage).' 
          order by wru.created asc 
          limit 0, 1 ';
     
      $result = $this->consulta($sql);

       if ($this->numResultats($result) > 0) {
            $row = $this->obteComArray($result);
            
            return $row;
        } else {
            return false;
        }
     
    }
    
    
    /**
     * Insert User in waiting room
     * @param type $id_waiting_room
     * @param type $language
     * @param type $idCourse
     * @param type $idExercise
     * @param type $idNumberUserWaiting
     * @return type
     */
    
   
    public function insertUserIntoWaitingRoom($id_waiting_room, $language, $idCourse, $idExercise, $idUser) {
        $result = false;
        
        //TODO check if is 
        $sql = 'SELECT * FROM `waiting_room_user` where id_waiting_room ='.$id_waiting_room.' and id_user = '.$idUser;
        $resultSQL = $this->consulta($sql);
        if ($this->numResultats($resultSQL)<=0){ 
            //Insert into waiting_room_user
            $sql = 'INSERT INTO waiting_room_user (id_waiting_room, id_user,created) VALUES (' . $id_waiting_room . ',' . $idUser . ',now())';
            $resultSQL = $this->consulta($sql);
            if ($resultSQL) {
                $result = $this->addOrRemoveUserToWaitingRoom($id_waiting_room, +1);
            }
        } else {
            $result = false;
        }
        return $result;
    }

    
    

    /**
     * Add or remove User to waiting room
     * @param type $id_waiting_room
     * @param type $number_user_to_add_or_remove
     * @return boolean
     */
    private function addOrRemoveUserToWaitingRoom($id_waiting_room, $number_user_to_add_or_remove) {
        $ok = false;
        //1. Check in waiting room
        $sql = 'Select * FROM `waiting_room` WHERE `id` = ' . $this->escapeString($id_waiting_room);
        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $object = $this->obteObjecteComArray($result);
            //$id_user_wating_room = $object['id'];
            $language = $object['language'];
            $id_course = $object['id_course'];
            $id_exercise = $object['id_exercise'];
            $number_user_waiting_old = $object['number_user_waiting'];
            $created = $object['created'];
            $number_user_waiting = $object['number_user_waiting'] + $number_user_to_add_or_remove;
            if ($number_user_waiting <= 0) {
                //2.Insert in history table
                $sqlInsert = 'INSERT INTO `waiting_room_history` (`id`, `id_waiting_room`, `language`, `id_course`, `id_exercise`, `number_user_waiting`, `created`, `created_history`) '
                        . 'VALUES (NULL, ' . $this->escapeString($id_waiting_room) . ', ' . $this->escapeString($language) . ', ' . $this->escapeString($id_course) . ', '
                        . '' . $this->escapeString($id_exercise) . ', ' . $this->escapeString($number_user_waiting_old) . ', ' . $this->escapeString($created) . ', NOW())';
                if ($this->consulta($sqlInsert)) {
                            //3. Delete from waiting_room_user
                    
                            $sqlDelete = 'DELETE FROM `waiting_room` WHERE `id` = ' . $this->escapeString($id_waiting_room);
                            if ($this->consulta($sqlDelete)) {
                                $ok = true;
                            }
                }
            } else {
                
                $ok = true;
            }
           
          
        }
        return $ok;
    }

    
    
     private function addToWaitingRoomHistory($id_waiting_room){
        $ok = false;
        //1. Check in waiting room
        $sql = 'Select * FROM `waiting_room` WHERE `id` = ' . $this->escapeString($id_waiting_room);
        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $object = $this->obteObjecteComArray($result);
            //$id_user_wating_room = $object['id'];
            $language = $object['language'];
            $id_course = $object['id_course'];
            $id_exercise = $object['id_exercise'];
            $number_user_waiting_old = $object['number_user_waiting'];
            $created = $object['created'];
            $number_user_waiting = $object['number_user_waiting'];
            if ($number_user_waiting <= 0) {
                //2.Insert in history table
                $sqlInsert = 'INSERT INTO `waiting_room_history` (`id`, `id_waiting_room`, `language`, `id_course`, `id_exercise`, `number_user_waiting`, `created`, `created_history`) '
                        . 'VALUES (NULL, ' . $this->escapeString($id_waiting_room) . ', ' . $this->escapeString($language) . ', ' . $this->escapeString($id_course) . ', '
                        . '' . $this->escapeString($id_exercise) . ', ' . $this->escapeString($number_user_waiting_old) . ', ' . $this->escapeString($created) . ', NOW())';
                    if ($this->consulta($sqlInsert)) {
                            //3. Delete from waiting_room_user
                    
                            $sqlDelete = 'DELETE FROM `waiting_room` WHERE `id` = ' . $this->escapeString($id_waiting_room);
                            if ($this->consulta($sqlDelete)) {
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
     * @param type $id_waiting_room
     * @param type $id_user
     * @param type $status
     * @return type
     */
    private function moveUserToHistory($id_waiting_room, $id_user, $status,$tandemID) {
        $ok = false;
        //1. Check in waiting room user
        $sql = 'Select * FROM `waiting_room_user` WHERE `id_waiting_room` = ' . $this->escapeString($id_waiting_room) . ' AND `id_user` = ' . $this->escapeString($id_user);
        $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0){
            $object = $this->obteObjecteComArray($result);
            $id_user_wating_room = $object['id'];
            $created = $object['created'];
            //2.Insert in history table
            $sqlInsert = 'INSERT INTO `waiting_room_user_history` (`id`, `id_waiting_room`, `id_user`, `status`, `id_tandem` , `created`, `created_history`) VALUES (NULL, ' . $this->escapeString($id_waiting_room) . ', ' . $this->escapeString($id_user) . ', '. $this->escapeString($status) .', ' . $this->escapeString($tandemID) . ', ' . $this->escapeString($created) . ', NOW())';
            if ($this->consulta($sqlInsert)){
                //3. Delete from waiting_room_user
                $sqlDelete = 'DELETE FROM `waiting_room_user` WHERE `id` = ' . $this->escapeString($id_user_wating_room);
                /*if ($this->consulta($sqlDelete)){
                    $ok = $this->addOrRemoveUserToWaitingRoom($id_waiting_room, -1);
                }*/
            }
        }
        return $ok;
    }
    
    
    
    function userIsNoWaitingMore($language,$courseID,$userID,$typeClose,$tandemID)
    {
        $ok = true;
        $sqlSelect = 'select wr.id_exercise , wr.number_user_waiting from waiting_room as wr'
                . ' inner join waiting_room_user as wru on wru.id_waiting_room = wr.id'
                . ' where wr.language = '.$this->escapeString($language).' and wr.id_course = '.$courseID.' and wru.id_user = '.$userID.' and wr.number_user_waiting > 0 limit 0,1 ';
        
        $resultSelect = $this->consulta($sqlSelect); 
        
        if ($this->numResultats($resultSelect) > 0){
            
            $resultSelect = $this->obteComArray($resultSelect);
            
            $id_exercise = $resultSelect[0]['id_exercise'];
         
            $number_user_waiting = $resultSelect[0]['number_user_waiting'];
           
            //si numero de usuaris en espera es mes gran o igual k 1
            
                if($number_user_waiting >0){

                    $sqlUpdateWR = 'update `waiting_room` set number_user_waiting = number_user_waiting - 1 where id_course = '.$courseID.' and id_exercise =  '.$id_exercise;
                    $resultUpdateWR = $this->consulta($sqlUpdateWR);
                    $sqlDeleteTA = 'delete from tandem where id_exercise = '.$id_exercise.' and id_course = '.$courseID.' and id_user_host= '.$userID.' and id_user_guest = -1';
                    $resultDeleteTA = $this->consulta($sqlDeleteTA); 
                    
                    $sqlSelectWR = 'select id,number_user_waiting from waiting_room where id_course = '.$courseID.' and id_exercise =  '.$id_exercise;
                    $resultSelectWR = $this->consulta($sqlSelectWR); 
                    
                    if ($this->numResultats($resultSelectWR) > 0){
            
                        $resultSelect = $this->obteComArray($resultSelectWR);

                        $id = $resultSelect[0]['id']; //id waiting room
                        
                        $number_user_waiting = $resultSelect[0]['number_user_waiting'];
                        
                        
                        if ($typeClose == 'assigned'){
                            $this->moveUserToHistory($id,$userID, $typeClose,$tandemID); 
                        }else{
                             $this->moveUserToHistory($id,$userID, $typeClose,$tandemID = false);
                        }
                        
                        $sqlDeleteWR = 'delete from waiting_room_user where id_waiting_room = '.$id.' and id_user = '.$userID;
                        $resultDeleteWR = $this->consulta($sqlDeleteWR); 
                        
                        if($number_user_waiting==0){
                            
                            $this->addToWaitingRoomHistory($id);
                            
                            $sqlDeleteWR = 'delete from waiting_room where id_exercise = '.$id_exercise.' and id_course = '.$courseID.' and number_user_waiting = 0';
                            $resultDeleteWR = $this->consulta($sqlDeleteWR); 
                            
                        }
                        
                    }
                    
                }else{
                    $this->addToWaitingRoomHistory($id);
                    
                    $sqlDeleteWR = 'delete from waiting_room where id_exercise = '.$id_exercise.' and id_course = '.$courseID.' and number_user_waiting = 0';
                    $resultDeleteWR = $this->consulta($sqlDeleteWR); 
                    
                    
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
     * @param string $language
     * @param integer $courseID
     * @return array
     */
    public function check_offered_exercises($language, $courseID) {
        $row = array();
        $result = $this->consulta("Select id_exercise,id FROM waiting_room WHERE language != " . $this->escapeString($language) . " AND id_course = " . $this->escapeString($courseID));
        while ($fila = mysql_fetch_assoc($result)) {
            $row[] = $fila['id_exercise'] . '-' . $fila['id'];
        }
        return $row;
    }

    /**
     * We INSERT a user with a new exercise if don't exists or UPDATE the
     * existing exercise increasing +1 in the comput global in the waiting_room table
     * @param type $language
     * @param type $courseID
     * @param type $exerciseID
     */
    public function offer_exercise($language, $courseID, $exerciseID,$idUser)
    {
        $ok = false;

        //return 'Dins offer exercise: '.$language.'-'.$courseID.'-'.$exerciseID;
        //TODO delete it
        $sqlDelete = 'delete from waiting_room where number_user_waiting = 0 and id_course = '.$courseID.' and id_exercise = ' . $exerciseID;
        $resultDelete = $this->consulta($sqlDelete);
        
        
        
        $sqlSelect = 'select number_user_waiting, id from waiting_room where id_course = '.$courseID.' and id_exercise = ' . $exerciseID;
        $resultSelect = $this->consulta($sqlSelect);
        $waiting_room_id = -1;
        
        if ($this->numResultats($resultSelect) > 0){
            
            $resultSelect = $this->obteComArray($resultSelect);
            
            $waiting_room_id = $resultSelect[0]['id'];
            
            $sql= "UPDATE waiting_room SET number_user_waiting = number_user_waiting + 1  WHERE id = ".$waiting_room_id." and id_course = ".$courseID." and id_exercise = ".$exerciseID;
            $ok = $this->consulta($sql);
            
        }else{
            $sqlInsert = 'INSERT INTO waiting_room (language, id_course, id_exercise,number_user_waiting,created) VALUES (' . $this->escapeString($language) . ',' . $courseID . ',' . $exerciseID . ',1,now())';
            $ok = $this->consulta($sqlInsert);
            if ($ok) {
                
                $waiting_room_id = mysql_insert_id();
            }
            
        }
        if ($ok) {
            $this->insertUserIntoWaitingRoom($waiting_room_id, $language, $courseID, $exerciseID, $idUser);
        }
        return $ok;
    }
    
   
    public function tandem_exercise($language, $courseID, $exerciseID)
    {
        $ok = false;

        $sqlSelect = 'select number_user_waiting, id from waiting_room where id_course = '.$courseID.' and id_exercise = ' . $exerciseID;
        $resultSelect = $this->consulta($sqlSelect);
        $waiting_room_id = -1;
        
        if ($this->numResultats($resultSelect) > 0){
            
            $resultSelect = $this->obteComArray($resultSelect);
            
            $waiting_room_id = $resultSelect[0]['id'];
           
            $sqlPrueba = "UPDATE waiting_room SET number_user_waiting = number_user_waiting - 1  WHERE id_course = ".$courseID." and id_exercise = ".$exerciseID;
            $ok = $this->consulta($sqlPrueba);
            
           
            $result = $this->addOrRemoveUserToWaitingRoom($waiting_room_id, -1);
            
        }else{
           
            //no hi han usuaris esperant **** en principi MAI ACCEDIREM AQUI
            
        }
        return $ok;
        
        
    }
    
    
    
    /**
     *
     * @param type $language
     * @param type $courseID
     * @param type $exerciseID
     */
    public function start_tandem($language, $courseID, $exerciseID)
    {
        $resultSelect = false;

        $sqlSelect = 'select number_user_waiting from waiting_room where id_exercise = ' . $exerciseID . ' and language = ' . $this->escapeString($language) . ' and id_course = ' . $courseID;
        $resultSelect = $this->consulta($sqlSelect);
        if ($this->numResultats($resultSelect) > 0) {
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
    
    public function updateWaitingDB($language, $idCourse, $idExercise, $idUser, $idRscLti,$onlyExID,$is_tandem = false) {
       
        $idExercise =  str_replace('%2F','/', $idExercise);
        
        $other_language = $language == 'en_US'?'es_ES':'en_US';

        //1st. Check it there are other language offering
        $tandem_waiting_room_other_lang_offered = $this->getWaitingTandemRoom($idCourse, $other_language, $onlyExID);
        $tandem_waiting_room = true;
        $ok = false;
        
        if (!$tandem_waiting_room_other_lang_offered) {
              if($is_tandem === true){
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
           
            $ok  = $this->offer_exercise($language, $idCourse, $onlyExID,$idUser);
        }else{
            //return 'tandem_exercise';
            $array=array();
            
            //false $user_tandem_host
            //retorna false !!! i salta ... no fa tandem ... xque?¿
            $user_tandem_host=$this->getFirstUserWaiting($idCourse,$onlyExID,$other_language);
            if ($user_tandem_host && count($user_tandem_host)>0){
                
                $id_user_host = $user_tandem_host[0]['id_user'];
                $id_tandem = $user_tandem_host[0]['id'];
                $id_waiting_room= $user_tandem_host[0]['id_waiting_room'];
                
                //insert us on tandem
                $resultUpdate = $this->updateUserGuestTandem($id_tandem,$idUser);
                
                //$this->userIsNoWaitingMore($language,$idCourse,$idUser,$type='assigned',$id_tandem);
                
                $this->userIsNoWaitingMore($other_language,$idCourse,$id_user_host,$type='assigned',$id_tandem);
                
                //$ok = $this->tandem_exercise($language, $idCourse, $onlyExID);
               
            }else {
             if($is_tandem === true){
                return "room_taken";
              } 
            }
           
        }
        
        return $ok;
    }
    

    public function getWaitingTandemRoom($courseID, $language, $id_exercise){
        
        
        $sql="SELECT ce.id_exercise, e.name, e.relative_path, e.name_xml_file, wr.language, coalesce(wr.number_user_waiting,0) as number_user_waiting FROM `course_exercise` as ce 
            inner join exercise as e on ce.id_exercise = e.id
            left join waiting_room as wr on ce.id_course = ce.id_course and ce.id_exercise = wr.id_exercise and wr.number_user_waiting>0
            WHERE ce.id_course = ".$courseID." and wr.language = ".$this->escapeString($language)." and wr.id_exercise = ".$this->escapeString($id_exercise)."  order by ce.id_exercise, wr.language";
      
       
       $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $row = $this->obteComArray($result);
            return $row;
        } else {
            return false;
        }
    }
    
    
    /***************************************************************************/
    /*   ASPECTE VISUAL DE LA TAULA DEL TANDEM - WAITING ROOM  */
    /****************************************************************************/
    
    
     public function getWaitingTandemRoomTable($courseID){
        
 
        $sql="SELECT wr.id as waitingRoomId, ce.id_exercise, e.name, e.relative_path, e.name_xml_file, wr.language, coalesce(wr.number_user_waiting,0) as number_user_waiting FROM `course_exercise` as ce 
            inner join exercise as e on ce.id_exercise = e.id
            left join waiting_room as wr on ce.id_course = ce.id_course and ce.id_exercise = wr.id_exercise and wr.number_user_waiting>0
            WHERE ce.id_course = ".$courseID." order by ce.id_exercise, wr.language";
     
       
       $result = $this->consulta($sql);
        if ($this->numResultats($result) > 0) {
            $row = $this->obteComArray($result);
            return $row;
        } else {
            return false;
        }
    }




    /****************************
     * Victor - auto load tandem.
     ****************************/

    /**
     *  Get all the exercices of the week that the user hasnt finished yet.
     */    
    function getExercicesNotDoneWeek($id_course,$user_id){
        $id_course = 2;
        $sql = 'SELECT id_exercise from course_exercise  WHERE week in( select max(week) from course_exercise) and id_course = '.$id_course;
        $result = $this->consulta($sql);

        if ($this->numResultats($result) > 0) {        
            $ids_exercise = array_values($this->obteComArray($result));             
            foreach($ids_exercise as $value){
                $ids[] = $value['id_exercise'];
            }           
            $sql = "SELECT distinct(id_exercise) from tandem where id_exercise in('".implode(",",$ids)."')
                    and id_user_guest != '".$user_id."' and id_user_host != '".$user_id."' ";               
             $result = $this->consulta($sql);
              if ($this->numResultats($result) > 0) {
                 $r =  $this->obteComArray($result);                  
                    foreach($r as $value){
                      $ids[] = $value['id_exercise'];
                     }
              }
              return $ids;
        }
        return array();
    }
    
    /**
     *  We pass an array of exercices the user is waiting or 
     */
    public function checkIfAvailableTandemForExercise($exercises_ids,$id_course,$language,$user_id,$otherlanguage){
        
        //lets see if there is someone waiting for one ot these exercises.
        foreach($exercises_ids as $id_ex){
            $val = $this->checkForTandems($id_ex,$id_course,$otherlanguage);
            if(!empty($val)){
                // We have someone already waiting for one of the exercises :)
                return $val;
            }        
        }

        $this->deleteUserFromWaitingRooms($user_id,$id_course);
        //if we are here is because there is no one for this exercise, so lets offer them.         
        foreach($exercises_ids as $id_ex){          
         $this->offer_exercise_autoassign($language, $id_course, $id_ex,$user_id);
        }  

        return false;     
    }  
    /**
     * Here we check if there are any tandems available from all the exercises id of the user.
     */
    public function checkForTandems($exercises_ids,$id_course,$otherlanguage){

         if(strpos($exercises_ids,",") !== false){
            $exs = explode(",",$exercises_ids);
         }else
         {
           $exs[] = $exercises_ids; 
         }

         foreach($exs as $id_ex){            
            $sql = "select wr.*,wru.id_user as guest_user_id from waiting_room as wr
                    inner join waiting_room_user as wru on wru.id_waiting_room = wr.id
             where wr.language='".$otherlanguage."' 
             and wr.id_course ='".$id_course."' 
             and wr.id_exercise= '".$id_ex."'";  
             echo $sql;        
            $result = $this->consulta($sql);
            if ($this->numResultats($result) > 0) { 
                return $this->obteComArray($result);
            }                    
        }
        return false;
    }

    /**
     * Delete the user from the waiting rooms the first time they come
     * TODO : update waiting_room table aswell.
     */
    public function deleteUserFromWaitingRooms($user_id,$course_id){
        $sql = "delete from waiting_room_user where id_user ='".$user_id."'";
        $this->consulta($sql);
    }  


    /**
     * We INSERT a user with a new exercise if don't exists or UPDATE the
     * existing exercise increasing +1 in the comput global in the waiting_room table
     * @param type $language
     * @param type $courseID
     * @param type $exerciseID
     */
    public function offer_exercise_autoassign($language, $courseID, $exerciseID,$idUser)
    {
        $ok = false;

        $sqlDelete = 'delete from waiting_room where number_user_waiting = 0 and id_course = '.$courseID.' and id_exercise = ' . $exerciseID;
        $resultDelete = $this->consulta($sqlDelete);
        
        $sqlSelect = 'select number_user_waiting, id from waiting_room where id_course = '.$courseID.' and id_exercise = ' . $exerciseID .' and language="'.$language.'"';
        $resultSelect = $this->consulta($sqlSelect);
        $waiting_room_id = -1;
        
        if ($this->numResultats($resultSelect) > 0){
            
            $resultSelect = $this->obteComArray($resultSelect);
            
            $waiting_room_id = $resultSelect[0]['id'];
            
            $sql= "UPDATE waiting_room SET number_user_waiting = number_user_waiting + 1  WHERE id = ".$waiting_room_id." and id_course = ".$courseID." and id_exercise = ".$exerciseID;
            $ok = $this->consulta($sql);
            
        }else{
            $sqlInsert = 'INSERT INTO waiting_room (language, id_course, id_exercise,number_user_waiting,created) VALUES (' . $this->escapeString($language) . ',' . $courseID . ',' . $exerciseID . ',1,now())';
            $ok = $this->consulta($sqlInsert);
            if ($ok) {
                
                $waiting_room_id = mysql_insert_id();
            }
            
        }
        if ($ok) {
            $this->insertUserIntoWaitingRoom($waiting_room_id, $language, $courseID, $exerciseID, $idUser);
        }
        return $ok;
    }


    /**
     * Lets delete a user_id from all the waiting rooms
     */
    function deleteFromWaitingRoom($user_id,$tandem_id){

        $resultSelect  = $this->consulta("select * from waiting_room_user where id_user =".$user_id);
        if ($this->numResultats($resultSelect) > 0){            
            $resultSelect = $this->obteComArray($resultSelect);
            foreach($resultSelect as $key){
                //insert into waiting_room_user_history
                $a = $this->consulta("insert into waiting_room_user_history (id_waiting_room,id_user,status,id_tandem,created,created_history) 
                    values('".$key['id_waiting_room']."','".$key['id_user']."','','".$tandem_id."','".$key['created']."',NOW()) ");
               
                if(mysql_affected_rows($this->conn) > 0){
                    //once we have copied it to the history , we delete it.
                    $e =$this->consulta("delete from waiting_room_user where id =".$key['id']);
                     if(mysql_affected_rows($this->conn) > 0){
                        //now lets backup the waiting_room table
                        $resultSelect2 = $this->consulta("select * from waiting_room where id=".$key['id_waiting_room']);
                        if ($this->numResultats($resultSelect2) > 0){  
                            $res = $this->obteComArray($resultSelect2);
                             $i = $this->consulta("insert into waiting_room_history(id_waiting_room,language,id_course,id_exercise,number_user_waiting,created,created_history) 
                                                    values('".$res['id']."','".$res['language']."','".$res['id_course']."','".$res['id_exercise']."','1','".$res['created']."',NOW()) ");
                             if(mysql_affected_rows($this->conn) > 0){
                                $e =$this->consulta("delete from waiting_room where id =".$res['id']);
                             }
                        }                        
                    }
                }
            }
        }       
    }

    //When we find someone to make a tandem, we create the tandem room here and return the id
    public function createTandemFromWaiting($response,$user_id,$id_resource_lti){

    //first of all lets see if isnt there already an open tandem room with our id in there
    $openTandem = $this->checkForOpenTandemRooms($user_id); 
    if(!empty($openTandem)){
        return $openTandem;
    }

    //ok lets see if this tandem isnt already created by the user.
    $sql= "select id from tandem where id_exercise = ".$response['id_exercise']." 
            and id_course = ".$response['id_course']."
            and (id_user_host = ".$response['guest_user_id']." and id_user_guest = ".$user_id.")";
    $result = $this->consulta($sql);

    //if the tandem was already created by the other user, then we are the guests.
    if ($this->numResultats($result) > 0){ 
        
        //to make sure we will delete ourselfs from all the waiting rooms
        //$gestordb->deleteFromWaitingRoom($user_id,$tandem_id);
        $result = $this->obteComArray($result);
       return  $result[0]['id'];
    }else{ 
        
        //the tandem is not yet created, lets created it and we will be he host.
        $tandem_id = $this->register_tandem($response['id_exercise'], $response['id_course'], $id_resource_lti, $user_id, $response['guest_user_id'], "", "");
        
        //$gestordb->deleteFromWaitingRoom($user_id,$tandem_id);
         return $tandem_id;
        die();
    }
    }

    public function checkForOpenTandemRooms($user_id){

        $this->consulta("select id from tandem where id_user_host ");
    }
     
}//end of class

?>
