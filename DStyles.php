<?php
/*
	DStyles Simple Template Engine
	Author:		Donator
	Email:		maciejkudas@gmail.com
	Version:	v2.3
	
	Fell free to use and don't forget to tell about fond bugs or share with Your ideas.
	Leave this comment and consider telling about DStyles Your friends :)
*/
define('DSTYLES_DEFAULT_DIR', './');
define('DSTYLES_DEFAULT_EXT', '.html');
define('DSTYLES_DEFAULT_STYLE', '');

class DStyles {
	// ---[ Variables ]---
	protected $ext;
	protected $name;
	protected $path;
	protected $folder;
	
	// ---[ Magic methods ]---
	function __construct($folder = DSTYLES_DEFAULT_DIR, $name = DSTYLES_DEFAULT_STYLE, $ext = DSTYLES_DEFAULT_EXT) {
		// Save needed data
		$this->ext    = $ext;
		$this->name   = self::fixPath($name);
		$this->folder = self::fixPath($folder);
		$this->path   = $this->folder . $this->name;
	}
	
	function __destruct() {
		foreach ($this as $k => $v) {
			unset($this->$k);
		}
		
		unset($this);
	}
	
	// ---[ Public ]---
	// Allows to change home folder
	function setHome($path) {
		$this->folder = self::fixPath($path);
		$this->path   = $this->folder . $this->name;
	}
	
	// Allows to change current style
	function setStyle($name) {
		$this->name = self::fixPath($name);
		$this->path = $this->folder . $this->name;
	}
	
	// Allows to change default extension
	function setExt($ext) {
		/*if (substr($ext, 0, 1) != '.')
		$ext = '.' . $ext;*/
		
		$this->ext = $ext;
	}
	
	// Creates object with template
	function get($file, $ext = false) {
		// Get template file
		$element = new DStylesElement($this->path, $file, ($ext !== false) ? $ext : $this->ext);
		
		// Send some "predefined" constants
		$element->set(array(
			'NAME' => $this->name,
			'HOME' => $this->folder,
			'PATH' => $this->path
		));
		
		// Return template object element
		return $element;
	}
	
	// Just predefined use of get('header')->set(array $data)
	function header($data = '') {
		$t = self::get('header');
		
		if (!empty($data) && is_array($data))
			$t->set($data);
		
		return $t->set(array(
			'NAME' => $this->name,
			'HOME' => $this->folder,
			'PATH' => $this->path
		));
	}
	
	// Just another predefined use of get('footer')->set(array $data)
	function footer($data = '') {
		$t = self::get('footer');
		
		if (!empty($data) && is_array($data))
			$t->set($data);
		
		return $t->set(array(
			'NAME' => $this->name,
			'HOME' => $this->folder,
			'PATH' => $this->path
		));
	}
	
	// ---[ Private ]---
	// Fixes path's slashes
	private function fixPath($path) {
		// Check if $path is empty
		if (empty($path)) {
			return '';
		}
		
		// Turn slashes into backslashes
		$path = str_replace('\\', '/', $path);
		
		// Remove repeating backslashes
		$path = preg_replace('/(\/{2,})/sim', '/', $path);
		
		// Search for backslash at the end
		if (substr($path, -1) != '/')
			$path .= '/';
		
		// Return fixed path
		return $path;
	}
}

class DStylesElement extends DStyles {
	// ---[ Variables ]---
	private $tmp = '';
	private $file = '';
	private $data = array();
	private $split = false;
	private $fullpath = false;
	private $triggers = array();
	
	// ---[ Magic methods ]---
	function __construct($path, $file, $ext) {
		$this->file     = $file;
		$this->fullpath = $path . $file . $ext;
		
		if (!file_exists($this->fullpath)) {
			$this->fullpath = false;
			trigger_error("File '$file$ext' does not exists", E_USER_WARNING);
		}
	}
	
	function __destruct() {
		foreach ($this as $k => $v) {
			unset($this->$k);
		}
		
		unset($this);
	}
	
	// ---[ Public ]---
	// Register new conditional trigger
	function registerTrigger($name) {
		if (is_string($name) && !array_key_exists($name, $this->triggers)) {
			$this->triggers[$name] = false;
		} elseif (is_array($name)) {
			foreach ($name as $v) {
				self::registerTrigger($v);
			}
		}
		
		// Return self
		return $this;
	}
	
	// Sets trigger state [in other words: Trigger (or not) a trigger :)]
	function trigger($name, $state = true) {
		if (is_string($name)) {
			if (array_key_exists($name, $this->triggers)) {
				$this->triggers[$name] = (bool) $state;
			}
		} elseif (is_array($name)) {
			foreach ($name as $k => $v) {
				if (is_string($v)) {
					self::trigger($v, $state);
				} elseif (is_bool($v)) {
					self::trigger($k, $v);
				}
			}
		}
		
		// Return self
		return $this;
	}
	
	
	// Allows to set value to a constant
	function set($data, $value = '') {
		if (empty($data)) {
			trigger_error('No data found', E_USER_NOTICE);
		} elseif (!is_array($data)) {
			$this->data[(string) $data] = (string) $value;
		} elseif (is_array($data)) {
			foreach ($data as $k => $v) {
				$this->data[$k] = $v;
			}
		} else {
			trigger_error('Unsupported type was passed', E_USER_NOTICE);
		}
		
		// Return self
		return $this;
	}
	
	// Sets splitter
	function split($by) {
		if (!empty($by))
			$this->split = (string) $by;
		
		// Return self
		return $this;
	}
	
	// Generates template and saves it inside the object
	function create() {
		if (!$this->fullpath) {
			return false;
		}
		
		if ($o = fopen($this->fullpath, 'rb')) {
			$t = filesize($this->fullpath) ? fread($o, filesize($this->fullpath)) : '';
			fclose($o);
		} else {
			return ((bool) $this->split) ? array() : '';
		}
		
		if (!empty($this->triggers)) {
			foreach ($this->triggers as $k => $v) {
				// Just for debugging
				/*if(preg_match_all("/<!-- !$k -->(.*?)<!-- !$k -->/sim", $t, $matches, PREG_SET_ORDER)){
				foreach($matches as $val){
				$t = str_replace($val[0], (!$v) ? $val[1] : '', $t);
				}
				}
				
				if(preg_match_all("/<!-- $k -->(.*?)<!-- $k -->/sim", $t, $matches, PREG_SET_ORDER)){
				foreach($matches as $val){
				$t = str_replace($val[0], ($v) ? $val[1] : '', $t);
				}
				}*/
				
				if (!$v) {
					$t = preg_replace("/<!-- !$k -->(.*?)<!-- !$k -->/sim", '\\1', $t);
					$t = preg_replace("/<!-- $k -->(.*?)<!-- $k -->/sim", '', $t);
				} else {
					$t = preg_replace("/<!-- !$k -->(.*?)<!-- !$k -->/sim", '', $t);
					$t = preg_replace("/<!-- $k -->(.*?)<!-- $k -->/sim", '\\1', $t);
				}
			}
		}
		
		if (!empty($this->data)) {
			foreach ($this->data as $k => $v) {
				$t = str_replace('{' . $k . '}', $v, $t);
			}
		}
		
		if ($this->split)
			$t = explode('{' . $this->split . '}', $t);
		
		// Save generated content
		$this->tmp = $t;
		
		// And return it
		return $t;
	}
	
	// Displays generated content
	function result($idx = -1) {
		if ((bool) $this->split) {
			$t = ((bool) $this->tmp && $idx >= 0) ? (array) $this->tmp : self::create();
			
			if (!$t) {
				$t = array();
			}
			
			echo ($idx >= 0 && array_key_exists($idx, $t)) ? $t[$idx] : implode($t);
		} else {
			echo self::create();
		}
	}
}
?>