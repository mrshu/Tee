<?php
class Tee{
	
	private $_REGEXES = 
	array('/\{\%\sinclude\s"(.*)"\s\%\}/e' => 'eval(\'return $this->load_file("\\1");\');',
		'/\{\{\s*([_a-zA-Z][_a-zA-Z0-9]*)\s*\}\}/' => "<?php echo @$\\1; ?>",
		'/\{\{\s*([_a-zA-Z][_a-zA-Z0-9]*)\.([_a-zA-Z][_a-zA-Z0-9]*)\s*\}\}/' => "<?php echo @$\\1['\\2']; ?>");
	
	private $_source = '';
	private $_filename = '';
	private $_cache_dir = '';

	const TAG_START = '{{';
	const TAG_END   = '}}';
	
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
		$this->_filename = $file;
	
	}

	public function file($filename)
	{
		$this->_filename = realpath($filename);
		return ($this->_source = @file_get_contents($this->_filename)) == '' ? false : true;
	}

	public function render()
	{
			
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

	public function replace($source = NULL,$ret = false)
	{
		if($source == NULL){
			$source = $this->_source;
		}

		foreach($this->_REGEXES as $regexin => $out){
			$source = preg_replace(
					$regexin,
					$out,
					$source);
		}
		
		if($ret){
			return $source;
		}else{
			$this->_source = $source;
		}

		return true;
	}
	
	public function load_file($filename)
	{
		$dir = dirname($this->_filename);
		$source = @file_get_contents($dir."/".$filename);
		return $this->replace($source,true);
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
