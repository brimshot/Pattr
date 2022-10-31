# PhpAttributes

An easy way to work with attributes in PHP.

## Installation

Install with composer:
```
composer require brimshot/phpattributes
```

Or download directly from:

https://github.com/brimshot/PhpAttributes

## Usage

PhpAttributes provides functions meant to simplify working with attributes in PHP.

Make the functions accessible in your code via including your `vendor/autoload.php` (when using composer) or by including `PhpAttributes.php` directly.

For each function you want to call, you can add a `use` directive at the top of your file for convenience:

```php
use function brimshot\PhpAttributes\has_attribute;
```

which will remove the need to prepend the namespace when calling the function.

The functions in the library operate on classes (or instantiated objects), class properties, class methods, class constants and functions.

## Function list

### has_attribute(mixed $item, string|array $attribute_or_array_of_attributes, bool $matchChildAttributes = true) : bool

Returns true or false whether the provided item has the provided attribute or *all* of the provided attributes if `$attribute_or_array_of_attributes` is an array.

By default, child classes of provided attributes will be matched but this can be disabled (strict mode) by passing false as the third argument.

`$attribute_or_array_of_attributes` can also be a string (or strings) i.e. `"MyAttribute"` in which case the unqualified classname will be matched.

Example:
```
namespace PhpAttributes\examples;

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

### has_attribute_callback(mixed $item, string $attribute, callable $callback) : bool

Returns true or false whether the provided item has the provided attribute and the attribute, when instantiated, satisfies the provided callback.

Example:

```
has_attribute_callback(Luke::class, HasALightSaber::class, fn($a) => $a->color == 'green'); // returns true

has_attribute_callback(ObiWan::class, HasALightSaber::class, fn($a) => $a->color == 'green'); // returns false
```

### does_not_have_attribute(mixed $item, string|array $attribute_or_array_of_attributes, bool $matchChildAttributes = false) : bool

Returns true if the provided item does not have the provided attribute or if `$attribute_or_array_of_attributes` is an array, returns true if the item does not have *any* of the provided attributes.

Example:
```
does_not_have_attribute(ObiWan::class, IsFromTatooine::class); // returns true

does_not_have_attribute(ObiWan::class, [IsFromTatooine::class, HasALightSaber::class]); // returns false

```

### get_attribute_names(mixed $item, bool $shortNames = false) : array

Returns the names of the provided items attributes. Default is the fully qualified class name, but names can be shortened by passing true for `$shortNames`.

Example:
```php
/*
Array
(
    [0] => PhpAttributes\examples\HasALightSaber
    [1] => PhpAttributes\examples\IsFromTatooine
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
print_r(get_attribute_names(Luke::class, true));
```

### get_attribute(mixed $item, string $attribute, int $index = 0) : ?object

Returns an instance of the requested attribute from the provided item, or null if item does not have the desired attribute.

An optional index parameter can be provided for use with repeated attributes to select which to return (index begins at 0).

Example:
```php
$a = get_attribute(Luke::class, HasALightSaber::class);

// PhpAttributes\examples\HasALightSaber
echo get_class($a) . "\n";

// "green"
echo $a->color . "\n";
```

### get_attributes(mixed $item, array $attribute_list = []) : array

Returns an array of instances of all of items attributes. Can be filtered to only include certain attributes