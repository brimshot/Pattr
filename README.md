# Pattr

A straightforward and simple library for working with attributes in PHP.

## Installation

Install with composer:
```
composer require brimshot/Pattr
```

Or download directly from:

https://github.com/brimshot/Pattr

## Usage

Pattr provides functions meant to simplify working with attributes in PHP.

Make the functions accessible in your code by including `vendor/autoload.php` (when using composer) or by including `src/Pattr.php` directly.

Import each function you intend to use with a `use` directive at the top of your source file, i.e.:

```php
use function brimshot\Pattr\has_attribute;
```

The functions in the library operate on classes (or instantiated objects), public class properties, class methods, class constants and globally available functions.

To get information about a class property or method, pass an array i.e. [ClassName, MethodName]

## Function list

The following functions defined in the library:

---

**has_attribute(mixed $item, string|array $attribute_or_array_of_attributes, bool $matchChildAttributes = true) : bool**

Returns true or false whether the provided item has the provided attribute or *all* of the provided attributes if `$attribute_or_array_of_attributes` is an array.

By default, child classes of provided attributes will be matched but this can be disabled (strict mode) by passing false as the third argument.

`$attribute_or_array_of_attributes` can also be a string (or strings) in which case the unqualified classname will be matched.

Example:
```php
namespace brimshot\Pattr\examples;

use function brimshot\Pattr\has_attribute;

// Define some attributes

#[\Attribute]
class HasALightSaber
{
	public function __construct(public string $color) {}
}

#[\Attribute]
class IsFromTatooine {}


// Define some classes

#[HasALightSaber(color: 'blue')]
class ObiWan {}

#[HasALightSaber(color: 'green')]
#[IsFromTatooine]
class Luke {}


// Check attributes

has_attribute(ObiWan::class, HasALightSaber::class); // returns true

has_attribute(Luke::class, 'HasALightSaber'); // returns true

has_attribute(ObiWan::class, [HasALightSaber::class, IsFromTatooine::class]); // returns false

has_attribute(Luke::class, [HasALightSaber::class, IsFromTatooine::class]); // returns true

```
---
**has_attribute_callback(mixed $item, string $attribute, callable $callback, $matchChildAttributes = true) : bool**

Returns true or false whether the provided item has the provided attribute and the attribute, when instantiated, satisfies the provided callback. By default, child attributes of the provided attribute will also be matched.

Example:
```php
use function brimshot\Pattr\has_attribute_callback;

has_attribute_callback(Luke::class, HasALightSaber::class, fn($a) => $a->color == 'green'); // returns true

has_attribute_callback(ObiWan::class, HasALightSaber::class, fn($a) => $a->color == 'green'); // returns false
```
---
**does_not_have_attribute(mixed $item, string|array $attribute_or_array_of_attributes, bool $matchChildAttributes = true) : bool**

Returns true if the provided item does not have the provided attribute or if `$attribute_or_array_of_attributes` is an array, returns true if the item does not have *any* of the provided attributes.

Example:
```php
use function brimshot\Pattr\does_not_have_attribute;

does_not_have_attribute(ObiWan::class, IsFromTatooine::class); // returns true

does_not_have_attribute(ObiWan::class, [IsFromTatooine::class, HasALightSaber::class]); // returns false

```
---
**get_attribute_names(mixed $item, bool $shortNames = false) : array**

Returns the names of the provided items attributes. Default is the fully qualified class name, but names can be shortened by passing true for `$shortNames`.

Example:
```php
use function brimshot\Pattr\get_attribute_names;

/*
Array
(
    [0] => Pattr\examples\HasALightSaber
    [1] => Pattr\examples\IsFromTatooine
)
*/
print_r(get_attribute_names(Luke::class));

/*
Array
(
    [0] => HasALightSaber
    [1] => IsFromTatooine
)
*/
print_r(get_attribute_names(Luke::class, true)); // Pass true as the second argument to return short names
```
---
**get_attribute(mixed $item, string $attribute, bool $matchChildAttributes = true, int $index = 0) : ?object**

Returns an instance of the requested attribute from the provided item, or null if item does not have the desired attribute.

An optional index parameter can be provided for use with repeated attributes to select which to return (index begins at 0).

Example:
```php
use function brimshot\Pattr\get_attribute;

$a = get_attribute(Luke::class, HasALightSaber::class);

// Pattr\examples\HasALightSaber
echo get_class($a) . "\n";

// "green"
echo $a->color . "\n";
```
---
**get_attributes(mixed $item, array $attribute_list = [], bool $matchChildAttributes = true) : array**

Returns an array of instances of the provided items attributes. Can be filtered to a desired subset by passing in `$attribute_list`. By default, children of attributes provided in the filter list are included in the result set.

Example:

```php
namespace brimshot\Pattr\examples;

use function brimshot\Pattr\get_attributes;

#[HasALightSaber(color: 'green')]
#[IsFromTatooine]
class Luke 
{
    #[UsesTheForce]
    #[JediPower]
    public function useJediMindTrick() {}
}

/*
Array
(
    [0] => brimshot\Pattr\examples\UsesTheForce Object
        (
        )

    [1] => brimshot\Pattr\examples\JediPower Object
        (
        )

)
*/
print_r(get_attributes([Luke::class, 'useJediMindTrick'])); // Get the attributes of a class method

/*
Array
(
    [0] => Pattr\examples\HasALightSaber Object
        (
            [color] => green
        )
)
*/
print_r(get_attributes(Luke::class, [HasALightSaber::class])); // Get the attributes of a class, filtered to only return the desired results
print_r(get_attributes(Luke::class, ['HasALightSaber'])); // Same result

```
---
**get_attributes_callback(mixed $item, string $attribute, callable $callback, $matchChildAttributes = true) : array**

Returns an array of instances of the requested attribute (array contains multiple results on repeated attributes) from the provided item when those attribute(s) satisfy the provided callback, or an empty array when none match.

```php
use function brimshot\Pattr\get_attributes_callback;

$matches = get_attributes_callback(Luke::class, HasALightSaber::class, fn($a) => $a->color == 'green');
echo empty($matches) ? 'empty' : get_class($matches[0]); // brimshot\Pattr\examples\HasALightSaber

$matches = get_attributes_callback(ObiWan::class, HasALightSaber::class, fn($a) => $a->color == 'green');
echo empty($matches) ? 'empty' : get_class($matches[0]); // 'empty'
```
---
**get_class_methods_with_attribute(object|string $object_or_class, string|array $attribute_or_array_of_attributes, bool $matchChildAttributes = true) : array**

Returns an array of method names from the given class or object that have the provided attribute or array of attributes. By default, children of attributes provided in search list are included in the result set.

Example:

```php
use function brimshot\Pattr\get_class_methods_with_attribute;

#[\Attribute]
class UsesTheForce {}

class Luke 
{
    function huntWompRats() {}

    #[UsesTheForce]
    function retrieveLightSaber() {}
    
    #[UsesTheForce]
    function tryToLiftXWing() {}
}

/**
Array
(
    [0] => retrieveLightSaber
    [1] => tryToLiftXWing
)
*/
print_r(get_class_methods_with_attribute(Luke::class, UsesTheForce::class));
```

---
**get_class_methods_with_attribute_callback(object|string $object_or_class, string $attribute, callable $callback, bool $matchChildAttributes = true) : array**

Returns an array of method names from the given class or object that have the provided attribute and that attribute satisfies the provided callback. By default, children of attributes provided in search list are included in the result set.

Example:
```php
use function brimshot\Pattr\get_class_properties_with_attribute_callback;

#[\Attribute]
class DoesNotUseTheForce
{
    public function __construct(public string $location) {}
}

class Luke 
{
    #[DoesNotUseTheForce('anywhere')]
    function huntWompRats() {}

    #[DoesNotUseTheForce('Tatooine')]
    function pickUpPowerConverters() {}
}

/**
Array
(
    [0] => pickUpPowerConverters
)
*/
print_r(get_class_methods_with_attribute_callback(Luke::class, DoesNotUseTheForce::class, fn($a) => $a->location == 'Tatooine'));
```

---

**get_object_properties_with_attribute(object $object, string|array $attribute_or_array_of_attributes, bool $matchChildAttributes = true) : array**

Returns an array of property names and their values from an instantiated object that have the provided attributes or array of attributes. By default, children of attributes provided in search list are included in the result set.

```php
use function brimshot\Pattr\get_object_properties_with_attribute;

#[\Attribute]
class Weapon {}

class XWing
{
    #[Weapon]
    public $laserCannons = 4;
    
    #[Weapon]
    public $protonTorpedoLaunchers = 2;
}

$ship = new XWing();

/*
Array
(
    [laserCannons] => 4
    [protonTorpedoLaunchers] => 2
)
*/
print_r(get_object_properties_with_attribute($ship, Weapon::class));
```

---

***get_object_properties_with_attribute_callback(object $object, string $attribute, callable $callback, $matchChildAttributes = true) : array***

Returns an array of property names and their values from an instantiated object that have the provided attribute and that attribute satisfies the provided callback.

Example:
```php
use function brimshot\Pattr\get_object_properties_with_attribute_callback;

#[\Attribute]
class PassengerData
{
    public function __construct(public string $type) {}
}

class MilleniumFalcon
{
    #[PassengerData('public')]
    public $mainRoomCapacity = 30;
    
    #[PassengerData('secret')]
    public $floorCompartmentCapacity = 2;
}

$ship = new MilleniumFalcon();

/*
Array
(
    [mainRoomCapacity] => 30
)
*/
print_r(get_object_properties_with_attribute_callback($ship, PassengerData::class, fn($a) => $a->type == 'public'));
```

---

**get_class_properties_with_attribute(string $class, string|array $attribute_or_array_of_attributes, bool $matchChildAttributes = true) : array**

Returns an array of property names from a class (not instantiated) that have the provided attributes or array of attributes. By default, children of attributes provided in search list are included in the result set.

Example:
```php
use function brimshot\Pattr\get_class_properties_with_attribute;

/*
(
    [0] => laserCannons
    [1] => protonTorpedoLaunchers
)
*/
print_r(get_class_properties_with_attribute(XWing::class, Weapon::class));
```
---

**get_class_properties_with_attribute_callback(string $class, string $attribute, callable $callback) : array**

Returns an array of property names from a class (not instantiated) that have the provided attribute and that attribute satisfies the provided callback.

Example:
```php
use function brimshot\Pattr\get_class_properties_with_attribute_callback;

/*
Array
(
    [0] => mainRoomCapacity
)
*/
print_r(get_class_properties_with_attribute_callback(MilleniumFalcon::class, PassengerData::class, fn($a) => $a->type == 'public'));
```

---

**get_class_constants_with_attribute(string|object $class_or_object, string|array $attribute_or_array_of_attributes) : array**

Returns an array of constant names and their values from a class or object that have the provided attributes or array of attributes. By default, children of attributes provided in search list are included in the result set.

Example:
```php
use function brimshot\Pattr\get_class_constants_with_attribute;

#[\Attribute]
class CrewData
{
    public function __construct(public string $type) {}
}

class XWing
{
    #[CrewData('human')]
    const PILOTS = 1;
    
    #[CrewData('droid')]
    const R2_UNITS = 1;
}

/*
Array
(
    [PILOTS] => 1
    [R2_UNITS] => 1
)
*/
print_r(get_class_constants_with_attribute(XWing::class, CrewData::class));
```

---

**get_class_constants_with_attribute_callback(string|object $class_or_object, string $attribute, callable $callback, bool $matchAttributeChildren = true) : array**

Returns an array of constant names and their values from a class or object that have the provided attribute and that attribute satisfies the provided callback. By default, children of attributes provided in search list are included in the result set.

Example:
```php
use function brimshot\Pattr\get_object_properties_with_attribute_callback;

/*
Array
(
    [R2_UNITS] => 1
)
*/
print_r(get_class_constants_with_attribute_callback(XWing::class, CrewData::class, fn($a) => $a->type == 'droid'));
```

---
Copyright (c) Chris Brim 2022, see LICENSE file