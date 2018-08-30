<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Form;

use zauberfisch\NamespaceTemplates\Form\CompositeField;

class ProxyArrayListField extends CompositeField {
	public function __construct($name, $title, $recordClassName) {
		$_this = $this;
		parent::__construct([
			(new ArrayListField($name, $title, $recordClassName))
				->setRecordFieldsUpdateCallback(function ($fields, $listField, $record = null) use ($_this) {
					foreach ($fields as $field) {
						$_this->push(new ProxyArrayListField_FieldProxy($field));
					}
					return $fields;
				}),
		]);
		$this->setName("{$name}_proxy_holder");
	}


}

class ProxyArrayListField_FieldProxy extends \DatalessField {
	private static $allowed_actions = [
		'handleField',
	];
	private static $url_handlers = [
		'' => 'handleField',
	];
	protected $originalField;

	/**
	 * @param \FormField $originalField
	 */
	public function __construct($originalField) {
		$this->originalField = $originalField;
		parent::__construct($originalField->getName());
	}


	public function handleField(\SS_HTTPRequest $request) {
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
