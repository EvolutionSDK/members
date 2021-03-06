<?php

namespace Bundles\LHTML\Nodes;
use Bundles\LHTML\Node;
use Exception;
use stack;
use e;

class Authenticate extends Node {
	
	public function ready() {
		$this->element = false;
	}
	
	public function build() {
		$this->element = false;
		$member = e::$members->currentMember();
		if(!$member) $auth = 0;
		else $auth = $member->permission;
		
		if(!isset($this->attributes['lte']) && !isset($this->attributes['gte']))
			throw new Exception("Cannot use `&lt;:authenticate&rt;` without the lte or gte attribute");
		
		/**
		 * Determine what permission level is required
		 */
		$gte = isset($this->attributes['gte']) ? $this->attributes['gte'] : false;
		$lte = isset($this->attributes['lte']) ? $this->attributes['lte'] : false;
		if($gte != false && $auth >= $gte) return parent::build();
		if($lte != false && $auth <= $lte) return parent::build();
		
		/**
		 * Get Redirection URL
		 */
		$url = isset($this->attributes['redir']) ? $this->attributes['redir'] : false;
		
		if($url) {
			header("Location: $url");
			exit;
		}
		
		return;
	}
	
}