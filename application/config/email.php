<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['emailConfig'] = array(
    'useragent' => '',
    'protocol' => 'smtp',
    'smtp_host' => 'ssl://smtp.naver.com',
    'smtp_user' => 'dongl_kr@naver.com',
    'smtp_pass' => '8WGEMBZQRX6K',
    'smtp_port' => 465,
    'smtp_timeout' => 5,
    'wordwrap' => TRUE,
    'wrapchars' => 76,
    'mailtype' => 'html',
    'charset' => 'utf-8',
    'validate' => FALSE,
    'priority' => 3,
    'crlf' => "\r\n",
    'newline' => "\r\n",
    'bcc_batch_mode' => FALSE,
    'bcc_batch_size' => 200
);
