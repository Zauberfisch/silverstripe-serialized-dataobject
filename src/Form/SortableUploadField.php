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
}
