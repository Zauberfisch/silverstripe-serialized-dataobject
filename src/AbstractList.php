<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject;

use zauberfisch\SerializedDataObject\Serialize\JsonSerializable;
use zauberfisch\SerializedDataObject\Serialize\JsonSerializer;
use zauberfisch\SerializedDataObject\Serialize\Serializer;

require_once 'Serialize/JsonSerializer.php';

/**
 * @author Zauberfisch
 */
abstract class AbstractList extends \ArrayList implements \Serializable, JsonSerializable {
	use JsonSerializer {
		jsonSerialize as jsonSerializeTrait;
	}

	public function jsonSerialize() {
		return array_merge([
			'items' => $this->serializeItems(),
		], $this->jsonSerializeTrait());
	}

	public function jsonDeserialize(array $data = null) {
		$this->items = isset($data['items']) && is_array($data['items']) ? $this->deserializeItems($data['items']) : [];
	}

	/**
	 * @deprecated 4.0 Support for php serialisation will be removed in Version 4.0
	 * @return string
	 */
	public function serialize() {
		return serialize($this->serializeItems());
	}

	/**
	 * @deprecated 4.0 Support for php serialisation will be removed in Version 4.0
	 * @param string $serialized
	 */
	public function unserialize($serialized) {
		$this->items = $this->deserializeItems(unserialize($serialized));
	}

	/**
	 * @return array
	 */
	protected function serializeItems() {
		return $this->items;
	}

	/**
	 * @param array $items
	 * @return array
	 */
	protected function deserializeItems(array $items) {
		return $items;
	}

	public function __toString() {
		return Serializer::serialize($this);
	}

	public function __construct(array $items = []) {
		foreach ($items as $item) {
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
