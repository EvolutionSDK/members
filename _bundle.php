<?php

namespace Bundles\Members;
use Bundles\SQL\SQLBundle;
use Bundles\SQL\NoMatchException as NoMatch;
use Exception;
use e;

class Bundle extends SQLBundle {

	private $_currentMember = null;
	
	public function _on_framework_loaded() {
		e::configure('lhtml')->activeAddKey('hook', ':members', $this);
		e::configure('lhtml')->activeAddKey('hook', ':member', function() { return e::$members->currentMember(); });
		
		// Add manager
		e::configure('manage')->activeAddKey('bundle', __NAMESPACE__, 'members');
	}
	
	public function currentMember() {
		if(is_null($this->_currentMember)) {
			try { $this->_currentMember = $this->getMember(e::$session->data->currentMember); }
			catch(NoMatch $e) { $this->_currentMember = false; }
		}

		return $this->_currentMember;
	}
	
	public function login($email, $password, $options = array()) {
		$return = e::$sql->query("SELECT * FROM `members.account` WHERE `email` = '$email' AND `password` = md5('$password');")->row();

		/**
		 * If disclose-email is enabled, users will see more specific messages
		 * @author Nate Ferrero
		 */
		if(!$return && isset($options['disclose-email']) && $options['disclose-email']) {
			$return = e::$sql->query("SELECT * FROM `members.account` WHERE `email` = '$email';")->row();

			if(!$return)
				return array('type' => 'error', 'message' => 'No account with that email address.');
			
			/**
			 * Indicate that password setup can happen
			 * @author Nate Ferrero
			 */
			if($return['password'] === '' && isset($options['setup-password']) && $options['setup-password'])
				return 'setup-password';

			return array('type' => 'error', 'message' => 'You entered an invalid password.');
		}

		if($return) return e::$session->data->currentMember = $return['id'];
		else return array('type' => 'error', 'message' => 'Email or Password was incorrect.');
	}

	public function register($email, $password) {
		if($this->getByEmail($email))
			return array('type' => 'error', 'messsage' => 'This email is already in use.');
		
		try {
			$member = e::$members->newMember();
			$member->email = $email;
			$member->password = md5($password);
			$member->save();
			$member->linkWebapp(e::$webapp->subdomainAccount());
		}
		catch(Exception $e) {
			return array('error', $e->getMessage());
		}
	}
	
	public function getByEmail($email) {
		$return = e::$sql->query("SELECT * FROM `members.account` WHERE `email` = '$email';")->row();
		if($return) return $this->getMember($return);
		else return false;
	}
	
	public function logout() {
		if($this->currentMember())
			e::$session->data->currentMember = null;

		return true;
	}

	public function onNotAdminRedirect($to) {
		$member = $this->currentMember();
		if(!$member || $member->permission < 3)
			e\redirect($to);
	}

	public function route() {
		$currentMember = $this->currentMember();
		dump($currentMember);
		dump($currentMember->name());
	}
	
}