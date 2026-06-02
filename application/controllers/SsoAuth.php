<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SSO bridge from the new (Nest) site to this legacy CodeIgniter app.
 *
 * Flow:
 *   GET /auth/sso?token=<JWT>&next=/path
 *   1. Verify the JWT against the shared HS256 secret (JWT_SHARED_SECRET).
 *   2. Read `user_number` from the payload (== member_list.idx).
 *   3. Look up the active member, write the CI session, and bounce the
 *      browser to `next` while stripping ?token=... from the URL bar +
 *      history so it doesn't leak via back-button / share / referrer.
 *   4. Any validation failure → silent redirect to /login.
 */
// firebase/php-jwt v6 has no autoloader bundled here, so we pull every class
// JWT::decode might touch (Key, exception types, JWK helper) up front.
$_jwtLib = APPPATH . 'views/libs/php-jwt/src/';
require_once $_jwtLib . 'JWTExceptionWithPayloadInterface.php';
require_once $_jwtLib . 'BeforeValidException.php';
require_once $_jwtLib . 'ExpiredException.php';
require_once $_jwtLib . 'SignatureInvalidException.php';
require_once $_jwtLib . 'Key.php';
require_once $_jwtLib . 'JWK.php';
require_once $_jwtLib . 'JWT.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class SsoAuth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->database();
    }

    public function callback() {
        $token = $this->input->get('token', TRUE);
        $next  = $this->_safeNext($this->input->get('next', TRUE));

        if (!$token) {
            $this->_finish('/login', false);
            return;
        }

        try {
            $payload = JWT::decode($token, new Key(JWT_SHARED_SECRET, 'HS256'));
            $userNumber = isset($payload->user_number) ? (int)$payload->user_number : 0;
            if (!$userNumber) {
                throw new Exception('payload missing user_number');
            }

            $user = $this->db
                ->where('idx', $userNumber)
                ->where('isUse', 'Y')
                ->get('member_list')->row_array();
            if (!$user) {
                throw new Exception("no active member_list row for idx={$userNumber}");
            }

            $this->session->set_userdata('user', $user);
            $this->_finish($next, true);
        } catch (Exception $e) {
            log_message('error', '[sso] '.$e->getMessage());
            $this->_finish('/login', false);
        }
    }

    /**
     * Whitelist `next` so it can only be an internal path. Anything else
     * (external URL, protocol-relative, missing leading slash) falls back
     * to '/'.
     */
    private function _safeNext($next) {
        if (!is_string($next) || $next === '') return '/';
        // must start with '/' but not '//' (protocol-relative).
        if (!preg_match('#^/[^/]#', $next)) return '/';
        return $next;
    }

    /**
     * Plain HTTP 302 redirect to $dest. We *want* the URL bar to NOT keep
     * `?token=...`, but the JS-based replaceState approach (used previously)
     * doesn't run reliably inside SFSafariViewController / WKWebView shells —
     * those rendered the HTML body blank because the redirect script never
     * executed. The 302 path works everywhere; the token still ends up in
     * one access-log line on cafe24, but the Referrer-Policy header keeps it
     * out of the next page's Referer header, and Cache-Control: no-store
     * stops it from being cached by intermediaries.
     */
    private function _finish($dest, $ok) {
        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, private')
            ->set_header('Pragma: no-cache')
            ->set_header('Referrer-Policy: no-referrer');
        // ok flag retained for future audit logging; redirect happens regardless.
        unset($ok);
        redirect($dest);
    }
}
