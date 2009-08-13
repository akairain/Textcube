<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class Model_Line extends DBModel {
	private $filter = array();

	public function __construct() {
		global $database;
		parent::reset($database['prefix']."Lines");
		$this->reset();
	}

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}
		
	public function reset() {
		$this->id = null;
		$this->blogid = getBlogId();
		$this->category = 'public';
		$this->content = '';
		$this->created = null;
		$this->filter = array();
		$this->_error = array();
	}

/// Methods for managing	
	public function add() {
		if(is_null($this->created)) $this->created = Timestamp::getUNIXTime();
		if(!$this->validate()) return false;
		$this->setAttribute('id',$this->id);
		$this->setAttribute('blogid',$this->blogid);
		$this->setAttribute('category',$this->category,true);
		$this->setAttribute('content',$this->content,true);
		$this->setAttribute('created',$this->created);
		return $this->insert();
	}
	
	public function delete(){
		global $database;
		if(empty($this->filter)) return $this->error('Filter empty');
		foreach($this->filter as $filter) {
			if(count($filter) == 3) {
				$this->setQualifier($filter[0],$filter[1],$filter[2]);
			} else {
				$this->setQualifier($filter[0],$filter[1],$filter[2],$filter[3]);			
			}
		}
		return $this->delete();
	}
/// Methods for querying
	public function get($fields = '*') {
		if(empty($this->filter)) return $this->error('Filter empty');
		foreach($this->filter as $filter) {
			if(count($filter) == 3) {
				$this->setQualifier($filter[0],$filter[1],$filter[2]);
			} else {
				$this->setQualifier($filter[0],$filter[1],$filter[2],$filter[3]);			
			}
		}
		$this->setOrder('created','desc');
		return $this->getAll($fields);		
	}
	
	/// @input condition<array> [array(name, condition, value, [need_escaping])]
	public function setFilter($condition) {
		if(!in_array(count($condition),array(3,4))) return $this->error('wrong filter');
		array_push($this->filter, $condition);
	}

/// Aliases
	/// conditions [array(page=>value<int>, linesforpage=>value<int>)]
	public function getWithConditions($conditions) {
		$count = 10;
		$offset = 0;
		if(isset($conditions['page'])) $page = $conditions['page'];
		if(isset($conditions['linesforpage'])) {
			$count = $conditions['linesforpage'];
			$offset = ($page - 1) * $count;
		}
		if(isset($conditions['category'])) $this->setQualifier('category','equals',$conditions['category'],true);
		if(isset($conditions['blogid'])) $this->setQualifier('blogid','equals',$conditions['blogid']);
		else $this->setQualifier('blogid','equals',getBlogId());
		if(isset($conditions['keyword'])) {
			$this->setQualifier('content','like',$conditions['keyword'],true);
		}
		$this->setLimit($count, $offset);
		$this->setOrder('created','desc');
		return $this->getAll();
	}

/// Methods for specific function.
	/// conditions [array(page=>value<int>, linesforpage=>value<int>)]
	public function getFormattedList($conditions) {
		//data [array(id, blogid, category, content, 
		$data = $this->getWithConditions($conditions);
		$view = '';
		foreach($data as $d) {
			$template = $conditions['template'];
			$d['created'] = Timestamp::getHumanReadable($d['created']);
			foreach($conditions['dress'] as $tag => $match) {
				dress($tag, $d[$match],$template);
			}
			$view .= $template;
		}
		return $view;
	}

/// Private members	
	private function validate() {
		if(is_null($this->id)) $this->id = $this->getNextId();
		$this->category = UTF8::lessenAsByte($this->category, 11);
		$this->content = UTF8::lessenAsByte($this->content, 512);
		if(!Validator::isInteger($this->blogid, 1)) return $this->error('blogid');		
		if(!Validator::timestamp($this->created)) return $this->error('created');
		return true;
	}
	
	private function getNextId() {
		global $database;
		$maxId = $this->getCell('MAX(id)');
		if(!empty($maxId)) return $maxId + 1;
		else return 1;
	}
	public function showResult($result) {
		echo "<html><head></head><body>";
		echo '<script type="text/javascript">alert("';
		if($result) {
			echo _t('Line이 추가되었습니다.');
		} else {
			echo _t('Line 추가에 실패했습니다.');	
		}
		echo '");history.back(-1);</script></body></html>';
	}
	private function error($state) {
		$this->_error['message'] = $state;
		return false;
	}
}
?>