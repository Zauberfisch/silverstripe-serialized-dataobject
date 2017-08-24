# SilverStripe SerializedDataObject module

SilverStripe database field that allows saving arbitrary data in a single db field using serialization.
 Additionally, this module also provides some Form Field that allows saving into those database fields.

The primary motivation for this module is to be able to save collections of data and relations to
 other objects directly into the Object without the need of a relation table. This is especially useful
 since SilverStripe does not support versioning of relations at this time.

## Maintainer Contact

* Zauberfisch <code@zauberfisch.at>

## Requirements

* silverstripe/framework >=3.6

## Installation

* `composer require "zauberfisch/silverstripe-serialized-dataobject"`
* rebuild manifest (flush)

## Documentation

### Example

This example creates a Page Type `GalleryPage` with a list of Images. The relation to the 
 images is saved as a serialized list of IDs into the `GalleryPage` table.
This means the List of images is also versioned. Reverting to an older version of the Page
 would also restore the previous list of images.

#### DataObject

	<?php
	
	/**
	 * @property string $Title
	 * @property \zauberfisch\SerializedDataObject\DBField\DataListField $GalleryImages
	 */
	class GalleryPage extends Page {
		private static $db = [
			'Title' => 'Varchar(255)',
			'GalleryImages' => \zauberfisch\SerializedDataObject\DBField\DataListField::class,
		];
		
		/**
		 * @return \zauberfisch\SerializedDataObject\DataList
		 */
		public function Images() {
			return $this->obj('GalleryImages')->getValue();
		}
		
		public function getCMSFields() {
			$fields = parent::getCMSFields();
			$fields->addFieldsToTab('Root.Main', [
				new \zauberfisch\SerializedUploadField\UploadField('GalleryImages', $this->fieldLabel('GalleryImages'))
			]);
			return $fields;
		}
	}
	
#### Template

	<% loop $Images %>
		$ClassName $ID $URL<br>
		<img src="$Fill(400, 300).URL" alt=""><br>
	<% end_loop %>

## Attribution

- JavaScript and CSS taken from the [bummzack/sortablefile](https://packagist.org/packages/bummzack/sortablefile) module 
