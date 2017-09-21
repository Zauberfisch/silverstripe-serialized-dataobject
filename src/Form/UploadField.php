<?php

namespace zauberfisch\SerializedDataObject\Form;

use SS_HTTPRequest;
use zauberfisch\SerializedDataObject\DataList;
use zauberfisch\SerializedDataObject\DBField\DataListField;

/**
 * @author Zauberfisch
 */
class UploadField extends \UploadField {
	protected function getSerializableList($ids) {
		return new DataList(\File::get()->byIDs($ids)->toArray());
	}
	
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
			$info = $record->db($fieldName);
			if ($info == DataListField::class) {
				// Get details to save
				$value = $this->getSerializableList($this->getItemIDs());
				$dbValue = new DataListField();
				$dbValue->setValue($value, null, true);
				$record->setField($fieldName, $dbValue);
			}
		} else {
			parent::saveInto($record);
		}
		return $this;
	}
	
	public function setValue($value, $record = null) {
		if (is_string($value) && $value) {
			$dbField = new DataListField();
			$dbField->setValue($value, null, true);
			$value = $dbField->getValue();
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
		$return = parent::upload($request);
		return $return;
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
