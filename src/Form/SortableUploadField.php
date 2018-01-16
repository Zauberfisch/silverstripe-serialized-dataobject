<?php

namespace zauberfisch\SerializedDataObject\Form;

/**
 * @author Zauberfisch
 */
class SortableUploadField extends UploadField {
	public function Field($properties = []) {
		\Requirements::javascript(SERIALIZED_DATAOBJECT_DIR . '/javascript/SortableUploadField.js');
		//\Requirements::css(SERIALIZED_DATAOBJECT_DIR . '/scss/SortableUploadField.scss');
		\Requirements::css(SERIALIZED_DATAOBJECT_DIR . '/css/SortableUploadField.scss.css');
		return parent::Field($properties);
	}

	/**
	 * @param array $value
	 * @param null $record
	 * @return \UploadField
	 * @throws \ValidationException
	 */
	public function setValue($value, $record = null) {
		if (!empty($value['Files'])) {
			// preserve sorting
			$list = [];
			foreach ($value['Files'] as $id) {
				$file = \File::get()->byID($id);
				if ($file && $file->exists()) {
					$list[] = $file;
				}
			}
			return parent::setValue(null, new \ArrayList($list));
		}
		return parent::setValue($value, $record);
	}
}
