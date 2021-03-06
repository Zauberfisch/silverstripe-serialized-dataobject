<?php
declare(strict_types=1);

namespace zauberfisch\SerializedDataObject\DBField;

use zauberfisch\SerializedDataObject\DataList;

/**
 * @author Zauberfisch
 */
class DataListField extends AbstractListField {
	public function nullValue() {
		return new DataList();
	}
}
