<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\Form;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\View\Requirements;
use zauberfisch\SerializedDataObject\AbstractDataObject;
use zauberfisch\SerializedDataObject\ArrayList;
use zauberfisch\SerializedDataObject\DBField\ArrayListField as ArrayListDBField;

class ArrayListField extends FormField {
	protected $recordFieldsCallback;
	protected $recordFieldsUpdateCallback;
	protected $recordClassNames = [];
	protected $orderable = false;
	protected $schemaDataType = FormField::SCHEMA_DATA_TYPE_CUSTOM;
	protected $compactLayout = false;
	protected $emptyDefaultValue = false;
	protected $fieldLabels = [];
	
	public function __construct($name, $title, $recordClassNames) {
		$recordClassNames = is_array($recordClassNames) ? array_values($recordClassNames) : [$recordClassNames];
		foreach ($recordClassNames as $className) {
			$this->recordClassNames[md5($className)] = $className;
		}
		parent::__construct($name, $title);
	}
	
	/**
	 * @param ArrayListDBField|array|string $val
	 * @param null $data
	 * @return $this
	 * @throws \Exception
	 */
	public function setValue($val, $data = null) {
		if (is_a($val, ArrayListDBField::class)) {
			$this->value = $val;
		} else {
			$this->value = new ArrayListDBField();
			$this->value->setValue('', null, true);
			if ($val) {
				// value is an array after form submission, lets turn it into an object
				if (is_array($val)) {
					$this->value->setValue($this->createValueFromArray($val));
				} else if (is_string($val)) {
					$this->value->setValue($val);
				} else {
					throw new \Exception('unexpected value');
				}
			}
		}
		return $this;
	}
	
	/**
	 * @return \zauberfisch\SerializedDataObject\DBField\ArrayListField
	 * @throws \Exception
	 */
	public function Value() {
		$return = parent::Value();
		if (!$return) {
			$this->setValue(null);
			$return = parent::Value();
		}
		if ($this->hasEmptyDefaultValue() && !$return->getValue()->exists()) {
			$class = array_values($this->recordClassNames)[0];
			$return->getValue()->push(new $class());
			//$this->value->setValue(new ArrayList(new $this->recordClassName()));
		}
		return $return;
	}
	
	/**
	 * @param $array
	 * @return ArrayList
	 * @throws \Exception
	 */
	public function createValueFromArray($array) {
		$records = [];
		foreach ($array as $recordArray) {
			$classNameHash = array_keys($recordArray)[0];
			$recordArray = $recordArray[$classNameHash];
			$class = $this->recordClassNames[$classNameHash];
			/** @var AbstractDataObject $record */
			$record = new $class();
			$record->update($recordArray);
			$records[] = $record;
		}
		return new ArrayList($records);
	}
	
	/**
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}
	
	/**
	 * @param array $properties
	 * @return string
	 */
	public function FieldHolder($properties = []) {
		$this->addExtraClass(self::class);
		if ($this->isOrderable()) {
			$this->addExtraClass('orderable');
		}
		$this->addExtraClass($this->isCompactLayout() ? 'layout-compact' : 'layout-default');
		$this->setAttribute('data-name', $this->getName());
		$this->setAttribute('data-add-record-url', $this->getAddRecordLink());
		return parent::FieldHolder($properties);
	}
	
	/**
	 * @param array $properties
	 * @return string
	 * @throws \Exception
	 */
	public function Field($properties = []) {
		Requirements::javascript('zauberfisch/silverstripe-serialized-dataobject:javascript/ArrayListField.js');
		Requirements::css('zauberfisch/silverstripe-serialized-dataobject:css/ArrayListField.scss.css');
		$records = $this->Value()->getValue();
		$fields = [];
		foreach ($records as $i => $record) {
			if (is_a($record, \__PHP_Incomplete_Class::class)) {
			} else {
				$fields[] = $this->getRecordFields($i, $record);
			}
		}
		
		$actions = [];
		foreach ($this->recordClassNames as $classNameHash => $class) {
			$actions[] = (new FormAction('addRecord', $this->fieldLabel("$class.AddRecord")))
				->setAttribute('tabindex', '-1')
				->setAttribute('data-classnamehash', $classNameHash)
				->setUseButtonTag(true)
				->addExtraClass('font-icon-plus')
				->addExtraClass('add-record');
		}
		
		/** @noinspection PhpParamsInspection */
		return (new CompositeField(array_merge([
			(new CompositeField($fields))->addExtraClass('record-list'),
		], $actions)))->addExtraClass('field-inner')->FieldHolder()->forTemplate();
	}
	
	/**
	 * @param int $index
	 * @param AbstractDataObject $record
	 * @return CompositeField
	 * @throws \Exception
	 */
	protected function getRecordFields($index, AbstractDataObject $record) {
		$className = get_class($record);
		/** @var FieldList $recordFields */
		$recordFields = call_user_func($this->getRecordFieldsCallback($className), $this, $record);
		if (!is_a($recordFields, FieldList::class)) {
			throw new \Exception(sprintf(
				'RecordFieldsCallback is expected to return FieldList, but returned "%s"',
				is_object($recordFields) ? get_class($recordFields) : gettype($recordFields)
			));
		}
		$recordFields->setForm($this->form);
		$this->loadDataFromRecord($recordFields->dataFields(), $record);
		$controls = [
			(new FormAction('ArrayListFieldControlsDelete', ''))
				->setUseButtonTag(true)
				->addExtraClass('delete-record')
				->addExtraClass('font-icon-cancel-circled')
				->setAttribute('data-confirm', $this->fieldLabel("$className.ConfirmDelete")),
		];
		if ($this->orderable) {
			$controls [] = (new FormAction('ArrayListFieldControlsOrderableUp', ''))
				->setUseButtonTag(true)
				->addExtraClass('orderable-up')
				->addExtraClass('font-icon-up-open-big');
			$controls [] = (new FormAction('ArrayListFieldControlsOrderableDown', ''))
				->setUseButtonTag(true)
				->addExtraClass('orderable-down')
				->addExtraClass('font-icon-down-open-big');
			$controls [] = new LiteralField('ArrayListFieldControlsOrderableHandle', '<div class="orderable-handle"></div>');
		}
		$recordFields->push(
			(new CompositeField($controls))
				->setName('ArrayListFieldControls')
				->addExtraClass('controls')
		);
		$this->prefixRecordFields($index, $recordFields, $className);
		$callback = $this->getRecordFieldsUpdateCallback();
		if ($callback) {
			$recordFields = call_user_func($callback, $recordFields, $this, $record, $index);
		}
		return (new CompositeField($recordFields))->addExtraClass('record');
	}
	
	const MERGE_DEFAULT = 0;
	const MERGE_CLEAR_MISSING = 1;
	const MERGE_IGNORE_FALSEISH = 2;
	
	protected function loadDataFromRecord($dataFields, $data) {
		$mergeStrategy = 0;
		foreach ($dataFields as $field) {
			/** @var FormField $field */
			$name = $field->getName();
			
			// First check looks for (fieldname)_unchanged, an indicator that we shouldn't overwrite the field value
			if (is_array($data) && isset($data[$name . '_unchanged'])) continue;
			
			// Does this property exist on $data?
			$exists = false;
			// The value from $data for this field
			$val = null;
			
			if (is_object($data)) {
				/** @var DataObject $data */
				$exists = (
					isset($data->$name) ||
					ClassInfo::hasMethod($data, $name) ||
					(ClassInfo::hasMethod($data, 'hasField') && $data->hasField($name))
				);
				
				if ($exists) {
					$val = $data->__get($name);
				}
			} else if (is_array($data)) {
				if (array_key_exists($name, $data)) {
					$exists = true;
					$val = $data[$name];
				} // If field is in array-notation we need to access nested data
				else if (preg_match_all('/(.*)\[(.*)\]/U', $name, $matches)) {
					//discard first match which is just the whole string
					array_shift($matches);
					
					$keys = array_pop($matches);
					$name = array_shift($matches);
					$name = array_shift($name);
					
					if (array_key_exists($name, $data)) {
						$tmpData = &$data[$name];
						// drill down into the data array looking for the corresponding value
						foreach ($keys as $arrayKey) {
							if ($arrayKey !== '') {
								$tmpData = &$tmpData[$arrayKey];
							} else {
								//empty square brackets means new array
								if (is_array($tmpData)) {
									$tmpData = array_shift($tmpData);
								}
							}
						}
						if ($tmpData) {
							$val = $tmpData;
							$exists = true;
						}
					}
				}
			}
			
			// save to the field if either a value is given, or loading of blank/undefined values is forced
			if ($exists) {
				if ($val != false || ($mergeStrategy & self::MERGE_IGNORE_FALSEISH) != self::MERGE_IGNORE_FALSEISH) {
					// pass original data as well so composite fields can act on the additional information
					$field->setValue($val, $data);
				}
			} else if (($mergeStrategy & self::MERGE_CLEAR_MISSING) == self::MERGE_CLEAR_MISSING) {
				$field->setValue($val, $data);
			}
		}
	}
	
	/**
	 * @param $index
	 * @param FieldList $fields
	 * @param $className
	 */
	protected function prefixRecordFields($index, $fields, $className) {
		foreach ($fields as $field) {
			/** @var FormField|CompositeField $field */
			$name = $field->getName();
			if ($name) {
				$field->setName($this->getPrefixedRecordFieldName($index, $name, $className));
			}
			if ($field->isComposite()) {
				$this->prefixRecordFields($index, $field->FieldList(), $className);
			}
		}
	}
	
	public function getPrefixedRecordFieldName($index, $fieldName, $className) {
		$classNameHash = array_search($className, $this->recordClassNames);
		return sprintf('%s[%s][%s][%s]', $this->getName(), $index, $classNameHash, $fieldName);
	}
	
	public function handleSubField($fullFieldName) {
		$str = substr($fullFieldName, strlen($this->getName()));
		if (preg_match('/^\[(\d*)\]\[([^]]*)\]/', $str, $matches)) {
			$index = $matches[1];
			$class = $this->recordClassNames[$matches[2]];
			$fields = $this->getRecordFields($index, new $class());
			$subField = $fields->FieldList()->dataFieldByName($fullFieldName);
			if (!$subField) {
				$subField = $fields->FieldList()->fieldByName($fullFieldName);
			}
			$subField->setForm($this->getForm());
			return $subField;
		}
		return null;
	}
	
	/**
	 * @param DataObjectInterface $record
	 */
	public function saveInto(DataObjectInterface $record) {
		$record->{$this->name} = $this->Value()->__toString();
	}
	
	private static $allowed_actions = [
		'addRecord',
	];
	
	public function addRecord(HTTPRequest $r) {
		$index = (int)$r->getVar('index');
		$class = $this->recordClassNames[$r->getVar('ClassNameHash')];
		return $this->getRecordFields($index, new $class())->FieldHolder()->forTemplate();
	}
	
	public function getAddRecordLink() {
		return $this->Link('addRecord');
	}
	
	/**
	 * @param bool $bool
	 * @return ArrayListField
	 */
	public function setOrderable($bool) {
		$this->orderable = $bool;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isOrderable() {
		return $this->orderable;
	}

	/**
	 * @param bool $bool
	 * @return ArrayListField
	 */
	public function setCompactLayout($bool) {
		$this->compactLayout = $bool;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isCompactLayout() {
		return $this->compactLayout;
	}
	
	/**
	 * @param bool $bool
	 * @return ArrayListField
	 */
	public function setEmptyDefaultValue($bool) {
		$this->emptyDefaultValue = $bool;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function hasEmptyDefaultValue() {
		return $this->emptyDefaultValue;
	}
	
	public function setForm($form) {
		parent::setForm($form);
	}
	
	/**
	 * @param callable $recordFieldsCallback
	 * @return ArrayListField
	 */
	public function setRecordFieldsCallback($recordFieldsCallback) {
		$this->recordFieldsCallback = $recordFieldsCallback;
		return $this;
	}
	
	/**
	 * @param string $className
	 * @return callable
	 */
	public function getRecordFieldsCallback($className) {
		$callback = $this->recordFieldsCallback;
		if (!is_callable($callback)) {
			/**
			 * @param ArrayListField $field
			 * @param AbstractDataObject $record
			 * @return mixed
			 */
			$callback = function (ArrayListField $field, $record = null) use ($className) {
				if (!$record) {
					$record = $className::singleton();
				}
				return $record->getCMSFields();
			};
		}
		return $callback;
	}
	
	/**
	 * @param callable $recordFieldsUpdateCallback
	 * @return ArrayListField
	 */
	public function setRecordFieldsUpdateCallback($recordFieldsUpdateCallback) {
		$this->recordFieldsUpdateCallback = $recordFieldsUpdateCallback;
		return $this;
	}
	
	/**
	 * @return callable|null
	 */
	public function getRecordFieldsUpdateCallback() {
		return $this->recordFieldsUpdateCallback;
	}
	
	public function setFieldLabel($name, $string) {
		$this->fieldLabels[$name] = $string;
	}
	
	public function fieldLabels() {
		$arr = [];
		foreach ($this->recordClassNames as $class) {
			$values = [
				'type' => $class::singleton()->i18n_singular_name(),
			];
			$arr["$class.AddRecord"] = _t('zauberfisch\SerializedDataObject\Form\ArrayListField.AddRecord', 'add {type}', $values);
			$arr["$class.ConfirmDelete"] = _t('zauberfisch\SerializedDataObject\Form\ArrayListField.ConfirmDelete', 'Are you sure you want to delete this {type}?', $values);
		}
		$arr = array_merge($arr, $this->fieldLabels);
		return $arr;
	}
	
	public function fieldLabel($name) {
		return $this->fieldLabels()[$name];
	}
}
