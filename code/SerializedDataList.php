<?php

/**
 * Class SerializedDataList
 *
 * @author Zauberfisch
 */
class SerializedDataList extends ArrayList implements Serializable, JsonSerializable {
	public function jsonSerialize() {
		return [
			'class' => $this->class,
			'items' => $this->toArray(),
		];
	}
	
	public function serialize() {
		return serialize($this->toArray());
	}
	
	public function unserialize($serialized) {
		$this->items = unserialize($serialized);
	}
	
	public function __toString() {
		return "'" . $this->serialize() . "'";
	}
	
	public function __construct(array $items = []) {
		array_walk($items, function ($item) {
			if (!is_a($item, 'SerializedDataObject')) {
				throw new InvalidArgumentException();
			}
		});
		parent::__construct($items);
	}
	
	public function push($item) {
		if (!is_a($item, 'SerializedDataObject')) {
			throw new InvalidArgumentException();
		}
		parent::push($item);
	}
	
	public function add($item) {
		$this->push($item);
	}
	
	public function remove($item) {
		if (!is_a($item, 'SerializedDataObject')) {
			throw new InvalidArgumentException();
		}
		parent::remove($item);
	}
	
	public function replace($item, $with) {
		if (!is_a($item, 'SerializedDataObject') || !is_a($with, 'SerializedDataObject')) {
			throw new InvalidArgumentException();
		}
		parent::replace($item, $with);
	}
	
	public function merge($item) {
		if (!is_a($item, 'SerializedDataObject')) {
			throw new InvalidArgumentException();
		}
		parent::merge($item);
	}
	
	public function unshift($item) {
		if (!is_a($item, 'SerializedDataObject')) {
			throw new InvalidArgumentException();
		}
		parent::unshift($item);
	}
}
