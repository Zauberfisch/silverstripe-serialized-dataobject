# SilverStripe SerializedDataObject module

SilverStripe database field that allows saving arbitrary data in a single db field using serialization.
 Additionally, this module also provides some Form Field that allows saving into those database fields.

The primary motivation for this module is to be able to save collections of data and relations to
 other objects directly into the Object without the need of a relation table. This is especially useful
 since SilverStripe does not support versioning of relations at this time.

## Maintainer Contact

* Zauberfisch <code@zauberfisch.at>

## Requirements

* silverstripe/framework >=4.2

## Installation

* `composer require "zauberfisch/silverstripe-serialized-dataobject"`
* rebuild manifest (flush)

## Documentation

See [docs/en/index.md](docs/en/index.md) for documentation and examples.

## Attribution

- JavaScript and CSS taken from the [bummzack/sortablefile](https://packagist.org/packages/bummzack/sortablefile) module 
