<?php

namespace Bundles\Members\Models;
use Bundles\SQL\Model;
use Exception;
use e;

class Account extends Model {
	
	/**
	 * Get HTML Link
	 */
	public function __getHTMLLink($portal) {
		if($portal)
			$portal .= "/";
		return '<a href="/'.$portal.'member/'.$this->id.'">'.$this->name() . '</a>';
	}
	
	/**
	 * Login this Member
	 *
	 * @return void
	 * @author Kelly Lauren Summer Becker
	 */
	public function login() {
		return $this->linkSessionSession(e::$session->_id);
	}
	
	public function name() {
		if(!$this->first_name && !$this->last_name) {
			$name = 'Unknown Name';
		}
		else 
			$name = $this->first_name.' '.$this->last_name;
		return $name;
	}

	public function setPassword($pass) {
		$this->password = md5($pass);
		$this->save();
	}

	public function isCurrentMember() {
		$member = e::$members->currentMember();
		if(!is_object($member))
			return false;
		return $this->id > 0 && $this->id === $member->id;
	}
	
}