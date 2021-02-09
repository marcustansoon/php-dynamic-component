<?php
require "Interactive.php";

class ContactForm extends Interactive 
{
	public $lname;
	public $greeting = 'helo world';
	public $fname = 'a';
	
	public function updatedFname()
	{
		$this->fname = '123';
	}

	public function getRenderFilePath()
	{
		return __DIR__ . "/../Components/ContactForm.php";
	}
} 