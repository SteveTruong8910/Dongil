<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserApiModel extends CI_Model {
        
    public function __construct(){
        parent::__construct();
    }
    
    function saveAuthEmail($mbEmail, $authNumber){
        $sql = "
            INSERT INTO
                auth_email
			 SET
                mbEmail = ?,
                authNumber = ?,
                regDate = NOW();
        ";
        
        return $this->db->query($sql, array(
            $mbEmail,
            $authNumber
        ));
    }
    
    function saveAuthMessage($phoneNumber, $authNumber){
        $sql = "
            INSERT INTO
                auth_message
			 SET
                phoneNumber = ?,
                authNumber = ?,
                regDate = NOW();
        ";
        
        return $this->db->query($sql, array(
            $phoneNumber,
            $authNumber
        ));
    }
    
    function getUserInfo($mbId){
        $sql = "
            SELECT
                idx
            FROM
                member_list
            WHERE
                mbId = ? AND                          
                isUse = 'Y';
        ";
                        
        $query = $this->db->query($sql, array(
            $mbId
        ));
        
        return $query->row_array();
    }
    
    function chkAuthNumber($mbEmail){
        $sql = "
            SELECT
                authNumber                
            FROM
                auth_email
            WHERE
                mbEmail = ? AND
                isUse = 'Y'
            ORDER BY
                idx DESC
            LIMIT
                1
        ";
                        
        $query = $this->db->query($sql, array(
            $mbEmail            
        ));
        
        return $query->row_array()['authNumber'];
    }
    
    function chkPhoneAuthNumber($phoneNumber){
        $sql = "
            SELECT
                authNumber                
            FROM
                auth_message
            WHERE
                phoneNumber = ? AND
                isUse = 'Y'
            ORDER BY
                idx DESC
            LIMIT
                1
        ";
                        
        $query = $this->db->query($sql, array(
            $phoneNumber            
        ));
        
        return $query->row_array()['authNumber'];
    }
    
    function changePwd($mbPassword, $nickname, $mbEmail){
        $sql = "
            UPDATE
                member_list
            SET
                mbPassword = password(?)
            WHERE
                nickname = ? AND
                mbEmail = ?                
        ";
        
        return $this->db->query($sql, array(            
            $mbPassword,
            $nickname,
            $mbEmail
        ));
    }
}
