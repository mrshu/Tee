<?php
require_once('simpletest/autorun.php');

require_once('../tee.php');

class SimpleTemplateTest extends UnitTestCase{
	function __construct() 
	{
	        parent::__construct('Log test');
	}


	function testVariableReplacement()
	{
		$tee = new Tee();
		$tee->clean_cache();
		$tee->file(__DIR__.'/input_files/variables.phtml');
		$tee->strong = 'This should be bold';
		$output = file_get_contents(__DIR__.'/output_files/variables.html');
		$this->assertEqual($tee->render(),$output);
			
	}
	
	function testBadVariableReplacement()
	{
		$tee = new Tee();
		$tee->clean_cache();
		$tee->file(__DIR__.'/input_files/bad_variables.phtml');
		$tee->strong = 'This should be bold';
		$output = file_get_contents(__DIR__.'/output_files/bad_variables.html');
		$this->assertEqual($tee->render(),$output);
		
	}

	function testArrayReplacement()
	{
		$tee = new Tee();
		$tee->clean_cache();
		$tee->file(__DIR__.'/input_files/array_replacement.phtml');
		$tee->person = array('name' => 'Tester', 'age' => 20);
		$output = file_get_contents(__DIR__.'/output_files/array_replacement.html');
		$this->assertEqual($tee->render(),$output);
	}
	

}



