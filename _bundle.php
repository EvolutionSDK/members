<?php

namespace Bundles\Members;
use Bundles\SQL\SQLBundle;
use Exception;
use e;

class Bundle extends SQLBundle {
	
	public function __initBundle() {
		e::$events->lhtml_add_hook(':members', $this);
		e::$events->lhtml_add_hook(':member', $this->currentMember());
	}
	
	public function currentMember() {
		try { return e::$session->getMembersMember(); }
		catch(\Bundles\SQL\NoMatchException $e) { return false; }
	}
	
	public function login($email, $password) {
		$return = e::$sql->query("SELECT * FROM `members.account` WHERE `email` = '$email' AND `password` = md5('$password');")->row();
		if($return) return $this->getMember($return)->linkSessionSession(e::$session->_id);
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
		if($tmp = $this->currentMember())
			$tmp->unlinkSessionSession(e::$session->_id);

		return true;
	}

	public function onNotAdminRedirect($to) {
		$member = $this->currentMember();
		if(!$member || $member->permission < 3)
			e\redirect($to);
	}
	
}