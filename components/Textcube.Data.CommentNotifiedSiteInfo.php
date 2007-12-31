<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class CommentNotifiedSiteInfo {
	function CommentNotifiedSiteInfo() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->title =
		$this->name=
		$this->url =
		$this->modified =
			null;
	}
	
	function open($filter = '', $fields = '*', $sort = 'id') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = mysql_query("SELECT $fields FROM {$database['prefix']}CommentsNotifiedSiteInfo WHERE True $filter $sort");
		if ($this->_result) {
			if ($this->_count = mysql_num_rows($this->_result))
				return $this->shift();
			else
				mysql_free_result($this->_result);
		}
		unset($this->_result);
		return false;
	}
	
	function close() {
		if (isset($this->_result)) {
			mysql_free_result($this->_result);
			unset($this->_result);
		}
		$this->_count = 0;
		$this->reset();
	}
	
	function shift() {
		$this->reset();
		if ($this->_result && ($row = mysql_fetch_assoc($this->_result))) {
			foreach ($row as $name => $value) {
				if ($name == 'blogid')
					continue;
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add() {
		global $database;
		if (!isset($this->id))
			$this->id = $this->nextId();
		else $this->id = $this->nextId($this->id);
		if (!isset($this->entry))
			return $this->_error('entry');
		if (!isset($this->title))
			return $this->_error('title');
		if (!isset($this->name))
			return $this->_error('name');
		if (!isset($this->url))
			return $this->_error('url');
		
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->hasAttribute('modified'))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');
		
		if (!$query->insert())
			return $this->_error('insert');
//		$this->id = $query->id;
		
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function getCommentsNotified() {
		if (!Validator::number($this->id, 1))
			return null;
		$comment = new CommentNotified();
		if ($comment->open('siteId = ' . $this->id))
			return $comment;
	}
	
	/*@static@*/
	function getEntry($id) {
		global $database;
		if (!Validator::number($id, 1))
			return null;
		return POD::queryCell("SELECT entry FROM {$database['prefix']}CommentsNotifiedSiteInfo WHERE id = {$id}");
	}

	function nextId($id = 0) {
		global $database;
		$maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}CommentsNotifiedSiteInfo");
		if($id == 0)
			return $maxId + 1;
		else
			 return ($maxId > $id ? $maxId : $id);
	}

	function _buildQuery() {
		global $database;
		$query = new TableQuery($database['prefix'] . 'CommentsNotifiedSiteInfo');
		$query->setQualifier('blogid', getBlogId());
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', $this->id);
		}
		if (isset($this->entry)) {
			if (!Validator::number($this->entry, 1))
				return $this->_error('entry');
			$query->setAttribute('entry', $this->entry);
		}
		if (isset($this->title)) {
			$this->title = UTF8::lessenAsEncoding(trim($this->title), 255);
			if (empty($this->title))
				return $this->_error('title');
			$query->setAttribute('title', $this->title, true);
		}
		if (isset($this->name)) {
			$this->name = UTF8::lessenAsEncoding(trim($this->name), 255);
			if (empty($this->name))
				return $this->_error('name');
			$query->setAttribute('name', $this->name, true);
		}
		if (isset($this->url)) {
			$this->url = UTF8::lessenAsEncoding(trim($this->url), 255);
			if (empty($this->url))
				return $this->_error('url');
			$query->setAttribute('url', $this->url, true);
		}
		if (isset($this->modified)) {
			if (!Validator::timestamp($this->modified))
				return $this->_error('modified');
			$query->setAttribute('modified', $this->modified);
		}
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>