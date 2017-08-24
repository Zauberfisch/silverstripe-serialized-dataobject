<?php

namespace zauberfisch\SerializedDataObject;

/**
 * @author Zauberfisch
 */
abstract class AbstractList extends \ArrayList implements \Serializable, \JsonSerializable {
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
		foreach($items as $item) {
			if (!$this->validateRecord($item)) {
				throw new \InvalidArgumentException();
			}
		}
		parent::__construct($items);
	}
	
	public function push($item) {
		if (!$this->validateRecord($item)) {
			throw new \InvalidArgumentException();
		}
		parent::push($item);
	}
	
	public function add($item) {
		$this->push($item);
	}
	
	public function remove($item) {
		if (!$this->validateRecord($item)) {
			throw new \InvalidArgumentException();
		}
		parent::remove($item);
	}
	
	public function replace($item, $with) {
		if (!$this->validateRecord($item)) {
			throw new \InvalidArgumentException();
		}
		parent::replace($item, $with);
	}
	
	public function merge($item) {
		if (!$this->validateRecord($item)) {
			throw new \InvalidArgumentException();
		}
		parent::merge($item);
	}
	
	public function unshift($item) {
		if (!$this->validateRecord($item)) {
			throw new \InvalidArgumentException();
		}
		parent::unshift($item);
	}
	
	/**
	 * Verify if a given object is valid for this list and can be added
	 *
	 * @param $item
	 * @return bool
	 */
	public function validateRecord($item) {
		return true;
	}
}
