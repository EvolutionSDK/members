<?php

namespace Bundles\Members;
use Bundles\Manage\Tile;
use e;

/**
 * Members PHP Manage
 * @author Nate Ferrero
 */
class Manage {
	
	public $title = 'Members';
	
	public function page($path) {

		/**
		 * Wrapper Style
		 */
		$_a = '<div class="section" style="margin-top: 1em">';
		$_b = '</div>';

		/**
		 * Edit a member
		 * @author Nate Ferrero
		 */
		$id = array_shift($path);
		if($id == 'action')
			return $_a . $this->action($path[0], $path[1]) . $_b;
		if(is_numeric($id))
			return $_a . $this->member($id) . $_b;

		/**
		 * Show Members List
		 */
		$list = '';
		$headers = '<tr><th>ID</th><th>Joined</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Actions</th></tr>';
		foreach(e::$members->getMembers() as $member) {
			$time = strtotime($member->created_timestamp);
			$joined = $time > 0 ? date('M jS Y', $time) : 'Unknown';
			$list .= '<tr>'.
				'<td>' . $member->id . '</td>'.
				'<td>' . $joined . '</td>'.
				'<td>' . $member->first_name . '</td>'.
				'<td>' . $member->last_name . '</td>'.
				'<td>' . $member->email . '</td>'.
				'<td><a href="/@manage/members/'.$member->id.'">Manage</a></td>'.
			'</tr>';
		}
		return $_a . '<table class="style">' . $headers . $list . '</table> ' . $_b;
	}

	public function member($id) {
		$member = e::$members->getMember($id);
		return <<<_

<h2><span style="font-weight: normal">Member details:</span> $member->first_name $member->last_name <span style="font-weight: normal">/ $member->email</span></h2>
<hr/>
<h3>Set Password</h3>
<form action="/@manage/members/action/$id/setPassword" method="POST">
	Choose password: <input type="text" name="password" />
	<input type="submit" />
</form>
_;
	}

	public function action($id, $action) {
		$member = e::$members->getMember($id);
		switch($action) {
			case 'setPassword':
				$member->setPassword($_POST['password']);
				return "Password updated for <a href='/@manage/members/$id'>$member->first_name $member->last_name</a>";
				break;
		}

	}
	
	public function tile() {
	    $tile = new Tile('members');
	    $mc = e\plural(e::$members->getMembers()->count(), 'member account/s');
    	$tile->body .= '<h2>Manage the '.$mc.'.</h2>';
    	return $tile;
    }
}