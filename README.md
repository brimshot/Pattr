# AttributeHelper

An easy way to work with attributes in PHP.

*AttributeHelper* is a utility for working with attributes in PHP 8.

The helper methods allow you to quickly access information about what attributes a class, method or parameter has, including filtering via callback, and retrieve instances of those attributes.


## Installation

Install with composer:
```
composer require brimshot/attributehelper
```

Or download directly from:

https://github.com/brimshot/AttributeHelper

## Usage

`AttributeHelper` is a static class. It cannot be instantiated and all methods are static.

In the below methods, the *$item* provided as the first argument can be either a class, a class method, a class constant or a function. Class methods and constants are identified by passing in an array with the first value being the class name and the second value being the name of the method or constant i.e. `['<class name>', '<method name>']` or `['<class name>', '<constant name>']`

When passing in attribute names as arguments, names for attributes that resolve to classes should be given in form of a qualified class name i.e. 'namespace\path\to\MyAttribute' or *MyAttribute::class*

Names for attributes that do not resolve to a class can be passed as simple strings.

### Method list

```php
// Returns the names of an items attributes
// If the attributes resolve to classes, these will be qualified class names
AttributeHelper::getAttributes($item) : array
```

```php
// Returns an instance of the given attribute from an item or null if the item does not have the attribute requested or attribute does not resolve to a class
// Optional index parameter can be provided when dealing with repeated attributes to choose which of the available options should be returned
AttributeHelper::getAttributeInstance($item, $attribute, $index = 0) : ?object
```

```php
// Returns an array with instances of all an items attributes that resolve to classes
AttributeHelper::getAttributeInstances($item) : array
```

```php
// Returns an array with instances of all an items attributes that resolve to classes filtered by the provided callback
AttributeHelper::getAttributeInstancesCallback(mixed $item, callable $callback) : array
```

```php
// Returns the names of methods on the provided class that have the attributes in the provided list
AttributeHelper::getClassMethodsWithAttributes(object|string $objectOrClass, array $attributesList, $matchAttributeChildren = true) : array
```

```php
// Returns true / false whether an item has a given attribute
AttributeHelper::hasAttribute(mixed $item, string $attribute, bool $matchAttributeChildren = true) : bool
```

```php
// Returns true / false whether an item has a given attribute and that the provided callback function returns true when passed the matched attribute
AttributeHelper::hasAttributeCallback(mixed $item, object|string $attribute, callable $callback) : bool
```

```php
// Returns true / false whether an item has one of the attributes in the provided list
AttributeHelper::hasOneOfTheseAttributes(mixed $item, array $attributesList, $matchAttributeChildren = true) : bool
```

```php
// Returns true / false whether an item has all of the attributes in the provided list
AttributeHelper::hasAllOfTheseAttributes(mixed $item, array $attributesList, $matchAttributeChildren = true) : bool
```

```php
// Returns true / false whether an item has an exact list of attributes. If the item has additional attributes beyond the list in question, this method returns false.
AttributeHelper::hasExactlyTheseAttributes(mixed $item, array $attributesList) : bool
```

```php
// Returns true when the given item does not have any of the attributes in the provided list.
AttributeHelper::doesNotHaveTheseAttributes(mixed $item, array $attributesList, $matchAttributeChildren = true) : bool
```

```php
// Returns true when the given class contains methods which have the attributes in the provided list.
AttributeHelper::classHasMethodsWithAttributes(object|string $objectOrClass, array $attributesList, $matchAttributeChildren = true) : bool
```

```php
// Sequentially calls all methods on a given object that have the attributes in the provided list.
// Returns an array, indexed by method name, with the result of each method called.
AttributeHelper::callClassMethodsWithAttributes(object|string $objectOrClass, array $attributesList, array $methodArguments = array(), $matchAttributeChildren = true) : array
```