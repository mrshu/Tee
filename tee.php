<?php
if(!defined('__DIR__'))   define('__DIR__',dirname(__FILE__));

class Tee{
	
	private $_REGEXES = 
	array( /* '/\{\%\sextends\s"(.*)"\s\%\}/e' => 'eval(return $this->load_file("\\1"));', */
		'/\{\%\sif\s(.*)\s\%\}/' => "<?php if(@$\\1): ?>",
		'/\{\%\selse\s\%\}/' => "<?php else: ?>",
		'/\{\%\sendif\s\%\}/' => "<?php endif; ?>",
		'/\{\%\sfor\s([_a-zA-Z][_a-zA-Z0-9]*)\sin\s([_a-zA-Z][_a-zA-Z0-9]*)\s\%\}/' => "<?php foreach(@$\\2 as $\\1): ?>",
		'/\{\%\sendfor\s\%\}/' => "<?php endforeach; ?>",
		'/\{\{\s*([_a-zA-Z][_a-zA-Z0-9]*)\s*\}\}/' => "<?php echo @$\\1; ?>",
		'/\{\{\s*([_a-zA-Z][_a-zA-Z0-9]*)\.([_a-zA-Z][_a-zA-Z0-9]*)\s*\}\}/' => "<?php echo @$\\1['\\2']; ?>");
	

	private static $_tags = array();
	
	private $_source = '';
	private $_filename = '';
	private $_cache_dir = '';
	
	private $_blocks = array();

	const TAG_START = '{%';
	const TAG_END   = '%}';
	
	const TAG_REGEX = '\s([_a-zA-Z][_a-zA-Z0-9]*)\s(.*)\s';
	
	public function __construct($file = '')
	{
		$this->_cache_dir = __DIR__."/cache/";
		if(!is_dir($this->_cache_dir)){
			if(!mkdir($this->_cache_dir)){
				throw new Exception("Cannot create dir ".__DIR__."/cache/");
			}
			if(!is_writable($this->_cache_dir)){
				@chmod(0644,__DIR__."/cache/");
			}

		}
		$this->file($file);
	
	}

	public function file($filename)
	{
		$this->_filename = realpath($filename);
		return ($this->_source = @file_get_contents($this->_filename)) == '' ? false : true;
	}

	public function render()
	{
		
		if($this->_filename === ''){
			throw new Exception('No template filename assgined');
		}

		if(!$this->from_cache($this->_filename)){
			$this->replace();
			$this->write_cache($this->_filename);
		}

		$vars = get_object_vars($this);
		ob_start();
		if(count($vars)){
			foreach($vars as $key => $val){
				// because O == false we had to add one char before key name
				if(strpos('-'.$key,'_') != 1){
					$$key = $val;
				}
			}
		}
		require($this->cached_file($this->_filename));
		$output = ob_get_contents();
		ob_end_clean();

		
		return $output;
	}

	private function tag_replace_callback($matches)
	{
		if(array_key_exists($matches[1],self::$_tags)){
				return "<?php echo @".
					self::$_tags[$matches[1]].
					"('".
					 addslashes($matches[2])
					."'); ?>";
		}else{
			return "";
		}
	}

	public function replace($source = NULL,$ret = false,$in_extend = false)
	{
		if($source == NULL){
			$source = $this->_source;
		}
	
		// simple replace regexes
		foreach($this->_REGEXES as $regexin => $out){
			$source = preg_replace(
					$regexin,
					$out,
					$source);
		}
		
		// extends tag
		if(preg_match('/\{\%\sextends\s"(.*)"\s\%\}/',$source,$matches)){
			$source = str_replace(
					$matches[0],
					$this->load_file($matches[1],true),
					$source);
		}
	
		// include tag
		if(preg_match_all('/\{\%\sinclude\s"(.*)"\s\%\}/',
					$source,
					$matches)){
			$source = str_replace(
					$matches[0][0],
					$this->load_file($matches[1][0]),
					$source);
		}
		

		if($i = preg_match_all("/\{\%\sblock\s(.*)\s\%\}(.*)\{\%\sendblock\s\%\}/sU",
					$source,
					$matches)){
	
			for(--$i;$i>=0;$i--){
				// just know about them
				if(isset($this->_blocks[$matches[1][$i]])){
					$source = str_replace($matches[0][$i],'',$source);
					$source = str_replace('[['.$matches[1][$i].']]',
						$matches[2][$i],
						$source);

				}else{
					$this->_blocks[$matches[1][$i]] = $matches[2][$i];	
					$source = str_replace($matches[0][$i],'[['.$matches[1][$i].']]',$source);
				}
			}
		
		}
		
		if(!$in_extend){
			$source = preg_replace('/\[\[(.*)\]\]/e','@$this->_blocks["\\1"]',$source);
		}

		// other tags
		$tag_regex = '/'.preg_quote(self::TAG_START).
			self::TAG_REGEX.
			preg_quote(self::TAG_END).'/';

		$source = preg_replace_callback($tag_regex,
				array($this,'tag_replace_callback'),
				$source);	

		if($ret){
			return $source;
		}else{
			$this->_source = $source;
		}

		return true;
	}

	public static function add_tag($name, $function)
	{
		if(!preg_match('/([_a-zA-Z][_a-zA-Z0-9]*)/',$name)){
			throw new Exception("Invalid tag name '$name' ");
		}

		if(is_callable($function,true,$real_function)){
			self::$_tags[$name] = $function /*$real_function*/;
			return true;
		}else{
			throw new Exception("Uncallable function '$function' ");
		}
	}

	public function load_file($filename, $in_extend = false)
	{
		$dir = dirname($this->_filename);
		$source = @file_get_contents($dir."/".$filename);
		return $this->replace($source,true,$in_extend);
	}

	public function clean_cache()
	{
		$files = scandir($this->_cache_dir); 
		foreach ($files as $file) { 
			if ($file != "." && $file != "..") { 
				unlink($this->_cache_dir."/".$file);  
			} 
		}
	}


	private function cached_file($file)
	{
		return $this->_cache_dir.str_replace("/","%",$file);
	}

	private function from_cache($file)
	{
		$cached_file = $this->cached_file($file); 		
		// TODO: included file edited
		return (file_exists($cached_file) && 
				(@filemtime($this->_filename) <
				 @filemtime($this->cached_file($this->_filename))
				)
		       );
	}
	private function write_cache($file)
	{
		$cached_file = $this->cached_file($file);
		return @file_put_contents($cached_file,$this->_source);	
	}
}

/*
   $t = new Tee();

   $a = $t->file(dirname(__FILE__)."/tests/input_files/variables.phtml");
   $t->strong = "asd";
   echo $t->render();
 */
?>
