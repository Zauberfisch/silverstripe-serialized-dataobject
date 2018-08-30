<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject;

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
			$baseClass = \ClassInfo::baseDataClass($className);
			$id = $item[1];
			if (!isset($map[$baseClass])) {
				$map[$baseClass] = [];
			}
			$map[$baseClass][] = $id;
		}
		$items = [];
		foreach ($map as $baseClass => $ids) {
			foreach ($baseClass::get()->byIDs($ids) as $item) {
				$items[] = $item;
			}
		}
		return $items;
	}

	public function validateRecord($item) {
		return is_a($item, \DataObject::class);
	}
}
