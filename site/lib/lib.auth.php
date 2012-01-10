<?php

    require_once 'data.php';

    function add_user(&$dbh)
    {
        while(true)
        {
            $user_id = generate_id();
            
            $q = sprintf('INSERT INTO users
                          SET id = %s',
                         $dbh->quoteSmart($user_id));

            error_log(preg_replace('/\s+/', ' ', $q));
    
            $res = $dbh->query($q);
            
            if(PEAR::isError($res)) 
            {
                if($res->getCode() == DB_ERROR_ALREADY_EXISTS)
                    continue;
    
                die_with_code(500, "{$res->message}\n{$q}\n");
            }
            
            return get_user($dbh, $user_id);
        }
    }
    
    function get_user(&$dbh, $user_id)
    {
        $q = sprintf('SELECT id, name,
                             UNIX_TIMESTAMP(created) AS created,
                             UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(created) AS age
                      FROM users
                      WHERE id = %s',
                     $dbh->quoteSmart($user_id));
    
        $res = $dbh->query($q);
        
        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");

        return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
    
    function get_user_by_name(&$dbh, $user_name)
    {
        $q = sprintf('SELECT id, name,
                             UNIX_TIMESTAMP(created) AS created,
                             UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(created) AS age
                      FROM users
                      WHERE name = %s',
                     $dbh->quoteSmart($user_name));
    
        $res = $dbh->query($q);
        
        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");

        return $res->fetchRow(DB_FETCHMODE_ASSOC);
    }
    
    function set_user(&$dbh, $user)
    {
        $old_user = get_user($dbh, $user['id']);
        
        if(!$old_user)
            return false;

        $update_clauses = array();

        if(!is_null($user['name']) && $user['name'] != $old_user['name'])
            $update_clauses[] = sprintf('name = %s', $dbh->quoteSmart($user['name']));
        
        if(!is_null($user['email']) && $user['email'] != $old_user['email'])
            $update_clauses[] = sprintf('email = %s', $dbh->quoteSmart($user['email']));
        
        if(!is_null($user['password']))
            $update_clauses[] = sprintf('password = SHA1(%s)', $dbh->quoteSmart($user['password']));
        
        if(empty($update_clauses)) {
            error_log("skipping user {$user['id']} update since there's nothing to change");

        } else {
            $update_clauses = join(', ', $update_clauses);
            
            $q = "UPDATE users
                  SET {$update_clauses}
                  WHERE id = ".$dbh->quoteSmart($user['id']);
    
            error_log(preg_replace('/\s+/', ' ', $q));
    
            $res = $dbh->query($q);
            
            if(PEAR::isError($res)) 
            {
                if($res->getCode() == DB_ERROR_ALREADY_EXISTS)
                {
                    return false;
                }
    
                die_with_code(500, "{$res->message}\n{$q}\n");
            }
        }

        return get_user($dbh, $user['id']);
    }
    
    function delete_user(&$dbh, $user_id)
    {
        $q = sprintf('DELETE FROM users
                      WHERE id = %s',
                     $dbh->quoteSmart($user_id));

        error_log(preg_replace('/\s+/', ' ', $q));

        $res = $dbh->query($q);
        
        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");

        return true;
    }
    
   /**
    * Return true if a given user ID and password match the database.
    */
    function check_user_password(&$dbh, $user_id, $password)
    {
        $q = sprintf('SELECT password = SHA1(%s)
                      FROM users
                      WHERE id = %s
                      LIMIT 1',
                     $dbh->quoteSmart($password),
                     $dbh->quoteSmart($user_id));
    
        $res = $dbh->query($q);
        
        if(PEAR::isError($res)) 
            die_with_code(500, "{$res->message}\n{$q}\n");

        $match = $res->fetchRow();
        
        return $match[0] ? true : false;
    }
    
   /**
    * Functions below all assume that session_start() has already been called.
    */

   /**
    * Return false if $_SESSION['logged-in'] is false, true otherwise.
    */
    function is_logged_in()
    {
    }
    
   /**
    * Return user array from get_user() based on content
    * of $_SESSION['user'], or false if it's empty.
    */
    function cookied_user(&$dbh)
    {
    }
    
   /**
    * Set $_SESSION['logged-in'] to true and populate $_SESSION['user'] from DB.
    */
    function login_user(&$dbh, $user_id)
    {
    }
    
   /**
    * Set $_SESSION['logged-in'] to false and erase $_SESSION['user'].
    */
    function logout_user()
    {
    }
    
   /**
    * For a visitor who's never been seen before, make up a new user_id in the
    * database with add_user() and track that person via $_SESSION['user'] that
    * doesn't have name or email.
    *
    * For a visitor who has a $_SESSION['user'] id, just make sure
    * it's valid and create a new one if it's not (see above).
    */
    function remember_user(&$dbh)
    {
    }
    
?>