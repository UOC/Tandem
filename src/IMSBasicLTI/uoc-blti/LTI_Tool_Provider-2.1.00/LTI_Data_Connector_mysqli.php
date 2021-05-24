<?php
/*
 *  LTI_Tool_Provider - PHP class to include in an external tool to handle connections with a LTI 1 compliant tool consumer
 *  Copyright (C) 2012  Stephen P Vickers
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 *  Contact: stephen@spvsoftwareproducts.com
 *
 *  Version history:
 *    2.0.00  30-Jun-12  Initial release
 *    2.1.00   3-Jul-12  Added fields to tool consumer: consumer_guid, protected, last_access
*/

###
###  Class to represent a LTI Data Connector for MySQLi
###

###
#    NB This class assumes that a MySQLi connection has already been opened to the appropriate schema
###

class LTI_Data_Connector_MySQLi extends LTI_Data_Connector {

  private $dbTableNamePrefix = '';
  private $db = NULL;

###
#    Class constructor
###
  function __construct($db, $dbTableNamePrefix = '') {

    $this->db = $db;
    $this->dbTableNamePrefix = $dbTableNamePrefix;

  }


###
###  LTI_Tool_Consumer methods
###

###
#    Load the tool consumer from the database
###
  public function Tool_Consumer_load($consumer) {

    $ok = FALSE;
    $sql = 'SELECT name, secret, lti_version, consumer_name, consumer_version, consumer_guid, css_path, protected, enabled, enable_from, enable_until, last_access, created, updated ' .
           "FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' ' .
           'WHERE consumer_key = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $key = $consumer->getKey();
      $result->bind_param('s', $key);
    }
    if ($result) {
      if ($result->execute()) {
        if ($result->bind_result($consumer->name, $consumer->secret, $consumer->lti_version, $consumer->consumer_name, $consumer->consumer_version,
           $consumer->consumer_guid, $consumer->css_path, $protected, $enabled, $from, $until, $last, $created, $updated)) {
          if ($result->fetch()) {
            $consumer->protected = ($protected == 1);
            $consumer->enabled = ($enabled == 1);
            $consumer->enable_from = NULL;
            if (!is_null($from)) {
              $consumer->enable_from = strtotime($from);
            }
            $consumer->enable_until = NULL;
            if (!is_null($until)) {
              $consumer->enable_until = strtotime($until);
            }
            $consumer->last_access = NULL;
            if (!is_null($last)) {
              $consumer->last_access = strtotime($last);
            }
            $consumer->created = strtotime($created);
            $consumer->updated = strtotime($updated);
            $ok = TRUE;
          }
        }
      }
    }
    if ($result) {
      $result->close();
    }

    return $ok;

  }

###
#    Save the tool consumer to the database
###
  public function Tool_Consumer_save($consumer) {

    $ok = FALSE;
    if ($consumer->protected) {
      $protected = 1;
    } else {
      $protected = 0;
    }
    if ($consumer->enabled) {
      $enabled = 1;
    } else {
      $enabled = 0;
    }
    $time = time();
    $now = date('Y-m-d H:i:s', $time);
    $from = NULL;
    if (!is_null($consumer->enable_from)) {
      $from = date('Y-m-d H:i:s', $consumer->enable_from);
    }
    $until = NULL;
    if (!is_null($consumer->enable_until)) {
      $until = date('Y-m-d H:i:s', $consumer->enable_until);
    }
    $last = NULL;
    if (!is_null($consumer->last_access)) {
      $last = date('Y-m-d', $consumer->last_access);
    }
    $key = $consumer->getKey();
    if (is_null($consumer->created)) {
      $sql = "INSERT INTO {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' (consumer_key, name, ' .
             'secret, lti_version, consumer_name, consumer_version, consumer_guid, css_path, protected, enabled, enable_from, enable_until, last_access, created, updated) ' .
             'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('ssssssssiisssss', $key, $consumer->name, $consumer->secret, $consumer->lti_version,
           $consumer->consumer_name, $consumer->consumer_version, $consumer->consumer_guid, $consumer->css_path, $protected, $enabled, $from, $until, $last, $now, $now);
      }
    } else {
      $sql = "UPDATE {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' SET ' .
             'name = ?, secret= ?, lti_version = ?, consumer_name = ?, consumer_version = ?, consumer_guid = ?, ' .
             'css_path = ?, protected = ?, enabled = ?, enable_from = ?, enable_until = ?, last_access = ?, updated = ? ' .
             'WHERE consumer_key = ?';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('sssssssiisssss', $consumer->name, $consumer->secret, $consumer->lti_version,
           $consumer->consumer_name, $consumer->consumer_version, $consumer->consumer_guid, $consumer->css_path, $protected, $enabled, $from, $until, $last, $now, $key);
      }
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }
    if ($ok) {
      if (is_null($consumer->created)) {
        $consumer->created = $time;
      }
      $consumer->updated = $time;
    }

    return $ok;

  }

###
#    Delete the tool consumer from the database
###
  public function Tool_Consumer_delete($consumer) {

    $key = $consumer->getKey();
// Delete any nonce values for this consumer
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::NONCE_TABLE_NAME . ' WHERE consumer_key = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $ok = $result->bind_param('s', $key);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

// Delete any outstanding share keys for contexts for this consumer
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_SHARE_KEY_TABLE_NAME . ' WHERE primary_consumer_key = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $ok = $result->bind_param('s', $key);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

// Delete any users in contexts for this consumer
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' WHERE consumer_key = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $ok = $result->bind_param('s', $key);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

// Update any contexts for which this consumer is acting as a primary context
    $sql = "UPDATE {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
           'SET primary_consumer_key = NULL AND primary_context_id = NULL AND share_approved = NULL ' .
           'WHERE primary_consumer_key = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $ok = $result->bind_param('s', $key);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

// Delete any contexts for this consumer
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' WHERE consumer_key = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $ok = $result->bind_param('s', $key);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

// Delete consumer
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME . ' WHERE consumer_key = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $ok = $result->bind_param('s', $key);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

    if ($ok) {
      $consumer->initialise();
    }

    return $ok;

  }

###
#    Load all tool consumers from the database
###
  public function Tool_Consumer_list() {

    $consumers = array();

    $sql = 'SELECT consumer_key, name, secret, lti_version, consumer_name, consumer_version, consumer_guid, css_path, protected, enabled, enable_from, enable_until, last_access, created, updated ' .
           "FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONSUMER_TABLE_NAME;
    $result = $this->db->prepare($sql);
    if ($result) {
      if ($result->execute()) {
        if ($result->bind_result($consumer_key, $name, $secret, $lti_version, $consumer_name, $consumer_version, $consumer_guid,
           $css_path, $protected, $enabled, $from, $until, $last, $created, $updated)) {
          while ($result->fetch()) {
            $consumer = new LTI_Tool_Consumer($consumer_key, $this);
            $consumer->name = $name;
            $consumer->secret = $secret;
            $consumer->lti_version = $lti_version;
            $consumer->consumer_name = $consumer_name;
            $consumer->consumer_version = $consumer_version;
            $consumer->consumer_guid = $consumer_guid;
            $consumer->css_path = $css_path;
            $consumer->protected = ($protected == 1);
            $consumer->enabled = ($enabled == 1);
            $consumer->enable_from = NULL;
            if (!is_null($from)) {
              $consumer->enable_from = strtotime($from);
            }
            $consumer->enable_until = NULL;
            if (!is_null($until)) {
              $consumer->enable_until = strtotime($until);
            }
            $consumer->last_access = NULL;
            if (!is_null($last)) {
              $consumer->last_access = strtotime($last);
            }
            $consumer->created = strtotime($created);
            $consumer->updated = strtotime($updated);
            $consumers[] = $consumer;
          }
        }
      }
    }
    if ($result) {
      $result->close();
    }

    return $consumers;

  }


###
###  LTI_Context methods
###

###
#    Load the context from the database
###
  public function Context_load($context) {

    $ok = FALSE;
    $sql = 'SELECT lti_context_id, lti_resource_id, title, settings, primary_consumer_key, primary_context_id, share_approved, created, updated ' .
           "FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
           'WHERE consumer_key = ? AND context_id = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $key = $context->getKey();
      $id = $context->getId();
      $result->bind_param('ss', $key, $id);
    }
    if ($result) {
      if ($result->execute()) {
        if ($result->bind_result($context->lti_context_id, $context->lti_resource_id, $context->title, $settings,
           $context->primary_consumer_key, $context->primary_context_id, $share_approved, $created, $updated)) {
          if ($result->fetch()) {
            $context->settings = unserialize($settings);
            if (!is_array($context->settings)) {
              $context->settings = array();
            }
            $context->share_approved = (is_null($share_approved)) ? NULL : ($share_approved == 1);
            $context->created = strtotime($created);
            $context->updated = strtotime($updated);
            $ok = TRUE;
          }
        }
      }
    }
    if ($result) {
      $result->close();
    }

    return $ok;

  }

###
#    Save the context to the database
###
  public function Context_save($context) {

    $ok = FALSE;
    if (is_null($context->share_approved)) {
      $approved = NULL;
    } else if ($context->share_approved) {
      $approved = 1;
    } else {
      $approved = 0;
    }
    $time = time();
    $now = date('Y-m-d H:i:s', $time);
    $settingsValue = serialize($context->settings);
    $key = $context->getKey();
    $id = $context->getId();
    if (is_null($context->created)) {
      $sql = "INSERT INTO {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' (consumer_key, context_id, ' .
             'lti_context_id, lti_resource_id, title, settings, primary_consumer_key, primary_context_id, share_approved, created, updated) ' .
             'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('ssssssssiss', $key, $id,
           $context->lti_context_id, $context->lti_resource_id, $context->title, $settingsValue, $context->primary_consumer_key,
           $context->primary_context_id, $approved, $now, $now);
      }
    } else {
      $sql = "UPDATE {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' SET ' .
             'lti_context_id = ?, lti_resource_id = ?, title = ?, settings = ?, '.
             'primary_consumer_key = ?, primary_context_id = ?, share_approved = ?, updated = ? ' .
             'WHERE consumer_key = ? AND context_id = ?';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('ssssssisss', $context->lti_context_id, $context->lti_resource_id, $context->title, $settingsValue,
           $context->primary_consumer_key, $context->primary_context_id, $approved, $now, $key, $id);
      }
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }
    if ($ok) {
      if (is_null($context->created)) {
        $context->created = $time;
      }
      $context->updated = $time;
    }

    return $ok;

  }

###
#    Delete the context from the database
###
  public function Context_delete($context) {

    $ok = FALSE;

    $key = $context->getKey();
    $id = $context->getId();
// Delete any outstanding share keys for contexts for this consumer
    if ($ok) {
      $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_SHARE_KEY_TABLE_NAME . ' ' .
             'WHERE primary_consumer_key = ? AND primary_context_id = ?';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('ss', $key, $id);
      }
      if ($result && $ok) {
        $ok = $result->execute();
      }
      if ($result) {
        $result->close();
      }
    }

// Delete users
    if ($ok) {
      $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
             'WHERE consumer_key = ? AND context_id = ?';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('ss', $key, $id);
      }
      if ($result && $ok) {
        $ok = $result->execute();
      }
      if ($result) {
        $result->close();
      }
    }

// Update any contexts for which this is the primary context
    $sql = "UPDATE {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
           'SET primary_consumer_key = NULL AND primary_context_id = NULL ' .
           'WHERE primary_consumer_key = ? AND primary_context_id = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $ok = $result->bind_param('ss', $key, $id);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

// Delete context
    if ($ok) {
      $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
             'WHERE consumer_key = ? AND context_id = ?';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('ss', $key, $id);
      }
      if ($result && $ok) {
        $ok = $result->execute();
      }
      if ($result) {
        $result->close();
      }
    }

    if ($ok) {
      $context->initialise();
    }

    return $ok;

  }

###
#    Obtain an array of LTI_User objects for users with a result sourcedId.  The array may include users from other
#    contexts which are sharing this context.  It may also be optionally indexed by the user ID of a specified scope.
###
  public function Context_getUserResultSourcedIDs($context, $context_only, $id_scope) {

    $users = array();

    $ok = FALSE;
    $key = $context->getKey();
    $id = $context->getId();
    if ($context_only) {
      $sql = 'SELECT u.consumer_key, u.context_id, u.user_id, u.lti_result_sourcedid ' .
             "FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' AS u '  .
             "INNER JOIN {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' AS c '  .
             'ON u.consumer_key = c.consumer_key AND u.context_id = c.context_id ' .
             'WHERE (c.consumer_key = ?) AND (c.context_id = ?) AND (c.primary_consumer_key IS NULL) AND (c.primary_context_id IS NULL)';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('ss', $key, $id);
      }
    } else {
      $sql = 'SELECT u.consumer_key, u.context_id, u.user_id, u.lti_result_sourcedid ' .
             "FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' AS u '  .
             "INNER JOIN {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' AS c '  .
             'ON u.consumer_key = c.consumer_key AND u.context_id = c.context_id ' .
             'WHERE (c.consumer_key = ? AND c.context_id = ? AND c.primary_consumer_key IS NULL AND c.primary_context_id IS NULL) OR ' .
             '(c.primary_consumer_key = ? AND c.primary_context_id = ? AND share_approved = 1)';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('ssss', $key, $id, $key, $id);
      }
    }
    if ($result && $ok) {
      if ($result->execute()) {
        if ($result->bind_result($consumer_key, $context_id, $user_id, $lti_result_sourcedid)) {
          while ($result->fetch()) {
            $user = new LTI_User($context, $user_id);
            $user->consumer_key = $consumer_key;
            $user->context_id = $context_id;
            $user->lti_result_sourcedid = $lti_result_sourcedid;
            if (is_null($id_scope)) {
              $users[] = $user;
            } else {
              $users[$user->getId($id_scope)] = $user;
            }
          }
        }
      }
    }
    if ($result) {
      $result->close();
    }

    return $users;

  }

###
#    Get an array of LTI_Context_Share objects for each context which is sharing this context
###
  public function Context_getShares($context) {

    $shares = array();

    $ok = FALSE;
    $sql = 'SELECT consumer_key, context_id, title, share_approved ' .
           "FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_TABLE_NAME . ' ' .
           'WHERE primary_consumer_key = ? AND primary_context_id = ? ' .
           'ORDER BY consumer_key';
    $result = $this->db->prepare($sql);
    if ($result) {
      $key = $context->getKey();
      $id = $context->getId();
      $ok = $result->bind_param('ss', $key, $id);
    }
    if ($result && $ok) {
      if ($result->execute()) {
        if ($result->bind_result($consumer_key, $context_id, $title, $share_approved)) {
          while ($result->fetch()) {
            $share = new LTI_Context_Share();
            $share->consumer_key = $consumer_key;
            $share->context_id = $context_id;
            $share->title = $title;
            $share->approved = ($share_approved == 1);
            $shares[] = $share;
          }
        }
      }
    }
    if ($result) {
      $result->close();
    }

    return $shares;

  }


###
###  LTI_Consumer_Nonce methods
###

###
#    Load the consumer nonce from the database
###
  public function Consumer_Nonce_load($nonce) {

#
### Delete nonce values more than one day old
#
    $ok = FALSE;
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::NONCE_TABLE_NAME . ' WHERE expires < ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $now = date('Y-m-d H:i:s', time());
      $ok = $result->bind_param('s', $now);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

#
### load the nonce
#
    $ok = TRUE;
    $sql = "SELECT value AS T FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::NONCE_TABLE_NAME . ' ' .
           'WHERE consumer_key = ? AND value = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $key = $nonce->getKey();
      $value = $nonce->getValue();
      $result->bind_param('ss', $key, $value);
    }
    if ($result) {
      if ($result->execute()) {
        if ($result->bind_result($value)) {
          if (!$result->fetch()) {
            $ok = FALSE;
          }
        }
      }
    }
    if ($result) {
      $result->close();
    }

    return $ok;

  }

###
#    Save the consumer nonce in the database
###
  public function Consumer_Nonce_save($nonce) {

    $ok = FALSE;
    $sql = "INSERT INTO {$this->dbTableNamePrefix}" . LTI_Data_Connector::NONCE_TABLE_NAME . ' (consumer_key, value, expires) ' .
           'VALUES (?, ?, ?)';
    $result = $this->db->prepare($sql);
    if ($result) {
      $key = $nonce->getKey();
      $value = $nonce->getValue();
      $expires = date('Y-m-d H:i:s', $nonce->expires);
      $ok = $result->bind_param('sss', $key, $value, $expires);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

    return $ok;

  }


###
###  LTI_Context_Share_Key methods
###

###
#    Load the context share key from the database
###
  public function Context_Share_Key_load($share_key) {

// Clear expired share keys
    $ok = FALSE;
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_SHARE_KEY_TABLE_NAME . ' WHERE expires <= ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $now = date('Y-m-d H:i:s', time());
      $ok = $result->bind_param('i', $now);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

// Load share key
    $ok = FALSE;
    $id = $share_key->getId();
    $sql = 'SELECT primary_consumer_key, primary_context_id, auto_approve, expires ' .
           "FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_SHARE_KEY_TABLE_NAME . ' ' .
           'WHERE share_key_id = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $id = $share_key->getId();
      $result->bind_param('s', $id);
    }
    if ($result) {
      if ($result->execute()) {
        if ($result->bind_result($share_key->primary_consumer_key, $share_key->primary_context_id, $auto_approve, $expires)) {
          if ($result->fetch()) {
            $share_key->auto_approve = ($auto_approve == 1);
            $share_key->expires = strtotime($expires);
            $ok = TRUE;
          }
        }
      }
    }
    if ($result) {
      $result->close();
    }

    return $ok;

  }

###
#    Save the context share key to the database
###
  public function Context_Share_Key_save($share_key) {

    $ok = FALSE;
    if ($share_key->auto_approve) {
      $approve = 1;
    } else {
      $approve = 0;
    }
    $expires = date('Y-m-d H:i:s', $share_key->expires);
    $sql = "INSERT INTO {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_SHARE_KEY_TABLE_NAME .
           ' (share_key_id, primary_consumer_key, primary_context_id, auto_approve, expires) ' .
           'VALUES (?, ?, ?, ?, ?)';
    $result = $this->db->prepare($sql);
    if ($result) {
      $id = $share_key->getId();
      $ok = $result->bind_param('sssis', $id, $share_key->primary_consumer_key,
         $share_key->primary_context_id, $approve, $expires);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

    return $ok;

  }

###
#    Delete the context share key from the database
###
  public function Context_Share_Key_delete($share_key) {

    $ok = FALSE;
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::CONTEXT_SHARE_KEY_TABLE_NAME . ' WHERE share_key_id = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $id = $share_key->getId();
      $ok = $result->bind_param('s', $id);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

    if ($ok) {
      $share_key->initialise();
    }

    return $ok;

  }


###
###  LTI_User methods
###


###
#    Load the user from the database
###
  public function User_load($user) {

    $ok = FALSE;
    $sql = 'SELECT lti_result_sourcedid, created, updated ' .
           "FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
           'WHERE consumer_key = ? AND context_id = ? AND user_id = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $key = $user->getContext()->getKey();
      $id = $user->getContext()->getId();
      $userId = $user->getId(LTI_Tool_Provider::ID_SCOPE_ID_ONLY);
      $ok = $result->bind_param('sss', $key, $id, $userId);
    }
    if ($result && $ok) {
      if ($result->execute()) {
        if ($result->bind_result($user->lti_result_sourcedid, $created, $updated)) {
          if ($result->fetch()) {
            $user->created = strtotime($created);
            $user->updated = strtotime($updated);
            $ok = TRUE;
          }
        }
      }
    }
    if ($result) {
      $result->close();
    }

    return $ok;

  }

###
#    Save the user to the database
###
  public function User_save($user) {

    $ok = FALSE;
    $time = time();
    $now = date('Y-m-d H:i:s', $time);
    $key = $user->getContext()->getKey();
    $id = $user->getContext()->getId();
    $userId = $user->getId(LTI_Tool_Provider::ID_SCOPE_ID_ONLY);
    if (is_null($user->created)) {
      $sql = "INSERT INTO {$this->dbTableNamePrefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' (consumer_key, context_id, ' .
             'user_id, lti_result_sourcedid, created, updated) ' .
             'VALUES (?, ?, ?, ?, ?, ?)';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('ssssss', $key, $id, $userId, $user->lti_result_sourcedid, $now, $now);
      }
    } else {
      $sql = "UPDATE {$this->dbTableNamePrefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
             'SET lti_result_sourcedid = ?, updated = ? ' .
             'WHERE consumer_key = ? AND context_id = ? AND user_id = ?';
      $result = $this->db->prepare($sql);
      if ($result) {
        $ok = $result->bind_param('sssss', $user->lti_result_sourcedid, $now, $key, $id, $userId);
      }
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }
    if ($ok) {
      if (is_null($user->created)) {
        $user->created = $time;
      }
      $user->updated = $time;
    }

    return $ok;

  }

###
#    Delete the user from the database
###
  public function User_delete($user) {

    $ok = FALSE;
    $sql = "DELETE FROM {$this->dbTableNamePrefix}" . LTI_Data_Connector::USER_TABLE_NAME . ' ' .
           'WHERE consumer_key = ? AND context_id = ? AND user_id = ?';
    $result = $this->db->prepare($sql);
    if ($result) {
      $key = $user->getContext()->getKey();
      $id = $user->getContext()->getId();
      $userId = $user->getId(LTI_Tool_Provider::ID_SCOPE_ID_ONLY);
      $ok = $result->bind_param('sss', $key, $id, $userId);
    }
    if ($result && $ok) {
      $ok = $result->execute();
    }
    if ($result) {
      $result->close();
    }

    if ($ok) {
      $user->initialise();
    }

    return $ok;

  }

}

?>
