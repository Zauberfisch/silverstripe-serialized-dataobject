<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Form;

use SilverStripe\Forms\CompositeField;

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
