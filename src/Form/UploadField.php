<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Form;

use SS_HTTPRequest;
use zauberfisch\SerializedDataObject\DBField\DataListField;
use zauberfisch\SerializedDataObject\Serialize\Serializer;

/**
 * @author Zauberfisch
 */
class UploadField extends \UploadField {
	/**
	 * @param \DataObjectInterface|\DataObject $record
	 * @return $this
	 */
	public function saveInto(\DataObjectInterface $record) {
		$fieldName = $this->getName();
		if (!$fieldName) {
			return $this;
		}
		if ($record->hasField($fieldName)) {
			/** @var DataListField $dbValue */
			$dbValue = $record->obj($fieldName);
			$list = $dbValue->nullValue();
			foreach ($this->getItems() as $item) {
				$list->add($item);
			}
			$dbValue->setValue($list, null, true);
			$record->setField($fieldName, $dbValue);
		} else {
			parent::saveInto($record);
		}
		return $this;
	}

	/**
	 * @param array $value
	 * @param null $record
	 * @return \UploadField
	 * @throws \ValidationException
	 */
	public function setValue($value, $record = null) {
		if (is_string($value) && $value) {
			$value = Serializer::deserialize($value);
			return parent::setValue(null, $value);
		}
		return parent::setValue($value, $record);
	}

	private static $allowed_actions = [
		'upload',
	];

	public function upload(SS_HTTPRequest $request) {
		$fieldName = $this->getName();
		$baseStrLength = strpos($fieldName, '[');
		if ($baseStrLength) {
			$postVars = $request->postVars();
			$baseName = substr($fieldName, 0, $baseStrLength);
			if (isset($postVars[$baseName])) {
				$postVars[$fieldName] = $this->flattenFilesArray($postVars[$baseName]);
				$request = new SS_HTTPRequest(
					$request->httpMethod(),
					$request->getURL(),
					$request->getVars(),
					$postVars
				);
			}
		}
		return parent::upload($request);
	}

	protected function flattenFilesArray($array) {
		$fieldName = $this->getName();
		$keys = substr($fieldName, strpos($fieldName, '['));
		$keys = trim($keys, '[');
		$keys = trim($keys, ']');
		$keys = explode('][', $keys);
		$return = [];
		foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $field) {
			$data = $array[$field];
			foreach ($keys as $key) {
				$data = $data[$key];
			}
			$return[$field] = $data;
		}
		return $return;
	}
}
