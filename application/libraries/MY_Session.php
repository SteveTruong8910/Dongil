<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Session extends CI_Session {

	protected function _set_cookie($cookie_data = NULL)
	{
		if (is_null($cookie_data)) {
			$cookie_data = $this->userdata;
		}

		$expire = $cookie_data['last_activity'] + $this->sess_expiration;

		// Lấy config
		$secure_cookie = $this->CI->config->item('cookie_secure');
		$samesite = $this->CI->config->item('cookie_samesite') ?: 'Lax';

		// Tạo header Set-Cookie với SameSite
		setcookie(
			$this->sess_cookie_name,
			$this->_serialize($cookie_data),
			[
				'expires' => $expire,
				'path' => $this->cookie_path,
				'domain' => $this->cookie_domain,
				'secure' => $secure_cookie,
				'httponly' => TRUE,
				'samesite' => $samesite
			]
		);
	}
}
