<?php

class UserLogin {
	var $_valid=true;
	var $_id;
	var $_name;
	function UserLogin($name) {
		switch (strtolower($name)) {
		case 'admin':
			$this->_id = 1;
			$this->_name = 'admin';
			break;
		default:
			trigger_error("Bad user name '$name'");
			$this->_valid=false;
		}
	}
	/**
	 * return the user name if valid
	 * return	@string
	 */
	function name() {
		if ($this->_valid) return $this->_name;
	}
	/**
	 * static method to validate if a user_name/password combination
	 * is valid
	 * @param	string	$user_name
	 * @param	string	$password
	 * @return	boolean
	 */
	function Validate($user_name, $password) {
		if ('admin' == strtolower($user_name)
			&& 's3cr3t' == $password) {
			return true;
		}
		return false;
	}
}

class Response {
	var $_head='';
	var $_body='';
	/**
	 * add some content to the head section of the document
	 * @param	string	$content
	 * @return 	void
	 */
	function addHead($content) {
		$this->_head .= $content;
	}
	/**
	 * add some content to the body section of the document
	 * @param	string	$content
	 * @return 	void
	 */
	function addBody($content) {
		$this->_body .= $content;
	}
	/**
	 * adds a simple template mechanism to the response class
	 * @param	string	$template	the path and name of the template file
	 * @return	void
	 */
	function addBodyTemplate($template, $vars=array()) {
		if (file_exists($template)) {
			extract($vars);
			ob_start();
			include $template;
			$this->_body .= ob_get_clean();
		}
	}
	/**
	 * send the output of the response to the browser
	 * @return	void
	 */
	function display() {
		echo $this->fetch();
	}
	/**
	 * return the content of the response as a string
	 * @return	string
	 */
	function fetch() {
		return '<html>'
			.'<head>'.$this->_head.'</head>'
			.'<body>'.$this->_body.'</body>'
			.'</html>';
	}
	/**
	 * issue an http retirect
	 * @param	string	$url	the url to redirect to
	 * @param	boolean	$exit	optional - exit after redirect, default true
	 * @return	void
	 */
	function redirect($url, $exit=true) {
		header('Location: '.$url);
		if ($exit) exit;
	}
}

class Session {
	/**
	 * constructor
	 * trigger an error if the session has not yet been started
	 * @return	void
	 */
	function Session() {
		$this->init();
	}
	/**
	 * start the session if not yet started, or trigger an error if headers are already sent
	 * @return	void
	 */
	function init() {
		if (!isset($_SESSION)) {
			if (headers_sent()) {
				trigger_error('Session not started before creating session object');
			} else {
				session_start();
			}
		}
	}
	/**
	 * check if a particular session key is valid
	 * @param	string	$key
	 * @return	boolean
	 */
	function isValid($key) {
		return array_key_exists($key, $_SESSION);
	}
	/** 
	 * retrieve a session key's value
	 * @param	string	$key
	 * @return	mixed
	 */
	function get($key) {
		return (array_key_exists($key, $_SESSION))
			? $_SESSION[$key]
			: null;
	}
	/**
	 * set a session key
	 * @param	string	$key
	 * @param	mixed	$value
	 * @return	void
	 */
	function set($key, $value) {
		$_SESSION[$key] = $value;
	}
	/**
	 * remove a session key if set
	 * @param	string	$key
	 * @return	void
	 */
	function clear($key) {
		unset($_SESSION[$key]);
	}
}

class PageDirector {
	var $session;
	var $response;
	function PageDirector(&$session, &$response) {
		$this->session =& $session;
		$this->response =& $response;
	}
	function isLoggedIn() {
		return ($this->session->get('user_name')) ? true : false;
	}
	function run() {
		$this->processLogin();
		if ($this->isLoggedIn()) {
			$this->showPage(new UserLogin($this->session->get('user_name')));
		} else {
			$this->showLogin();
		}
		$this->response->display();
	}
	function showPage(&$user) {
		$vars = array(
			 'name' => $user->name()
			,'self' => SELF
			);
		$this->response->addBodyTemplate('page.tpl', $vars);
	}
	function processLogin() {
		if (array_key_exists('clear', $_REQUEST)) {
			$this->session->clear('user_name');
			$this->response->redirect(SELF);
		}
		if (array_key_exists('name', $_REQUEST)
			&& array_key_exists('passwd', $_REQUEST)
			&& UserLogin::validate($_REQUEST['name'], $_REQUEST['passwd'])) {
			$this->session->set('user_name', $_REQUEST['name']);
			$this->response->redirect(SELF);
		}
	}
	function showLogin() {
		$vars = array('self' => SELF);
		$this->response->addBodyTemplate('login.tpl', $vars);
	}
}
