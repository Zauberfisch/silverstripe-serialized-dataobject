<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject;

require_once 'Serialize/JsonSerializer.php';

use zauberfisch\NamespaceTemplates\Form\FormField;
use zauberfisch\SerializedDataObject\Serialize\JsonSerializable;
use zauberfisch\SerializedDataObject\Serialize\JsonSerializer;
use zauberfisch\SerializedDataObject\Serialize\Serializer;

/**
 * @author Zauberfisch
 * @method static \Config_ForClass|\stdClass config
 */
abstract class AbstractDataObject extends \ViewableData implements \Serializable, JsonSerializable, \i18nEntityProvider {
	private static $fields = [];
	private static $lists = [];
	protected $fieldsData = [];
	protected $listsData = [];

	use JsonSerializer {
		jsonSerialize as jsonSerializeTrait;
	}

	public function jsonSerialize() {
		return array_merge([
			'fieldsData' => $this->fieldsData,
			'listsData' => $this->listsData,
		], $this->jsonSerializeTrait());
	}

	public function jsonDeserialize(array $data = null) {
		$this->fieldsData = isset($data['fieldsData']) ? $data['fieldsData'] : [];
		$this->listsData = isset($data['listsData']) ? $data['listsData'] : [];
	}

	/**
	 * @deprecated 4.0 Support for php serialisation will be removed in Version 4.0
	 * @return string
	 */
	public function serialize() {
		return serialize([
			'fieldsData' => $this->fieldsData,
			'listsData' => $this->listsData,
		]);
	}

	/**
	 * @deprecated 4.0 Support for php serialisation will be removed in Version 4.0
	 * @param string $serialized
	 */
	public function unserialize($serialized) {
		$data = unserialize($serialized);
		$this->class = get_class($this);
		$this->fieldsData = isset($data['fieldsData']) ? $data['fieldsData'] : [];
		$this->listsData = isset($data['listsData']) ? $data['listsData'] : [];
		foreach (\ClassInfo::ancestry(get_called_class()) as $class) {
			if (in_array($class, \Config::inst()->get(\Object::class, 'unextendable_classes'))) {
				continue;
			}
			$extensions = \Config::inst()->get($class, 'extensions',
				\Config::UNINHERITED | \Config::EXCLUDE_EXTRA_SOURCES);

			if ($extensions) foreach ($extensions as $extension) {
				$instance = self::create_from_string($extension);
				$instance->setOwner(null, $class);
				$this->extension_instances[$instance->class] = $instance;
			}
		}
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
		throw new \Exception("Could not find field '$name'.");
	}

	public function setField($name, $value) {
		if ($this->hasField($name)) {
			$this->fieldsData[$name] = $value;
			return $this;
		}
		throw new \Exception("Could not find field '$name'.");
	}

	public function hasList($name) {
		return in_array($name, static::config()->lists);
	}

	public function getList($name) {
		if ($this->hasList($name)) {
			if (!isset($this->listsData[$name])) {
				// TODO separate ArrayList and DataList
				$this->listsData[$name] = new ArrayList();
			}
			return $this->listsData[$name];
		}
		throw new \Exception("Could not find field '$name'.");
	}

	public function setList($name, AbstractList $value) {
		if ($this->hasList($name)) {
			$this->listsData[$name] = $value;
			return $this;
		}
		throw new \Exception("Could not find field '$name'.");
	}

	/**
	 * @param array $data
	 * @return $this
	 * @throws \Exception
	 */
	public function update($data) {
		foreach (array_merge(static::config()->lists, static::config()->fields) as $name) {
			if (isset($data[$name])) {
				$value = $data[$name];
				if (is_a($value, ArrayList::class)) {
					if ($this->hasList($name)) {
						$this->setList($name, $value);
					}
				} else if ($this->hasField($name)) {
					$this->setField($name, $value);
				}
			}
		}
		return $this;
	}

	private static $_cache_field_labels = [];

	protected function i18nFields() {
		$fields = [];
		$ancestry = array_reverse(\ClassInfo::ancestry($this->class));
		if ($ancestry) {
			foreach ($ancestry as $ancestorClass) {
				if ($ancestorClass == __CLASS__) {
					break;
				}
				$fields[$ancestorClass] = [];
				foreach ([
							 'field' => (array)\Config::inst()->get($ancestorClass, 'field', \Config::UNINHERITED),
							 'list' => (array)\Config::inst()->get($ancestorClass, 'list', \Config::UNINHERITED),
						 ] as $type => $attrs) {
					$fields[$ancestorClass][$type] = [];
					foreach ($attrs as $name => $spec) {
						$fields[$ancestorClass][$type][$name] = \FormField::name_to_label($name);
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
		return (isset($labels[$name])) ? $labels[$name] : \FormField::name_to_label($name);
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
		return Serializer::serialize($this);
	}


	/**
	 * Process tri-state responses from permission-alterting extensions.  The extensions are
	 * expected to return one of three values:
	 *  - false: Disallow this permission, regardless of what other extensions say
	 *  - true: Allow this permission, as long as no other extensions return false
	 *  - NULL: Don't affect the outcome
	 * This method itself returns a tri-state value, and is designed to be used like this:
	 * <code>
	 * $extended = $this->extendedCan('canDoSomething', $member);
	 * if($extended !== null) return $extended;
	 * else return $normalValue;
	 * </code>
	 *
	 * @param String $methodName Method on the same object, e.g. {@link canEdit()}
	 * @param \Member|int $member
	 * @return boolean|null
	 */
	public function extendedCan($methodName, $member) {
		$results = $this->extend($methodName, $member);
		if ($results && is_array($results)) {
			// Remove NULLs
			$results = array_filter($results, function ($v) {
				return !is_null($v);
			});
			// If there are any non-NULL responses, then return the lowest one of them.
			// If any explicitly deny the permission, then we don't get access
			if ($results) return min($results);
		}
		return null;
	}

	/**
	 * @param \Member $member
	 * @return boolean
	 */
	public function canView($member = null) {
		$extended = $this->extendedCan(__FUNCTION__, $member);
		if ($extended !== null) {
			return $extended;
		}
		return \Permission::check('ADMIN', 'any', $member);
	}

	/**
	 * @param \Member $member
	 * @return boolean
	 */
	public function canEdit($member = null) {
		$extended = $this->extendedCan(__FUNCTION__, $member);
		if ($extended !== null) {
			return $extended;
		}
		return \Permission::check('ADMIN', 'any', $member);
	}

	/**
	 * @param \Member $member
	 * @return boolean
	 */
	public function canDelete($member = null) {
		$extended = $this->extendedCan(__FUNCTION__, $member);
		if ($extended !== null) {
			return $extended;
		}
		return \Permission::check('ADMIN', 'any', $member);
	}

	/**
	 * @todo Should canCreate be a static method?
	 * @param \Member $member
	 * @return boolean
	 */
	public function canCreate($member = null) {
		$extended = $this->extendedCan(__FUNCTION__, $member);
		if ($extended !== null) {
			return $extended;
		}
		return \Permission::check('ADMIN', 'any', $member);
	}

	public function i18n_singular_name() {
		// TODO fix class name
		$class = explode('\\', $this->class);
		$class = $class[count($class) - 1];
		return _t("{$this->class}.SINGULARNAME", FormField::name_to_label($class));
	}

	public function i18n_plural_name() {
		$name = $this->i18n_singular_name();
		//if the penultimate character is not a vowel, replace "y" with "ies"
		if (preg_match('/[^aeiou]y$/i', $name)) {
			$name = substr($name, 0, -1) . 'ie';
		}
		return _t("{$this->class}.PLURALNAME", $name . 's');
	}
}
