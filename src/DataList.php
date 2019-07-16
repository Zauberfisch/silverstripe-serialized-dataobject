<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject;

use SilverStripe\ORM\DataObject;

class DataList extends AbstractList {
	protected function serializeItems() {
		$items = [];
		foreach ($this->toArray() as $item) {
			$items[] = [$item->ClassName, $item->ID];
		}
		return $items;
	}

	protected function deserializeItems(array $items) {
		$map = [];
		foreach ($items as $item) {
			$className = $item[0];
			$baseClass = DataObject::getSchema()->baseDataClass($className);
			$id = $item[1];
			if (!isset($map[$baseClass])) {
				$map[$baseClass] = [];
			}
			$map[$baseClass][] = $id;
		}
		$_items = [];
		foreach ($map as $baseClass => $ids) {
			foreach ($baseClass::get()->byIDs($ids) as $item) {
				$_items[] = $item;
			}
		}
		return $_items;
	}

	public function validateRecord($item) {
		return is_a($item, DataObject::class);
	}
}
