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
 *    2.1.00   3-Jul-12
*/

###
###  Class to represent an empty (no operation) LTI Data Connector
###

class LTI_Data_Connector_None extends LTI_Data_Connector {

###
###  LTI_Tool_Consumer methods
###

###
#    Load the tool consumer from the database
###
  public function Tool_Consumer_load($consumer) {

    return TRUE;

  }

###
#    Save the tool consumer to the database
###
  public function Tool_Consumer_save($consumer) {

    return TRUE;

  }

###
#    Delete the tool consumer from the database
###
  public function Tool_Consumer_delete($consumer) {

    return TRUE;

  }

###
#    Load all tool consumers from the database
###
  public function Tool_Consumer_list() {

    $consumers = array();

    return $consumers;

  }

###
###  LTI_Context methods
###

###
#    Load the context from the database
###
  public function Context_load($context) {

    return TRUE;

  }

###
#    Save the context to the database
###
  public function Context_save($context) {

    return TRUE;

  }

###
#    Delete the context from the database
###
  public function Context_delete($context) {

    return TRUE;

  }

###
#    Obtain an array of LTI_User objects for users with a result sourcedId.  The array may include users from other
#    contexts which are sharing this context.  It may also be optionally indexed by the user ID of a specified scope.
###
  public function Context_getUserResultSourcedIDs($context, $context_only, $id_scope) {

    $users = array();

    return $users;

  }

###
#    Get an array of LTI_Context_Share objects for each context which is sharing this context
###
  public function Context_getShares($context) {

    $shares = array();

    return $shares;

  }


###
###  LTI_Consumer_Nonce methods
###

###
#    Load the consumer nonce from the database
###
  public function Consumer_Nonce_load($nonce) {

    return TRUE;

  }

###
#    Save the consumer nonce in the database
###
  public function Consumer_Nonce_save($nonce) {

    return TRUE;

  }


###
###  LTI_Context_Share_Key methods
###

###
#    Load the context share key from the database
###
  public function Context_Share_Key_load($share_key) {

    return TRUE;

  }

###
#    Save the context share key to the database
###
  public function Context_Share_Key_save($share_key) {

    return TRUE;

  }

###
#    Delete the context share key from the database
###
  public function Context_Share_Key_delete($share_key) {

    return TRUE;

  }


###
###  LTI_User methods
###


###
#    Load the user from the database
###
  public function User_load($user) {

    return TRUE;

  }

###
#    Save the user to the database
###
  public function User_save($user) {

    return TRUE;

  }

###
#    Delete the user from the database
###
  public function User_delete($user) {

    return TRUE;

  }

}

?>
