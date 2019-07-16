<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Form;

use SilverStripe\Assets\File;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\Requirements;

if (class_exists('\SilverStripe\AssetAdmin\Forms\UploadField')) {
	/**
	 * @author Zauberfisch
	 */
	class SortableUploadField extends UploadField {
		public function Field($properties = []) {
			Requirements::javascript('zauberfisch/silverstripe-serialized-dataobject:javascript/SortableUploadField.js');
			Requirements::css('zauberfisch/silverstripe-serialized-dataobject:css/SortableUploadField.scss.css');
			return parent::Field($properties);
		}
		
		public function setValue($value, $record = null) {
			if (!empty($value['Files'])) {
				// preserve sorting
				$list = [];
				foreach ($value['Files'] as $id) {
					$file = File::get()->byID($id);
					if ($file && $file->exists()) {
						$list[] = $file;
					}
				}
				return parent::setValue(null, new ArrayList($list));
			}
			return parent::setValue($value, $record);
		}
	}
}
