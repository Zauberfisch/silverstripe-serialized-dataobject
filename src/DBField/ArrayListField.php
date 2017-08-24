<?php

namespace zauberfisch\SerializedDataObject\DBField;

use zauberfisch\SerializedDataObject\ArrayList;

/**
 * @author Zauberfisch
 */
class ArrayListField extends AbstractListField {
	public function nullValue() {
		return new ArrayList();
	}
}
