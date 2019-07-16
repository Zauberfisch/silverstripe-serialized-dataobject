<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Form;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FormField;

class FormExtension extends Extension {
	private static $allowed_actions = [
		'handleFieldArrayList',
	];
//	private static $url_handlers = [
//		'field/$FieldName!' => 'handleFieldArrayList',
//	];


	/**
	 * @param HTTPRequest $request
	 * @return FormField
	 */
	public function handleFieldArrayList($request) {
		$field = $this->owner->handleField($request);
		if (!$field) {
			$fieldName = $request->param('FieldName');
			foreach ($this->owner->Fields()->dataFields() as $dataField) {
				/** @var FormField|ArrayListField $dataField */
				if ($dataField instanceof ArrayListField) {
					if (strpos($fieldName, $dataField->getName()) === 0) {
						$field = $dataField->handleSubField($fieldName);
						break;
					}
				}
			}
		}
		return $field;
	}
}
