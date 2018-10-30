<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject;

class SortedDataList extends DataList {
	protected function deserializeItems(array $items) {
		$_items = [];
		foreach ($items as $item) {
			$className = $item[0];
			$id = $item[1];
			$itemObj = $className::get()->byID($id);
			if ($itemObj && $itemObj->exists()) {
				$_items[] = $itemObj;
			}
		}
		return $_items;
	}
}