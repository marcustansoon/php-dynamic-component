<?php

class Interactive
{
	private $hydratedVariables;
	private $modifiedArr;

	public function __construct()
	{
		
	}

	/**
	 * Get class public variables
	 * 
	 * @return array
	 */
	private function getPublicVariables() : array
	{
		return array_diff_key(get_object_vars($this), array_flip(['hydratedVariables', 'modifiedArr']));
	}

	/**
	 * Return the resultant modified variables
	 * 
	 * @param  array  $hydrateParameters - Parameters taken from POST request (client side)
	 * @return array                   
	 */
	private function getHydratedVariables(array $hydrateParameters) : array
	{
		// Get class public variables
		$publicVariables = $this->getPublicVariables();
		// Filter out unnecessary array keys
		$intersectedArr = array_intersect_key($publicVariables, $hydrateParameters);
		// Get array keys which are modified
		$modifiedKeysArr = array_keys(array_diff($intersectedArr, $hydrateParameters));
		// Get associative array where keys are modified
		$this->modifiedArr = array_intersect_key($hydrateParameters, array_flip($modifiedKeysArr));
		
		$this->invokeCallbacks('updating');

		// Merge and return the modified array
		return array_merge($publicVariables, $this->modifiedArr);
	}

	private function invokeCallbacks(string $type)
	{
		$classMethods = get_class_methods($this);

		foreach ($this->modifiedArr as $key => $value) {
			in_array($type.ucwords($key), $classMethods) && $this->{$type.ucwords($key)}($value);
		}
	}

	private function getClassName() : string
	{
		return get_class($this);
	}

	public function renderHTML(array $hydrateParameters = []) : string
	{
		$this->hydratedVariables = $this->getHydratedVariables($hydrateParameters);

		// Make hydrated variables accessible as local scope
		foreach ($this->hydratedVariables as $key => $value) {
			${$key} = $value;	
			$this->{$key} = $value;	
		}
		
		$this->invokeCallbacks('updated');

		// Turn on output buffering
		ob_start();
		// Load component content
		require $this->getRenderFilePath();
		// Store buffer output
		$output = ob_get_contents();
		// Turn off output buffering
		ob_end_clean();

		// Modify HTML code and append custom attribute 'd-class'
		$doc = new DOMDocument();
		$doc->preserveWhitespace = false;
		$doc->formatOutput = true;
		$doc->loadHTML($output, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		$doc->getElementsByTagName('div')->item(0)->setAttribute('d-class', $this->getClassName());

		// Source:- https://stackoverflow.com/questions/25486687/remove-whitespaces-and-line-breaks-from-captured-data-with-php-dom-document
		return preg_replace(['(\s+)u', '(^\s|\s$)u'], [' ', ''], $doc->saveHTML());
	}

	public function renderJSON(array $hydrateVariables = []) : array
	{
		return [
			'html' => $this->renderHTML($hydrateVariables),
			'variables' => $this->hydratedVariables,
		];
	}
} 