<?php
require_once("simpletest/autorun.php");

require_once('../tee.php');
require_once('uDebug.php');
class TagTest extends UnitTestCase{


	public function testDefaultTagInclude()
	{
		$tee = new Tee();
		$tee->clean_cache();
		$tee->file(__DIR__."/input_files/include.phtml");
		
		$tee->strong = "Strong";
		$output = file_get_contents(__DIR__.'/output_files/include.html');
		
		$o = $tee->render();
		$this->assertEqual($o,$output);
	}


	public function testUserTags()
	{

		$tee = new Tee();
		//$tee->clean_cache();
		$tee->file(__DIR__.'/input_files/tag.phtml');
	
		function with_quotes($input)
		{
			return '"'.$input.'"';
		}
		$tee->add_tag('quote','with_quotes');
			
		$output = file_get_contents(__DIR__.'/output_files/tag.html');
		$this->assertEqual($tee->render(),$output);
	
	}

	public function testIfTag()
	{
		$tee = new Tee();
		//$tee->clean_cache();
		$tee->file(__DIR__.'/input_files/if_tag.phtml');
		
		$tee->user = "Joe";

		$output = file_get_contents(__DIR__.'/output_files/if_tag.html');
		$this->assertEqual($tee->render(),$output);
	
	
	}

	public function testIfElseTag()
	{
		$tee = new Tee();
		//$tee->clean_cache();
		$tee->file(__DIR__.'/input_files/if_else_tag.phtml');
		
		$tee->user = "Joe";

		$output = file_get_contents(__DIR__.'/output_files/if_else_tag.html');
		$o = $tee->render();
		$this->assertEqual($o, $output);

	}

	public function testIfElseElseTag()
	{
		$tee = new Tee();
		//$tee->clean_cache();
		$tee->file(__DIR__.'/input_files/if_else_tag.phtml');
		
		// $tee->user = "Joe";

		$output = file_get_contents(__DIR__.'/output_files/if_else_else_tag.html');
		$this->assertEqual($tee->render(),$output);

	}
	
	public function testIfElseArrayTag()
	{
		$tee = new Tee();
		$tee->file(__DIR__.'/input_files/if_else_array_tag.phtml');
		
		$tee->user = array('name' => 'Joe');
		
		$output = file_get_contents(__DIR__.'/output_files/if_else_array_tag.html');
		$o = $tee->render();
		uD::dump($o);
		uD::dump($output);
		$this->assertEqual($o,$output);


	}
	public function testForTag()
	{
		$tee = new Tee();
		//$tee->clean_cache();
		$tee->file(__DIR__.'/input_files/for_tag.phtml');
		
		$tee->users = array(
			array('name' => 'Joe Doe', 'email' => 'joe@doe.com'),
			array('name' => 'Linus Torvalds', 'email' => 'torvalds@cs.helsinki.fi')
		);
		
		$o = $tee->render();
		$output = file_get_contents(__DIR__.'/output_files/for_tag.html');
		$this->assertEqual($o,$output);

	}

	public function testBlockExtendsTag()
	{
		$tee = new Tee();
		//$tee->clean_cache();
		$tee->file(__DIR__.'/input_files/block_extends_tag.phtml');
		
		$tee->menu= array("Home", "Blog");
		$r = $tee->render();
		$output = file_get_contents(__DIR__.'/output_files/block_extends_tag.html');
		$this->assertEqual($r ,$output);
		
	}

} 



?>
