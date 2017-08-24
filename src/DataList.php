<?php

namespace zauberfisch\SerializedDataObject;

class DataList extends AbstractList {
	protected function convertToIDList() {
		$items = [];
		foreach ($this->toArray() as $item) {
			$items[] = [$item->ClassName, $item->ID];
		}
		return $items;
	}
	
	public function jsonSerialize() {
		return [
			'class' => $this->class,
			'items' => $this->convertToIDList(),
		];
	}
	
	public function serialize() {
		return serialize($this->convertToIDList());
	}
	
	public function unserialize($serialized) {
		$items = unserialize($serialized);
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
		$this->items = [];
		foreach ($map as $baseClass => $ids) {
			foreach ($baseClass::get()->byIDs($ids) as $item) {
				$this->items[] = $item;
			}
		}
	}
	
	public function validateRecord($item) {
		return is_a($item, \DataObject::class);
	}
}
