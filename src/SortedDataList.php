<?php

namespace zauberfisch\SerializedDataObject;

class SortedDataList extends DataList {
	public function unserialize($serialized) {
		$items = unserialize($serialized);
		$this->items = [];
		foreach ($items as $item) {
			$className = $item[0];
			$id = $item[1];
			$this->items[] = $className::get()->byID($id);
		}
	}
}