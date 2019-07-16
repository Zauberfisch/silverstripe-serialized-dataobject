<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Form;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\DatalessField;
use SilverStripe\Forms\FormField;

class ProxyArrayListField_FieldProxy extends DatalessField {
	private static $allowed_actions = [
		'handleField',
	];
	private static $url_handlers = [
		'' => 'handleField',
	];
	protected $originalField;
	
	/**
	 * @param FormField $originalField
	 */
	public function __construct($originalField) {
		$this->originalField = $originalField;
		parent::__construct($originalField->getName());
	}
	
	
	public function handleField(HTTPRequest $request) {
		return $this->originalField;
	}
	
	public function Field($properties = []) {
		return '';
	}
	
	public function FieldHolder($properties = []) {
		return '';
	}
	
	public function SmallFieldHolder($properties = []) {
		return '';
	}
}
