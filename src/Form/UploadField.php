<?php

namespace zauberfisch\SerializedDataObject\Form;

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
}
