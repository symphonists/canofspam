<?php

	require_once(TOOLKIT . '/class.event.php');

	Class eventCanofspam extends Event {

		public static function about(){

			return array('name' => 'Can Of Spam', 
			             'version' => 'Can Of Spam 2.0',
			             'release-date' => '2012-06-18',			
			             'author' => array('name' => 'Symphony Community', 'website' => 'https://github.com/symphonists/'));
		}

		public function load() {

			// set session data

			if (!isset($_SESSION['canofspam'])) {
				
				// generate hash value
				
				$_SESSION['canofspam'] = sha1(uniqid($_SERVER['REMOTE_ADDR'], true));
			}

			// add hash value to param pool

			Frontend::Page()->_param['canofspam'] = $_SESSION['canofspam'];
		}
	}