<?php

/**
 * Class SerializedDataObject
 *
 * @author Zauberfisch
 * @method static Config_ForClass|stdClass config
 */
abstract class SerializedDataObject extends ViewableData implements Serializable, JsonSerializable, i18nEntityProvider {
	private static $fields = [];
	private static $lists = [];
	protected $fieldsData = [];
	protected $listsData = [];

	public function jsonSerialize() {
		return array_merge([
			'class' => $this->class,
			'fieldsData' => $this->fieldsData,
			'listsData' => $this->listsData,
		]);
	}

	public function serialize() {
		return serialize([
			'fieldsData' => $this->fieldsData,
			'listsData' => $this->listsData,
		]);
	}

	public function unserialize($serialized) {
		$data = unserialize($serialized);
		$this->class = get_class($this);
		$this->fieldsData = isset($data['fieldsData']) ? $data['fieldsData'] : [];
		$this->listsData = isset($data['listsData']) ? $data['listsData'] : [];
	}

	public function __get($fieldName) {
		return $this->getField($fieldName);
	}

	public function __set($fieldName, $value) {
		return $this->setField($fieldName, $value);
	}

//	public function __call($method, $arguments) {
//		if ($this->hasList($method)) {
//			return $this->getList($method);
//		}
//		//	if (strlen($method) > 3) {
//		//		list($prefix, $fieldName) = str_split($method, 3);
//		//		if ($this->hasField($fieldName)) {
//		//			return $this->{$prefix . "Field"}($arguments);
//		//		}
//		//	}
//		return parent::__call($method, $arguments);
//	}

	public function defineMethods() {
		parent::defineMethods();
		// TODO how to handle method name collisions?
		foreach (static::config()->fields as $field) {
			$this->createMethod("set$field", sprintf('return $obj->setField("%s", $args[0]);', $field));
			$this->createMethod("get$field", sprintf('return $obj->getField("%s");', $field));
			//$this->addWrapperMethod("set$field", 'setField');
			//$this->addWrapperMethod("get$field", 'getField');
		}
		foreach (static::config()->lists as $field) {
			$this->createMethod("set$field", sprintf('return $obj->setList("%s", $args[0]);', $field));
			$this->createMethod("get$field", sprintf('return $obj->getList("%s");', $field));
			//$this->addWrapperMethod("set$field", 'setField');
			//$this->addWrapperMethod("get$field", 'getField');
		}
	}

	public function hasField($name) {
		return in_array($name, static::config()->fields);
	}

	public function getField($name) {
		if ($this->hasField($name)) {
			if (isset($this->fieldsData[$name])) {
				return $this->fieldsData[$name];
			}
			return null;
		}
		throw new Exception("Could not find field '$name'.");
	}

	public function setField($name, $value) {
		if ($this->hasField($name)) {
			$this->fieldsData[$name] = $value;
			return $this;
		}
		throw new Exception("Could not find field '$name'.");
	}

	public function hasList($name) {
		return in_array($name, static::config()->lists);
	}

	public function getList($name) {
		if ($this->hasList($name)) {
			if (!isset($this->listsData[$name])) {
				$this->listsData[$name] = new SerializedDataList();
			}
			return $this->listsData[$name];
		}
		throw new Exception("Could not find field '$name'.");
	}

	public function setList($name, SerializedDataList $value) {
		if ($this->hasList($name)) {
			$this->listsData[$name] = $value;
			return $this;
		}
		throw new Exception("Could not find field '$name'.");
	}

	/**
	 * @param array $data
	 * @return $this
	 * @throws Exception
	 */
	public function update($data) {
		foreach (array_merge(static::config()->lists, static::config()->fields) as $name) {
			if (isset($data[$name])) {
				$value = $data[$name];
				if (is_a($value, 'SerializedDataList')) {
					if ($this->hasList($name)) {
						$this->setList($name, $value);
					}
				} elseif ($this->hasField($name)) {
					$this->setField($name, $value);
				}
			}
		}
		return $this;
	}

	private static $_cache_field_labels = [];

	protected function i18nFields() {
		$fields = [];
		$ancestry = array_reverse(ClassInfo::ancestry($this->class));
		if ($ancestry) {
			foreach ($ancestry as $ancestorClass) {
				if ($ancestorClass == __CLASS__) {
					break;
				}
				$fields[$ancestorClass] = [];
				foreach ([
							 'field' => (array)Config::inst()->get($ancestorClass, 'field', Config::UNINHERITED),
							 'list' => (array)Config::inst()->get($ancestorClass, 'list', Config::UNINHERITED),
						 ] as $type => $attrs) {
					$fields[$ancestorClass][$type] = [];
					foreach ($attrs as $name => $spec) {
						$fields[$ancestorClass][$type][$name] = FormField::name_to_label($name);
					}
				}
			}
		}
		return $fields;
	}

	public function fieldLabels() {
		$cacheKey = $this->class;
		if (!isset(self::$_cache_field_labels[$cacheKey])) {
			$labels = [];
			foreach ($this->i18nFields() as $className => $types) {
				foreach ($types as $type => $defaultLabels) {
					foreach ($defaultLabels as $name => $defaultValue) {
						$labels["{$type}_$name"] = _t("$className.{$type}_$name", $defaultValue);
						if (!isset($labels[$name])) {
							$labels[$name] = $labels["{$type}_$name"];
						}
					}
				}
			}
			self::$_cache_field_labels[$cacheKey] = $labels;
		}
		return self::$_cache_field_labels[$cacheKey];
	}

	public function fieldLabel($name) {
		$labels = $this->fieldLabels();
		return (isset($labels[$name])) ? $labels[$name] : FormField::name_to_label($name);
	}

	public function provideI18nEntities() {
		$entities = [];
		foreach ($this->i18nFields() as $className => $types) {
			foreach ($types as $type => $defaultLabels) {
				foreach ($defaultLabels as $name => $defaultValue) {
					$entities["$className.{$type}_$name"] = $defaultValue;
				}
			}
		}
		return $entities;
	}

	public function __toString() {
		return serialize($this);
	}
}
