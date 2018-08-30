<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject;

class SortedDataList extends DataList {
	protected function deserializeItems(array $items) {
		$items = [];
		foreach ($items as $item) {
			$className = $item[0];
			$id = $item[1];
			$items[] = $className::get()->byID($id);
		}
		return $items;
	}
}