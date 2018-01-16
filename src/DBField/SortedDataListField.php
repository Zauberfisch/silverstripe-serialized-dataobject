<?php

namespace zauberfisch\SerializedDataObject\DBField;

use zauberfisch\SerializedDataObject\SortedDataList;

/**
 * @author Zauberfisch
 */
class SortedDataListField extends DataListField {
	public function nullValue() {
		return new SortedDataList();
	}
}
