This file is a merged representation of the entire codebase, combined into a single document by Repomix.

# File Summary

## Purpose
This file contains a packed representation of the entire repository's contents.
It is designed to be easily consumable by AI systems for analysis, code review,
or other automated processes.

## File Format
The content is organized as follows:
1. This summary section
2. Repository information
3. Directory structure
4. Repository files (if enabled)
5. Multiple file entries, each consisting of:
  a. A header with the file path (## File: path/to/file)
  b. The full contents of the file in a code block

## Usage Guidelines
- This file should be treated as read-only. Any changes should be made to the
  original repository files, not this packed version.
- When processing this file, use the file path to distinguish
  between different files in the repository.
- Be aware that this file may contain sensitive information. Handle it with
  the same level of security as you would the original repository.

## Notes
- Some files may have been excluded based on .gitignore rules and Repomix's configuration
- Binary files are not included in this packed representation. Please refer to the Repository Structure section for a complete list of file paths, including binary files
- Files matching patterns in .gitignore are excluded
- Files matching default ignore patterns are excluded
- Files are sorted by Git change count (files with more changes are at the bottom)

# Directory Structure
```
advanced-usage/
  _index.md
  available-property-mappers.md
  commands.md
  creating-a-cast.md
  creating-a-rule-inferrer.md
  creating-a-transformer.md
  eloquent-casting.md
  get-data-from-a-class-quickly.md
  in-packages.md
  internal-structures.md
  mapping-rules.md
  normalizers.md
  performance.md
  pipeline.md
  traits-and-interfaces.md
  typescript.md
  use-with-inertia.md
  use-with-livewire.md
  validation-attributes.md
  working-with-dates.md
as-a-data-transfer-object/
  _index.md
  abstract-data.md
  casts.md
  collections.md
  computed.md
  creating-a-data-object.md
  defaults.md
  factories.md
  injecting-property-values.md
  mapping-property-names.md
  model-to-data-object.md
  nesting.md
  optional-properties.md
  request-to-data-object.md
as-a-resource/
  _index.md
  appending-properties.md
  from-data-to-array.md
  from-data-to-resource.md
  lazy-properties.md
  mapping-property-names.md
  transformers.md
  wrapping.md
getting-started/
  _index.md
  quickstart.md
validation/
  _index.md
  auto-rule-inferring.md
  introduction.md
  manual-rules.md
  nesting-data.md
  skipping-validation.md
  using-validation-attributes.md
  working-with-the-validator.md
_index.md
about-us.md
changelog.md
installation-setup.md
introduction.md
questions-issues.md
requirements.md
support-us.md
third-party-packages.md
```

# Files

## File: advanced-usage/_index.md
`````markdown
---
title: Advanced usage
weight: 5
---
`````

## File: advanced-usage/available-property-mappers.md
`````markdown
---
title: Available property mappers
weight: 19
---

In previous sections we've already seen how
to [create](/docs/laravel-data/v4/as-a-data-transfer-object/mapping-property-names) data objects where the keys of the
payload differ from the property names of the data object. It is also possible
to [transform](/docs/laravel-data/v4/as-a-resource/mapping-property-names) data objects to an
array/json/... where the keys of the payload differ from the property names of the data object.

These mappings can be set manually put the package also provide a set of mappers that can be used to automatically map
property names:

```php
class ContractData extends Data
{
    public function __construct(
        #[MapName(CamelCaseMapper::class)]
        public string $name,
        #[MapName(SnakeCaseMapper::class)]
        public string $recordCompany,
        #[MapName(new ProvidedNameMapper('country field'))]
        public string $country,
        #[MapName(StudlyCaseMapper::class)]
        public string $cityName,
        #[MapName(LowerCaseMapper::class)]
        public string $addressLine1,
        #[MapName(UpperCaseMapper::class)]
        public string $addressLine2,
    ) {
    }
}
```

Creating the data object can now be done as such:

```php
ContractData::from([
    'name' => 'Rick Astley',
    'record_company' => 'RCA Records',
    'country field' => 'Belgium',
    'CityName' => 'Antwerp',
    'addressline1' => 'some address line 1',
    'ADDRESSLINE2' => 'some address line 2',
]);
```

When transforming such a data object the payload will look like this:

```json
{
    "name" : "Rick Astley",
    "record_company" : "RCA Records",
    "country field" : "Belgium",
    "CityName" : "Antwerp",
    "addressline1" : "some address line 1",
    "ADDRESSLINE2" : "some address line 2"
}
```
`````

## File: advanced-usage/commands.md
`````markdown
---
title: Commands
weight: 16
---

## make:data

You can easily generate new data objects with the artisan command `make:data`:

```shell
php artisan make:data PostData
```

By default, this command puts data objects in the `App\Data` namespace, this can be changed as such:

```shell
php artisan make:data PostData --namespace=DataTransferObjects
```

By default, the command creates a new data object within the `\App\Data` namespace and suffixes the class with `Data`, this can be changed by adding the following lines to the `data.php` config file:

```php
'commands' => [
    /*
     * Provides default configuration for the `make:data` command. These settings can be overridden with options
     * passed directly to the `make:data` command for generating single Data classes, or if not set they will
     * automatically fall back to these defaults. See `php artisan make:data --help` for more information
     */
    'make' => [
        /*
         * The default namespace for generated Data classes. This exists under the application's root namespace,
         * so the default 'Data` will end up as '\App\Data', and generated Data classes will be placed in the
         * app/Data/ folder. Data classes can live anywhere, but this is where `make:data` will put them.
         */
        'namespace' => 'Data',
        
        /*
         * This suffix will be appended to all data classes generated by make:data, so that they are less likely
         * to conflict with other related classes, controllers or models with a similar name without resorting
         * to adding an alias for the Data object. Set to a blank string (not null) to disable.
         */
        'suffix' => 'Data',
    ],
]
```
`````

## File: advanced-usage/creating-a-cast.md
`````markdown
---
title: Creating a cast
weight: 6
---

Casts take simple values and cast them into complex types. For example, `16-05-1994T00:00:00+00` could be cast into a `Carbon` object with the same date.

A cast implements the following interface:

```php
interface Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed;
}
```

A cast receives the following:

- **property** a `DataProperty` object which represents the property for which the value is cast. You can read more about the internal structures of the package [here](/docs/laravel-data/v4/advanced-usage/internal-structures)
- **value** the value that should be cast
- **properties** an array of the current properties that will be used to create the data object
- **creationContext** the context in which the data object is being created you'll find the following info here:
    - **dataClass** the data class which is being created
    - **validationStrategy** the validation strategy which is being used
    - **mapPropertyNames** whether property names should be mapped
    - **disableMagicalCreation** whether to use the magical creation methods or not
    - **ignoredMagicalMethods** the magical methods which are ignored
    - **casts** a collection of global casts

In the end, the cast should return a casted value.

When the cast is unable to cast the value, an `Uncastable` object should be returned.

## Null

A cast like a transformer never receives a `null` value, this is because the package will always keep a `null` value as `null` because we don't want to create values out of thin air. If you want to replace a `null` value, then use a magic method.

## Castables

You may want to allow your application's value objects to define their own custom casting logic. Instead of attaching the custom cast class to your object, you may alternatively attach a value object class that implements the `Spatie\LaravelData\Casts\Castable` interface:

```php
class ForgotPasswordRequest extends Data
{
    public function __construct(
        #[WithCastable(Email::class)]
        public Email $email,
    ) {
    }
}
```

When using `Castable` classes, you may still provide arguments in the `WithCastable` attribute. The arguments will be passed to the `dataCastUsing` method:

```php
class DuplicateEmailCheck extends Data
{
    public function __construct(
        #[WithCastable(Email::class, normalize: true)]
        public Email $email,
    ) {
    }
}
```

By combining "castables" with PHP's [anonymous classes](https://www.php.net/manual/en/language.oop5.anonymous.php), you may define a value object and its casting logic as a single castable object. To accomplish this, return an anonymous class from your value object's `dataCastUsing` method. The anonymous class should implement the `Cast` interface:

```php
<?php
namespace Spatie\LaravelData\Tests\Fakes\Castables;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class Email implements Castable
{
  public function __construct(public string $email) {

  }

  public static function dataCastUsing(...$arguments): Cast
  {
    return new class implements Cast {
        public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
        {
            return new Email($value);
        }
    };
  }
}
```

## Casting iterable values

We saw earlier that you can cast all sorts of values in an array or Collection which are not data objects, for this to work, you should implement the `IterableItemCast` interface:

```php
interface IterableItemCast
{
    public function castIterableItem(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed;
}
```

The `castIterableItem` method is called for each item in an array or Collection when being cast, you can check the `iterableItemType` property of `DataProperty->type` to get the type the items should be transformed into.

## Combining casts and transformers

You can combine casts and transformers in one class:

```php
class ToUpperCastAndTransformer implements Cast, Transformer
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): string
    {
        return strtoupper($value);
    }
    
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): string
    {
        return strtoupper($value);
    }
}
```

Within your data object, you can use the `WithCastAndTransformer` attribute to use the cast and transformer:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[WithCastAndTransformer(SomeCastAndTransformer::class)]
        public string $artist,
    ) {
    }
}
```
`````

## File: advanced-usage/creating-a-rule-inferrer.md
`````markdown
---
title: Creating a rule inferrer
weight: 8
---

Rule inferrers will try to infer validation rules for properties within a data object.

A rule inferrer can be created by implementing the `RuleInferrer` interface:

```php
interface RuleInferrer
{
    public function handle(DataProperty $property, PropertyRules $rules, ValidationContext $context): PropertyRules;
}
```

A collection of previous inferred rules is given, and a `DataProperty` object which represents the property for which the value is transformed. You can read more about the internal structures of the package [here](/docs/laravel-data/v4/advanced-usage/internal-structures).

The `ValidationContext` is also injected, this contains the following info:

- **payload** the current payload respective to the data object which is being validated
- **fullPayload** the full payload which is being validated
- **validationPath** the path from the full payload to the current payload

The `RulesCollection` contains all the rules for the property represented as `ValidationRule` objects.

You can add new rules to it:

```php
$rules->add(new Min(42));
```

When adding a rule of the same kind, a previous version of the rule will be removed:

```php
$rules->add(new Min(42));
$rules->add(new Min(314)); 

$rules->all(); // [new Min(314)]
```

Adding a string rule can be done as such:

```php
$rules->add(new Rule('min:42'));
```

You can check if the collection contains a type of rule:

```php
$rules->hasType(Min::class);
```

Or remove certain types of rules:

```php
$rules->removeType(Min::class);
```

In the end, a rule inferrer should always return a `RulesCollection`.

Rule inferrers need to be manually defined within the `data.php` config file.
`````

## File: advanced-usage/creating-a-transformer.md
`````markdown
---
title: Creating a transformer
weight: 7
---

Transformers take complex values and transform them into simple types. For example, a `Carbon` object could be transformed to `16-05-1994T00:00:00+00`.

A transformer implements the following interface:

```php
interface Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed;
}
```

The following parameters are provided:

- **property**: a `DataProperty` object which represents the property for which the value is transformed. You can read more about the internal structures of the package [here](/docs/laravel-data/v4/advanced-usage/internal-structures)
- **value**: the value that should be transformed, this will never be `null`
- **context**: a `TransformationContext` object which contains the current transformation context with the following properties:
    - **transformValues** indicates if values should be transformed or not
    - **mapPropertyNames** indicates if property names should be mapped or not
    - **wrapExecutionType** the execution type that should be used for wrapping values
    - **transformers** a collection of transformers that can be used to transform values

In the end, the transformer should return a transformed value.

## Combining transformers and casts

You can transformers and casts in one class:

```php
class ToUpperCastAndTransformer implements Cast, Transformer
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): string
    {
        return strtoupper($value);
    }
    
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): string
    {
        return strtoupper($value);
    }
}
```

Within your data object, you can use the `WithCastAndTransformer` attribute to use the cast and transformer:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[WithCastAndTransformer(SomeCastAndTransformer::class)]
        public string $artist,
    ) {
    }
}
```
`````

## File: advanced-usage/eloquent-casting.md
`````markdown
---
title: Eloquent casting
weight: 1
---

Since data objects can be created from arrays and be easily transformed into arrays back again, they are excellent to be used
with [Eloquent casts](https://laravel.com/docs/eloquent-mutators#custom-casts):

```php
class Song extends Model
{
    protected $casts = [
        'artist' => ArtistData::class,
    ];
}
```

Now you can store a data object in a model as such:

```php
Song::create([
    'artist' => new ArtistData(name: 'Rick Astley', age: 22),
]);
```

It is also possible to use an array representation of the data object:

```php
Song::create([
    'artist' => [
        'name' => 'Rick Astley',
        'age' => 22
    ]
]);
```

This will internally be converted to a data object which you can later retrieve as such:

```php
Song::findOrFail($id)->artist; // ArtistData object
```

### Abstract data objects

Sometimes you have an abstract parent data object with multiple child data objects, for example:

```php
abstract class RecordConfig extends Data
{
    public function __construct(
        public int $tracks,
    ) {}
}

class CdRecordConfig extends RecordConfig
{
    public function __construct(
        int $tracks,
        public int $bytes,
    ) {
        parent::__construct($tracks);
    }
}

class VinylRecordConfig extends RecordConfig
{
    public function __construct(
        int $tracks,
        public int $rpm,
    ) {
        parent::__construct($tracks);
    }
}
```

A model can have a JSON field which is either one of these data objects:

```php
class Record extends Model
{
    protected $casts = [
        'config' => RecordConfig::class,
    ];
}
```

You can then store either a `CdRecordConfig` or a `VinylRecord` in the `config` field:

```php
$cdRecord = Record::create([
    'config' => new CdRecordConfig(tracks: 12, bytes: 1000),
]);

$vinylRecord = Record::create([
    'config' => new VinylRecordConfig(tracks: 12, rpm: 33),
]);

$cdRecord->config; // CdRecordConfig object
$vinylRecord->config; // VinylRecordConfig object
```

When a data object class is abstract and used as an Eloquent cast, then this feature will work out of the box.

The child data object value of the model will be stored in the database as a JSON string with the class name and the data object properties:

```json
{
    "type": "\\App\\Data\\CdRecordConfig",
    "data": {
        "tracks": 12,
        "bytes": 1000
    }
}
```

When retrieving the model, the data object will be instantiated based on the `type` key in the JSON string.

#### Abstract data object with collection

You can use with collection.

```php
class Record extends Model
{
    protected $casts = [
        'configs' => DataCollection::class . ':' . RecordConfig::class,
    ];
}
```

#### Abstract data class morphs

By default, the `type` key in the JSON string will be the fully qualified class name of the child data object. This can break your application quite easily when you refactor your code. To prevent this, you can add a morph map like with [Eloquent models](https://laravel.com/docs/eloquent-relationships#polymorphic-relationships). Within your `AppServiceProvivder` you can add the following mapping:

```php
use Spatie\LaravelData\Support\DataConfig;

app(DataConfig::class)->enforceMorphMap([
    'cd_record_config' => CdRecordConfig::class,
    'vinyl_record_config' => VinylRecordConfig::class,
]);
```

## Casting data collections

It is also possible to store data collections in an Eloquent model:

```php
class Artist extends Model
{
    protected $casts = [
        'songs' => DataCollection::class.':'.SongData::class,
    ];
}
```

A collection of data objects within the Eloquent model can be made as such:

```php
Artist::create([
    'songs' => [
        new SongData(title: 'Never gonna give you up', artist: 'Rick Astley'),
        new SongData(title: 'Together Forever', artist: 'Rick Astley'),
    ],
]);
```

It is also possible to provide an array instead of a data object to the collection:

```php
Artist::create([
    'songs' => [
        ['title' => 'Never gonna give you up', 'artist' => 'Rick Astley'],
        ['title' => 'Together Forever', 'artist' => 'Rick Astley']
    ],
]);
```

## Using defaults for null database values

By default, if a database value is `null`, then the model attribute will also be `null`. However, sometimes you might want to instantiate the attribute with some default values.

To achieve this, you may provide an additional `default` [Cast Parameter](https://laravel.com/docs/eloquent-mutators#cast-parameters) to ensure the caster gets instantiated.

```php
class Song extends Model
{
    protected $casts = [
        'artist' => ArtistData::class . ':default',
    ];
}
```

This will ensure that the `ArtistData` caster is instantiated even when the `artist` attribute in the database is `null`.

You may then specify some default values in the cast which will be used instead.

```php
class ArtistData extends Data 
{
    public string $name = 'Default name';
}
```

```php
Song::findOrFail($id)->artist->name; // 'Default name'
```

### Nullable collections

You can also use the `default` argument in the case where you _always_ want a `DataCollection` to be returned.

The first argument (after `:`) should always be the data class to be used with the `DataCollection`, but you can add `default` as a comma separated second argument.

```php
class Artist extends Model
{
    protected $casts = [
        'songs' => DataCollection::class.':'.SongData::class.',default',
    ];
}
```

```php
$artist = Artist::create([
    'songs' => null
]);

$artist->songs; // DataCollection
$artist->songs->count();// 0
```

## Using encryption with data objects and collections

Similar to Laravel's native encrypted casts, you can also encrypt data objects and collections.

When retrieving the model, the data object will be decrypted automatically.

```php
class Artist extends Model
{
    protected $casts = [
        'songs' => DataCollection::class.':'.SongData::class.',encrypted',
    ];
}
```
`````

## File: advanced-usage/get-data-from-a-class-quickly.md
`````markdown
---
title: Get data from a class quickly
weight: 15
---

By adding the `WithData` trait to a Model, Request or any class that can be magically be converted to a data object,
you'll enable support for the `getData` method. This method will automatically generate a data object for the object it
is called upon.

For example, let's retake a look at the `Song` model we saw earlier. We can add the `WithData` trait as follows:

```php
class Song extends Model
{
    use WithData;
    
    protected $dataClass = SongData::class;
}
```

Now we can quickly get the data object for the model as such:

```php
Song::firstOrFail($id)->getData(); // A SongData object
```

We can do the same with a FormRequest, we don't use a property here to define the data class but use a method instead:

```php
class SongRequest extends FormRequest
{
    use WithData;
    
    protected function dataClass(): string
    {
        return SongData::class;
    }
}
```

Now within a controller where the request is injected, we can get the data object like this:

```php
class SongController
{
    public function __invoke(SongRequest $request): SongData
    {
        $data = $request->getData();
    
        $song = Song::create($data->toArray());
        
        return $data;
    }
}
```
`````

## File: advanced-usage/in-packages.md
`````markdown
---
title: In Packages
weight: 18
---

## Testing

When you're developing a package, running into config access issues is quite common, especially when using the magic from factory method, which won't resolve the factory instance without the config. Simply include our Provider in your package's TestCase, and you're all set.

```php
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelDataServiceProvider::class,
            // Register additional service providers to use with your tests
        ];
    }
}
```
`````

## File: advanced-usage/internal-structures.md
`````markdown
---
title: Internal structures
weight: 11
---

This package has some internal structures which are used to analyze data objects and their properties. They can be
helpful when writing casts, transformers or rule inferrers.

## DataClass

The DataClass represents the structure of a data object and has the following properties:

- `name` the name of the data class
- `properties` all the `DataProperty`'s of the class (more on that later)
- `methods` all the magical creation `DataMethod`s of the class (more on that later)
- `constructorMethod` the constructor `DataMethod` of the class
- `isReadOnly` is the class read only
- `isAbstract` is the class abstract
- `appendable` is the class implementing `AppendableData`
- `includeable` is the class implementing `IncludeableData`
- `responsable` is the class implementing `ResponsableData`
- `transformable` is the class implementing `TransformableData`
- `validatable` is the class implementing `ValidatableData`
- `wrappable` is the class implementing `WrappableData`
- `emptyData` the the class implementing `EmptyData`
- `attributes` a collection of resolved attributes assigned to the class
- `dataCollectablePropertyAnnotations` the property annotations of the class used to infer the data collection type
- `allowedRequestIncludes` the allowed request includes of the class
- `allowedRequestExcludes` the allowed request excludes of the class
- `allowedRequestOnly` the allowed request only of the class
- `allowedRequestExcept` the allowed request except of the class
- `outputMappedProperties` properties names which are mapped when transforming the data object
- `transformationFields` structure of the transformation fields

## DataProperty

A data property represents a single property within a data object.

- `name` the name of the property
- `className` the name of the class of the property
- `type` the `DataPropertyType` of the property (more on that later)
- `validate` should the property be automatically validated
- `computed` is the property computed
- `hidden` will the property be hidden when transforming the data object
- `isPromoted` is the property constructor promoted
- `isReadOnly` is the property read only
- `hasDefaultValue` has the property a default value
- `defaultValue` the default value of the property
- `cast` the cast assigned to the property
- `transformer` the transformer assigned to the property
- `inputMappedName` the name used to map a property name given
- `outputMappedName` the name used to map a property name onto
- `attributes` a collection of resolved attributes assigned to the property

## DataMethod

A data method represents a method within a data object.

- `name` the name of the method
- `parameters` all the `DataParameter`'s and `DataProperty`s of the method (more on that later)
- `isStatic` whether the method is static
- `isPublic` whether the method is public
- `isCustomCreationMethod` whether the method is a custom creation method (=magical creation method)
- `returnType` the `DataType` of the return value (more on that later)

## DataParameter

A data parameter represents a single parameter/property within a data method.

- `name` the name of the parameter
- `isPromoted` is the property/parameter constructor promoted
- `hasDefaultValue` has the parameter a default value
- `defaultValue` the default value of the parameter
- `type` the `DataType` of the parameter (more on that later)

## DataType

A data type represents a type within a data object.

- `Type` can be a `NamedType`, `UnionType` or `IntersectionType` (more on that later)
- `isNullable` can the type be nullable
- `isMixed` is the type a mixed type
- `kind` the `DataTypeKind` of the type (more on that later)

## DataPropertyType

Extends from the `DataType` and has the following additional properties:

- `isOptional` can the type be optional
- `lazyType` the class of the lazy type for the property
- `dataClass` the data object class of the property or the data object class of the collection it collects
- `dataCollectableClass` the collectable type of the data objects
- `kind` the `DataTypeKind` of the type (more on that later)

## DataTypeKind

An enum representing the kind of type of a property/parameter with respect to the package:

- Default: a non package specific type
- DataObject: a data object
- DataCollection: a `DataCollection` of data objects
- DataPaginatedCollection: a `DataPaginatedCollection` of data objects
- DataCursorPaginatedCollection: a `DataCursorPaginatedCollection` of data objects
- DataArray: an array of data objects
- DataEnumerable: a `Enumerable` of data objects
- DataPaginator: a `Paginator` of data objects
- DataCursorPaginator: a `CursorPaginator` of data objects

## NamedType

Represents a named PHP type with the following properties:

- `name` the name of the type
- `builtIn` is the type a built-in type
- `acceptedTypes` an array of accepted types as string
- `kind` the `DataTypeKind` of the type (more on that later)
- `dataClass` the data object class of the property or the data object class of the collection it collects
- `dataCollectableClass` the collectable type of the data objects
- `isCastable` wetter the type is a `Castable`

## UnionType / IntersectionType

Represents a union or intersection of types with the following properties:

- `types` an array of types (can be `NamedType`, `UnionType` or `IntersectionType`)
`````

## File: advanced-usage/mapping-rules.md
`````markdown
---
title: Mapping rules
weight: 13
---

It is possible to map the names properties going in and out of your data objects using: `MapOutputName`, `MapInputName`
and `MapName` attributes. But sometimes it can be quite hard to follow where which name can be used. Let's go through
some case:

In the data object:

```php
class UserData extends Data
 {
     public function __construct(
         #[MapName('favorite_song')] // name mapping
         public Lazy|SongData $song,
         #[RequiredWith('song')] // In validation rules, use the original name
         public string $title,
     ) {
     }

     public static function allowedRequestExcept(): ?array
     {
         return [
             'song', // Use the original name when defining includes, excludes, excepts and only
         ];
     }
     
     public function rules(ValidContext $context): array {
        return  [
            'song' => 'required', // Use the original name when defining validation rules
        ];
    }

    // ...
 }
 ```

When creating a data object:

```php
UserData::from([
    'favorite_song' => ..., // You can use the mapped or original name here
    'title' => 'some title'
]);
```

When adding an include, exclude, except or only:

```php
 UserData::from(User::first())->except('song'); // Always use the original name here
```

Within a request query, you can use the mapped or original name:

```
https://spatie.be/my-account?except[]=favorite_song 
```

When validating a data object or getting rules for a data object, always use the original name:

```php
$data = [
    'favorite_song' => 123,
    'title' => 'some title',
];

UserData::validate($data)
UserData::getValidationRules($data)
```
`````

## File: advanced-usage/normalizers.md
`````markdown
---
title: Normalizers
weight: 4
---

This package allows you to dynamically create data objects from any kind of object. For example, you can use an
eloquent model to create a data object like this:

```php
SongData::from(Song::findOrFail($id));
```

A `Normalizer` will take a payload like a model and will transform it into an array, so it can be used in the pipeline (see further).

By default, there are five normalizers:

- **ModelNormalizer** will cast eloquent models
- **ArrayableNormalizer** will cast `Arrayable`'s
- **ObjectNormalizer** will cast `stdObject`'s
- **ArrayNormalizer** will cast arrays
- **JsonNormalizer** will cast json strings

A sixth normalizer can be optionally enabled:

- **FormRequestNormalizer** will normalize a form request by calling the `validated` method

Normalizers can be globally configured in `config/data.php`, and can be configured on a specific data object by overriding the `normalizers` method.

```php
class SongData extends Data
{
    public function __construct(
        // ...
    ) {
    }

    public static function normalizers(): array
    {
        return [
            ModelNormalizer::class,
            ArrayableNormalizer::class,
            ObjectNormalizer::class,
            ArrayNormalizer::class,
            JsonNormalizer::class,
        ];
    }
}
```

A normalizer implements the `Normalizer` interface and should return an array representation of the payload, or null if it cannot normalize the payload:

```php
class ArrayableNormalizer implements Normalizer
{
    public function normalize(mixed $value): ?array
    {
        if (! $value instanceof Arrayable) {
            return null;
        }

        return $value->toArray();
    }
}
```

Normalizers are executed in the same order as they are defined in the `normalize` method. The first normalizer not returning null will be used to normalize the payload. Magical creation methods always have precedence over normalizers.
`````

## File: advanced-usage/performance.md
`````markdown
---
title: Performance
weight: 15
---

Laravel Data is a powerful package that leverages PHP reflection to infer as much information as possible. While this approach provides a lot of benefits, it does come with a minor performance overhead. This overhead is typically negligible during development, but it can become noticeable in a production environment with a large number of data objects.

Fortunately, Laravel Data is designed to operate efficiently without relying on reflection. It achieves this by allowing you to cache the results of its complex analysis. This means that the performance cost is incurred only once, rather than on every request. By caching the analysis results before deploying your application to production, you ensure that a pre-analyzed, cached version of the data objects is used, significantly improving performance.

## Caching

Laravel Data provides a command to cache the analysis results of your data objects. This command will analyze all of your data objects and store the results in a Laravel cache of your choice:

```
php artisan data:cache-structures
```

That's it, the command will search for all the data objects in your application and cache the analysis results. Be sure to always run this command after creating or modifying a data object or when deploying your application to production.

## Configuration

The caching mechanism can be configured in the `data.php` config file. By default, the cache store is set to the default cache store of your application. You can change this to any other cache driver supported by Laravel. A prefix can also be set for the cache keys stored:

```php
'structure_caching' => [
    'cache' => [
        'store' => 'redis',
        'prefix' => 'laravel-data',
    ],
],
```

To find the data classes within your application, we're using the [php-structure-discoverer](https://github.com/spatie/php-structure-discoverer) package. This package allows you to configure the directories that will be searched for data objects. By default, the `app/data` directory is searched recursively. You can change this to any other directory or directories:

```php
'structure_caching' => [
    'directories' => [
        app_path('Data'),
    ],
],
```

Structure discoverer uses reflection (enabled by default) or a PHP parser to find the data objects. You can disable the reflection-based discovery and thus use the PHP parser discovery as such:

```php
'structure_caching' => [
    'reflection_discovery' => [
        'enabled' => false,
    ],
],
```

Since we cannot depend on reflection, we need to tell the parser what data objects are exactly and where to find them. This can be done by adding the laravel-data directory to the config directories:

```php
'structure_caching' => [
    'directories' => [
        app_path('Data'),
        base_path('vendor/spatie/laravel-data/src'),
    ],
],
```

When using reflection discovery, the base directory and root namespace can be configured as such if you're using a non-standard directory structure or namespace

```php
'structure_caching' => [
    'reflection_discovery' => [
        'enabled' => true,
        'base_path' => base_path(),
        'root_namespace' => null,
    ],
],
```

The caching mechanism can be disabled by setting the `enabled` option to `false`:

```php
'structure_caching' => [
    'enabled' => false,
],
```

You can read more about reflection discovery [here](https://github.com/spatie/php-structure-discoverer#parsers).

## Testing

When running tests, the cache is automatically disabled. This ensures that the analysis results are always up-to-date during development and testing. And that the cache won't interfere with your caching mocks.
`````

## File: advanced-usage/pipeline.md
`````markdown
---
title: Pipeline
weight: 5
---

The data pipeline allows you to configure how data objects are constructed from a payload. In the previous chapter we
saw that a data object created from a payload will be first normalized into an array. This array is passed into the
pipeline.

The pipeline exists of multiple pipes which will transform the normalized data into a collection of property values
which can be passed to the data object constructor.

By default, the pipeline exists of the following pipes:

- **AuthorizedDataPipe** checks if the user is authorized to perform the request
- **MapPropertiesDataPipe** maps the names of properties
- **FillRouteParameterPropertiesDataPipe** fills property values from route parameters
- **ValidatePropertiesDataPipe** validates the properties
- **DefaultValuesDataPipe** adds default values for properties when they are not set
- **CastPropertiesDataPipe** casts the values of properties

Each result of the previous pipe is passed on into the next pipe, you can define the pipes on an individual data object
as such:

```php
class SongData extends Data
{
    public function __construct(
        // ...
    ) {
    }

    public static function pipeline(): DataPipeline
    {
        return DataPipeline::create()
            ->into(static::class)
            ->through(AuthorizedDataPipe::class)
            ->through(MapPropertiesDataPipe::class)
            ->through(FillRouteParameterPropertiesDataPipe::class)
            ->through(ValidatePropertiesDataPipe::class)
            ->through(DefaultValuesDataPipe::class)
            ->through(CastPropertiesDataPipe::class);
    }
}
```

Each pipe implements the `DataPipe` interface and should return an `array` of properties:

```php
interface DataPipe
{
    public function handle(mixed $payload, DataClass $class, array $properties, CreationContext $creationContext): array;
}
```

The `handle` method has several arguments:

- **payload** the non normalized payload
- **class** the `DataClass` object for the data
  object [more info](/docs/laravel-data/v4/advanced-usage/internal-structures)
- **properties** the key-value properties which will be used to construct the data object
- **creationContext** the context in which the data object is being created you'll find the following info here:
  - **dataClass** the data class which is being created
  - **validationStrategy** the validation strategy which is being used
  - **mapPropertyNames** whether property names should be mapped
  - **disableMagicalCreation** whether to use the magical creation methods or not
  - **ignoredMagicalMethods** the magical methods which are ignored
  - **casts** a collection of global casts

When using a magic creation methods, the pipeline is not being used (since you manually overwrite how a data object is
constructed). Only when you pass in a request object a minimal version of the pipeline is used to authorize and validate
the request.

## Preparing data for the pipeline

Sometimes you need to make some changes to the payload after it has been normalized, but before they are sent into the data pipeline. You can do this using the `prepareForPipeline` method as follows: 

```php
class SongMetadata
{
    public function __construct(
        public string $releaseYear,
        public string $producer,
    ) {}
}

class SongData extends Data
{
    public function __construct(
        public string $title,
        public SongMetadata $metadata,
    ) {}
    
    public static function prepareForPipeline(array $properties): array
    {
        $properties['metadata'] = Arr::only($properties, ['release_year', 'producer']);
        
        return $properties;
    }
}
```

Now it is possible to create a data object as follows:

```php
$songData = SongData::from([
    'title' => 'Never gonna give you up',
    'release_year' => '1987',
    'producer' => 'Stock Aitken Waterman',
]);
```

## Extending the pipeline within your data class

Sometimes you want to send your payload first through a certain pipe without creating a whole new pipeline, this can be done as such:

```php
class SongData extends Data
{
    public static function pipeline(): DataPipeline
    {
        return parent::pipeline()->firstThrough(GuessCasingForKeyDataPipe::class);
    }
}
```
`````

## File: advanced-usage/traits-and-interfaces.md
`````markdown
---
title: Traits and interfaces
weight: 17
---

Laravel data, is built to be as flexible as possible. This means that you can use it in any way you want.

For example, the `Data` class we've been using throughout these docs is a class implementing a few data interfaces and traits:

```php
use Illuminate\Contracts\Support\Responsable;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\BaseData;
use Spatie\LaravelData\Concerns\ContextableData;
use Spatie\LaravelData\Concerns\EmptyData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\ValidateableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\AppendableData as AppendableDataContract;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
use Spatie\LaravelData\Contracts\EmptyData as EmptyDataContract;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Contracts\ResponsableData as ResponsableDataContract;
use Spatie\LaravelData\Contracts\TransformableData as TransformableDataContract;
use Spatie\LaravelData\Contracts\ValidateableData as ValidateableDataContract;
use Spatie\LaravelData\Contracts\WrappableData as WrappableDataContract;

abstract class Data implements Responsable, AppendableDataContract, BaseDataContract, TransformableDataContract, IncludeableDataContract, ResponsableDataContract, ValidateableDataContract, WrappableDataContract, EmptyDataContract
{
    use ResponsableData;
    use IncludeableData;
    use AppendableData;
    use ValidateableData;
    use WrappableData;
    use TransformableData;
    use BaseData;
    use EmptyData;
    use ContextableData;
}
```

These traits and interfaces allow you to create your own versions of the base `Data` class, and add your own functionality to it.

An example of such custom base data classes are the `Resource` and `Dto` class.

Each interface (and corresponding trait) provides a piece of functionality:

- **BaseData** provides the base functionality of the data package to create data objects
- **BaseDataCollectable** provides the base functionality of the data package to create data collections
- **ContextableData** provides the functionality to add context for includes and wraps to the data object/collectable
- **IncludeableData** provides the functionality to add includes, excludes, only and except to the data object/collectable
- **TransformableData** provides the functionality to transform the data object/collectable
- **ResponsableData** provides the functionality to return the data object/collectable as a response
- **WrappableData** provides the functionality to wrap the transformed data object/collectable
- **AppendableData** provides the functionality to append data to the transformed data payload
- **EmptyData** provides the functionality to get an empty version of the data object
- **ValidateableData** provides the functionality to validate the data object
- **DeprecatableData** provides the functionality to add deprecated functionality to the data object
`````

## File: advanced-usage/typescript.md
`````markdown
---
title: Transforming to TypeScript
weight: 2
---

Thanks to the [typescript-transformer](https://spatie.be/docs/typescript-transformer) package, it is possible to
automatically transform data objects into TypeScript definitions.

For example, the following data object:

```php
class DataObject extends Data
{
    public function __construct(
        public null|int $nullable,
        public int $int,
        public bool $bool,
        public string $string,
        public float $float,
        /** @var string[] */
        public array $array,
        public Lazy|string $lazy,
        public Optional|string $optional,
        public SimpleData $simpleData,
        /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
        public DataCollection $dataCollection,
    )
    {
    }
}
```

... can be transformed to the following TypeScript type:

```tsx
{
    nullable: number | null;
    int: number;
    bool: boolean;
    string: string;
    float: number;
    array: Array<string>;
    lazy? : string;
    optional? : string;
    simpleData: SimpleData;
    dataCollection: Array<SimpleData>;
}
```

## Installation of extra package

First, you must install the spatie/laravel-typescript-transformer into your project.

```bash
composer require spatie/laravel-typescript-transformer
```

Next, publish the config file of the typescript-transformer package with:

```bash
php artisan vendor:publish --tag=typescript-transformer-config
```

Finally, add the `Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer` transformer to the
transformers in the `typescript-transformer.php` config file. 

If you're using the `DtoTransformer` provided by the package, then be sure to put the `DataTypeScriptTransformer` before the `DtoTransformer`.

## Usage

Annotate each data object that you want to transform to Typescript with a `/** @typescript */` annotation or
a `#[TypeScript]` attribute.

To [generate the typescript file](https://spatie.be/docs/typescript-transformer/v4/laravel/executing-the-transform-command)
, run this command:

```php
php artisan typescript:transform
```

If you want to transform all the data objects within your application to TypeScript, you can use
the `DataTypeScriptCollector`, which should be added to the collectors in `typescript-transformer.php`.

If you're using the `DefaultCollector` provided by the package, then be sure to put the `DataTypeScriptCollector` before the `DefaultCollector`.

### Optional types

An optional or lazy property will automatically be transformed into an optional type within TypeScript:

```php
class DataObject extends Data
{
    public function __construct(
        public Lazy|string $lazy,
        public Optional|string $optional,
    )
    {
    }
}
```

This will be transformed into:

```tsx
{
    lazy? : string;
    optional? : string;
}
```

If you want to have optional typed properties in TypeScript without typing your properties optional or lazy within PHP,
then you can use the `Optional` attribute from the `typescript-transformer` package.

Don't forget to alias it as `TypeScriptOptional` when you're already using this package's `Optional` type!

```php
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;

class DataObject extends Data
{
    public function __construct(
        #[TypeScriptOptional]
        public int $id,
        public string $someString,
        public Optional|string $optional,
    )
    {
    }
}
```
`````

## File: advanced-usage/use-with-inertia.md
`````markdown
---
title: Use with Inertia
weight: 9
---

> Inertia.js lets you quickly build modern single-page React, Vue, and Svelte apps using classic server-side routing and controllers.

Laravel Data works excellent with [Inertia](https://inertiajs.com).

You can pass a complete data object to an Inertia response:

```php
return Inertia::render('Song', SongsData::from($song));
```

## Lazy properties

This package supports [lazy](https://spatie.be/docs/laravel-data/v4/as-a-resource/lazy-properties) properties, which can be manually included or excluded.

Inertia has a similar concept called [lazy data evaluation](https://inertiajs.com/partial-reloads#lazy-data-evaluation), where some properties wrapped in a closure only get evaluated and included in the response when explicitly asked.
Inertia v2 introduced the concept of [deferred props](https://inertiajs.com/deferred-props), which allows to defer the loading of certain data until after the initial page render.

This package can output specific properties as Inertia lazy or deferred props as such:

```php
class SongData extends Data
{
    public function __construct(
        public Lazy|string $title,
        public Lazy|string $artist,
        public Lazy|string $lyrics,
    ) {
    }

    public static function fromModel(Song $song): self
    {
        return new self(
            Lazy::inertia(fn() => $song->title),
            Lazy::closure(fn() => $song->artist)
            Lazy::inertiaDeferred(fn() => $song->lyrics)
        );
    }
}
```

We provide three kinds of lazy properties:

- **Lazy::inertia()** Never included on first visit, optionally included on partial reloads
- **Lazy::closure()** Always included on first visit, optionally included on partial reloads
- **Lazy::inertiaDeferred()** Included when ready, optionally included on partial reloads

Now within your JavaScript code, you can include the properties as such:

```js
router.reload((url, {
    only: ['title'],
});
```

### Auto lazy Inertia properties

We already saw earlier that the package can automatically make properties Lazy, the same can be done for Inertia properties.

It is possible to rewrite the previous example as follows:

```php
use Spatie\LaravelData\Attributes\AutoClosureLazy;
use Spatie\LaravelData\Attributes\AutoInertiaLazy;
use Spatie\LaravelData\Attributes\AutoInertiaDeferred;

class SongData extends Data
{
    public function __construct(
        #[AutoInertiaLazy]
        public Lazy|string $title,
        #[AutoClosureLazy]
        public Lazy|string $artist,
        #[AutoInertiaDeferred]
        public Lazy|string $lyrics,
    ) {
    }
}
```

If all the properties of a class should be either Inertia or closure lazy, you can use the attributes on the class level:

```php
#[AutoInertiaLazy]
class SongData extends Data
{
    public function __construct(
        public Lazy|string $title,
        public Lazy|string $artist,
    ) {
    }
}
```
`````

## File: advanced-usage/use-with-livewire.md
`````markdown
---
title: Use with Livewire
weight: 10
---

> Livewire is a full-stack framework for Laravel that makes building dynamic interfaces simple without leaving the
> comfort of Laravel.

Laravel Data works excellently with [Laravel Livewire](https://laravel-livewire.com).

You can use a data object as one of the properties of your Livewire component as such:

```php
class Song extends Component
{
    public SongData $song;

    public function mount(int $id)
    {
        $this->song = SongData::from(Song::findOrFail($id));
    }

    public function render()
    {
        return view('livewire.song');
    }
}
```

A few things are required to make this work:

1) You should implement `Wireable` on all the data classes you'll be using with Livewire
2) Each of these classes should also use the `WireableData` trait provided by this package
3) That's it

We can update our previous example to make it work with Livewire as such:

```php
class SongData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

## Livewire Synths (Experimental)

Laravel Data also provides a way to use Livewire Synths with your data objects. It will allow you to use data objects
and collections
without the need to make them Wireable. This is an experimental feature and is subject to change.

You can enable this feature by setting the config option in `data.php`:

```php
'livewire' => [
    'enable_synths' => false,
]
```

Once enabled, you can use data objects within your Livewire components without the need to make them Wireable:

```php
class SongUpdateComponent extends Component
{
    public SongData $data;

    public function mount(public int $id): void
    {
        $this->data = SongData::from(Song::findOrFail($id));
    }

    public function save(): void
    {
        Artist::findOrFail($this->id)->update($this->data->toArray());
    }

    public function render(): string
    {
        return <<<'BLADE'
        <div>
            <h1>Songs</h1>
            <input type="text" wire:model.live="data.title">
            <input type="text" wire:model.live="data.artist">
            <p>Title: {{ $data->title }}</p>
            <p>Artist: {{ $data->artist }}</p>
            <button wire:click="save">Save</button>
        </div>
        BLADE;
    }
}
```

### Lazy

It is possible to use Lazy properties, these properties will not be sent over the wire unless they're included. **Always
include properties permanently** because a data object is being transformed and then cast again between Livewire
requests the includes should be permanent.

It is possible to query lazy nested data objects, it is however not possible to query lazy properties which are not a data:

```php
use Spatie\LaravelData\Lazy;

class LazySongData extends Data
{
    public function __construct(
        public Lazy|ArtistData $artist,
        public Lazy|string $title,
    ) {}    
}
```

Within your Livewire view

```php
$this->data->artist->name; // Works
$this->data->title; // Does not work
```

### Validation

Laravel data **does not provide validation** when using Livewire, you should do this yourself! This is because laravel-data
does not support object validation at the moment. Only validating payloads which eventually become data objects.
The validation could technically happen when hydrating the data object, but this is not implemented 
because we cannot guarantee that every hydration happens when a user made sure the data is valid
and thus the payload should be validated.
`````

## File: advanced-usage/validation-attributes.md
`````markdown
---
title: Validation attributes
weight: 14
---

These are all the validation attributes currently available in laravel-data.

## Accepted

[Docs](https://laravel.com/docs/validation#rule-accepted)

```php
#[Accepted]
public bool $closure; 
```

## AcceptedIf

[Docs](https://laravel.com/docs/validation#rule-accepted-if)

```php
#[AcceptedIf('other_field', 'equals_this')]
public bool $closure; 
```

## ActiveUrl

[Docs](https://laravel.com/docs/validation#rule-active-url)

```php
#[ActiveUrl]
public string $closure; 
```

## After

[Docs](https://laravel.com/docs/validation#rule-after)

```php
#[After('tomorrow')]
public Carbon $closure; 

#[After(Carbon::yesterday())]
public Carbon $closure; 

// Always use field references when referencing other fields
#[After(new FieldReference('other_field'))]
public Carbon $closure; 
```

## AfterOrEqual

[Docs](https://laravel.com/docs/validation#rule-after-or-equal)

```php
#[AfterOrEqual('tomorrow')]
public Carbon $closure; 

#[AfterOrEqual(Carbon::yesterday())]
public Carbon $closure; 

// Always use field references when referencing other fields
#[AfterOrEqual(new FieldReference('other_field'))]
public Carbon $closure; 
```

## Alpha

[Docs](https://laravel.com/docs/validation#rule-alpha)

```php
#[Alpha]
public string $closure; 
```

## AlphaDash

[Docs](https://laravel.com/docs/validation#rule-alpha-dash)

```php
#[AlphaDash]
public string $closure; 
```

## AlphaNumeric

[Docs](https://laravel.com/docs/validation#rule-alpha-num)

```php
#[AlphaNumeric]
public string $closure; 
```

## ArrayType

[Docs](https://laravel.com/docs/validation#rule-array)

```php
#[ArrayType]
public array $closure; 

#[ArrayType(['valid_key', 'other_valid_key'])]
public array $closure; 

#[ArrayType('valid_key', 'other_valid_key')]
public array $closure; 
```

## Bail

[Docs](https://laravel.com/docs/validation#rule-bail)

```php
#[Bail]
public string $closure; 
```

## Before

[Docs](https://laravel.com/docs/validation#rule-before)

```php
#[Before('tomorrow')]
public Carbon $closure; 

#[Before(Carbon::yesterday())]
public Carbon $closure; 

// Always use field references when referencing other fields
#[Before(new FieldReference('other_field'))]
public Carbon $closure; 
```

## BeforeOrEqual

[Docs](https://laravel.com/docs/validation#rule-before-or-equal)

```php
#[BeforeOrEqual('tomorrow')]
public Carbon $closure; 

#[BeforeOrEqual(Carbon::yesterday())]
public Carbon $closure; 

// Always use field references when referencing other fields
#[BeforeOrEqual(new FieldReference('other_field'))]
public Carbon $closure; 
```

## Between

[Docs](https://laravel.com/docs/validation#rule-between)

```php
#[Between(3.14, 42)]
public int $closure; 
```

## BooleanType

[Docs](https://laravel.com/docs/validation#rule-boolean)

```php
#[BooleanType]
public bool $closure; 
```

## Confirmed

[Docs](https://laravel.com/docs/validation#rule-confirmed)

```php
#[Confirmed]
public string $closure; 
```

## CurrentPassword

[Docs](https://laravel.com/docs/validation#rule-current-password)

```php
#[CurrentPassword]
public string $closure; 

#[CurrentPassword('api')]
public string $closure; 
```

## Date

[Docs](https://laravel.com/docs/validation#rule-date)

```php
#[Date]
public Carbon $date; 
```

## DateEquals

[Docs](https://laravel.com/docs/validation#rule-date-equals)

```php
#[DateEquals('tomorrow')]
public Carbon $date; 

#[DateEquals(Carbon::yesterday())]
public Carbon $date; 
```

## DateFormat

[Docs](https://laravel.com/docs/validation#rule-date-format)

```php
#[DateFormat('d-m-Y')]
public Carbon $date;

#[DateFormat(['Y-m-d', 'Y-m-d H:i:s'])]
public Carbon $date;  
```

## Declined

[Docs](https://laravel.com/docs/validation#rule-declined)

```php
#[Declined]
public bool $closure; 
```

## DeclinedIf

[Docs](https://laravel.com/docs/validation#rule-declined-if)

```php
#[DeclinedIf('other_field', 'equals_this')]
public bool $closure; 
```

## Different

[Docs](https://laravel.com/docs/validation#rule-different)

```php
#[Different('other_field')]
public string $closure; 
```

## Digits

[Docs](https://laravel.com/docs/validation#rule-digits)

```php
#[Digits(10)]
public int $closure; 
```

## DigitsBetween

[Docs](https://laravel.com/docs/validation#rule-digits-between)

```php
#[DigitsBetween(2, 10)]
public int $closure; 
```

## Dimensions

[Docs](https://laravel.com/docs/validation#rule-dimensions)

```php
#[Dimensions(ratio: 1.5)]
public UploadedFile $closure; 

#[Dimensions(maxWidth: 100, maxHeight: 100)]
public UploadedFile $closure; 
```

## Distinct

[Docs](https://laravel.com/docs/validation#rule-distinct)

```php
#[Distinct]
public string $closure;

#[Distinct(Distinct::Strict)]
public string $closure;  

#[Distinct(Distinct::IgnoreCase)]
public string $closure;  
```

## DoesntEndWith

[Docs](https://laravel.com/docs/validation#rule-doesnt-end-with)

```php
#[DoesntEndWith('a')]
public string $closure;

#[DoesntEndWith(['a', 'b'])]
public string $closure;

#[DoesntEndWith('a', 'b')]
public string $closure;
```

## DoesntStartWith

[Docs](https://laravel.com/docs/validation#rule-doesnt-start-with)

```php
#[DoesntStartWith('a')]
public string $closure;

#[DoesntStartWith(['a', 'b'])]
public string $closure;

#[DoesntStartWith('a', 'b')]
public string $closure;
```

## Email

[Docs](https://laravel.com/docs/validation#rule-email)

```php
#[Email]
public string $closure;

#[Email(Email::RfcValidation)]
public string $closure;  

#[Email([Email::RfcValidation, Email::DnsCheckValidation])]
public string $closure;  

#[Email(Email::RfcValidation, Email::DnsCheckValidation)]
public string $closure;  
```

## EndsWith

[Docs](https://laravel.com/docs/validation#rule-ends-with)

```php
#[EndsWith('a')]
public string $closure;

#[EndsWith(['a', 'b'])]
public string $closure;

#[EndsWith('a', 'b')]
public string $closure;
```

## Enum

[Docs](https://laravel.com/docs/validation#rule-enum)

```php
#[Enum(ChannelType::class)]
public string $closure;

#[Enum(ChannelType::class, only: [ChannelType::Email])]
public string $closure;

#[Enum(ChannelType::class, except: [ChannelType::Email])]
public string $closure;
```

## ExcludeIf

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/validation#rule-exclude-if)

```php
#[ExcludeIf('other_field', 'has_value')]
public string $closure;
```

## ExcludeUnless

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/validation#rule-exclude-unless)

```php
#[ExcludeUnless('other_field', 'has_value')]
public string $closure;
```

## ExcludeWith

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/validation#rule-exclude-with)

```php
#[ExcludeWith('other_field')]
public string $closure;
```

## ExcludeWithout

*At the moment the data is not yet excluded due to technical reasons, v4 should fix this*

[Docs](https://laravel.com/docs/validation#rule-exclude-without)

```php
#[ExcludeWithout('other_field')]
public string $closure;
```

## Exists

[Docs](https://laravel.com/docs/validation#rule-exists)

```php
#[Exists('users')]
public string $closure; 

#[Exists(User::class)]
public string $closure; 

#[Exists('users', 'email')]
public string $closure;

#[Exists('users', 'email', connection: 'tenant')]
public string $closure;

#[Exists('users', 'email', withoutTrashed: true)]
public string $closure;
```

## File

[Docs](https://laravel.com/docs/validation#rule-file)

```php
#[File]
public UploadedFile $closure; 
```

## Filled

[Docs](https://laravel.com/docs/validation#rule-filled)

```php
#[Filled]
public string $closure; 
```

## GreaterThan

[Docs](https://laravel.com/docs/validation#rule-gt)

```php
#[GreaterThan('other_field')]
public int $closure; 
```

## GreaterThanOrEqualTo

[Docs](https://laravel.com/docs/validation#rule-gte)

```php
#[GreaterThanOrEqualTo('other_field')]
public int $closure; 
```

## Image

[Docs](https://laravel.com/docs/validation#rule-image)

```php
#[Image]
public UploadedFile $closure; 
```

## In

[Docs](https://laravel.com/docs/validation#rule-in)

```php
#[In([1, 2, 3, 'a', 'b'])]
public mixed $closure; 

#[In(1, 2, 3, 'a', 'b')]
public mixed $closure; 
```

## InArray

[Docs](https://laravel.com/docs/validation#rule-in-array)

```php
#[InArray('other_field')]
public string $closure; 
```

## IntegerType

[Docs](https://laravel.com/docs/validation#rule-integer)

```php
#[IntegerType]
public int $closure; 
```

## IP

[Docs](https://laravel.com/docs/validation#rule-ip)

```php
#[IP]
public string $closure; 
```

## IPv4

[Docs](https://laravel.com/docs/validation#ipv4)

```php
#[IPv4]
public string $closure; 
```

## IPv6

[Docs](https://laravel.com/docs/validation#ipv6)

```php
#[IPv6]
public string $closure; 
```

## Json

[Docs](https://laravel.com/docs/validation#rule-json)

```php
#[Json]
public string $closure; 
```

## LessThan

[Docs](https://laravel.com/docs/validation#rule-lt)

```php
#[LessThan('other_field')]
public int $closure; 
```

## LessThanOrEqualTo

[Docs](https://laravel.com/docs/validation#rule-lte)

```php
#[LessThanOrEqualTo('other_field')]
public int $closure; 
```

## Lowercase

[Docs](https://laravel.com/docs/validation#rule-lowercase)

```php
#[Lowercase]
public string $closure; 
```

## ListType

[Docs](https://laravel.com/docs/validation#rule-list)

```php
#[ListType]
public array $array; 
```

## MacAddress

[Docs](https://laravel.com/docs/validation#rule-mac)

```php
#[MacAddress]
public string $closure; 
```

## Max

[Docs](https://laravel.com/docs/validation#rule-max)

```php
#[Max(20)]
public int $closure; 
```

## MaxDigits

[Docs](https://laravel.com/docs/validation#rule-max-digits)

```php
#[MaxDigits(10)]
public int $closure; 
```

## MimeTypes

[Docs](https://laravel.com/docs/validation#rule-mimetypes)

```php
#[MimeTypes('video/quicktime')]
public UploadedFile $closure; 

#[MimeTypes(['video/quicktime', 'video/avi'])]
public UploadedFile $closure; 

#[MimeTypes('video/quicktime', 'video/avi')]
public UploadedFile $closure; 
```

## Mimes

[Docs](https://laravel.com/docs/validation#rule-mimes)

```php
#[Mimes('jpg')]
public UploadedFile $closure; 

#[Mimes(['jpg', 'png'])]
public UploadedFile $closure; 

#[Mimes('jpg', 'png')]
public UploadedFile $closure; 
```

## Min

[Docs](https://laravel.com/docs/validation#rule-min)

```php
#[Min(20)]
public int $closure; 
```

## MinDigits

[Docs](https://laravel.com/docs/validation#rule-min-digits)

```php
#[MinDigits(2)]
public int $closure; 
```

## MultipleOf

[Docs](https://laravel.com/docs/validation#rule-multiple-of)

```php
#[MultipleOf(3)]
public int $closure; 
```

## NotIn

[Docs](https://laravel.com/docs/validation#rule-not-in)

```php
#[NotIn([1, 2, 3, 'a', 'b'])]
public mixed $closure; 

#[NotIn(1, 2, 3, 'a', 'b')]
public mixed $closure; 
```

## NotRegex

[Docs](https://laravel.com/docs/validation#rule-not-regex)

```php
#[NotRegex('/^.+$/i')]
public string $closure; 
```

## Nullable

[Docs](https://laravel.com/docs/validation#rule-nullable)

```php
#[Nullable]
public ?string $closure; 
```

## Numeric

[Docs](https://laravel.com/docs/validation#rule-numeric)

```php
#[Numeric]
public ?string $closure; 
```

## Password

[Docs](https://laravel.com/docs/validation#rule-password)

```php
#[Password(min: 12, letters: true, mixedCase: true, numbers: false, symbols: false, uncompromised: true, uncompromisedThreshold: 0)]
public string $closure; 
```

## Present

[Docs](https://laravel.com/docs/validation#rule-present)

```php
#[Present]
public string $closure; 
```

## Prohibited

[Docs](https://laravel.com/docs/validation#rule-prohibited)

```php
#[Prohibited]
public ?string $closure; 
```

## ProhibitedIf

[Docs](https://laravel.com/docs/validation#rule-prohibited-if)

```php
#[ProhibitedIf('other_field', 'has_value')]
public ?string $closure; 

#[ProhibitedIf('other_field', ['has_value', 'or_this_value'])]
public ?string $closure; 
```

## ProhibitedUnless

[Docs](https://laravel.com/docs/validation#rule-prohibited-unless)

```php
#[ProhibitedUnless('other_field', 'has_value')]
public ?string $closure; 

#[ProhibitedUnless('other_field', ['has_value', 'or_this_value'])]
public ?string $closure; 
```

## Prohibits

[Docs](https://laravel.com/docs/validation#rule-prohibits)

```php
#[Prohibits('other_field')]
public ?string $closure; 

#[Prohibits(['other_field', 'another_field'])]
public ?string $closure; 

#[Prohibits('other_field', 'another_field')]
public ?string $closure; 
```

## Regex

[Docs](https://laravel.com/docs/validation#rule-regex)

```php
#[Regex('/^.+$/i')]
public string $closure; 
```

## Required

[Docs](https://laravel.com/docs/validation#rule-required)

```php
#[Required]
public string $closure; 
```

## RequiredIf

[Docs](https://laravel.com/docs/validation#rule-required-if)

```php
#[RequiredIf('other_field', 'value')]
public ?string $closure; 

#[RequiredIf('other_field', ['value', 'another_value'])]
public ?string $closure; 
```

## RequiredUnless

[Docs](https://laravel.com/docs/validation#rule-required-unless)

```php
#[RequiredUnless('other_field', 'value')]
public ?string $closure; 

#[RequiredUnless('other_field', ['value', 'another_value'])]
public ?string $closure; 
```

## RequiredWith

[Docs](https://laravel.com/docs/validation#rule-required-with)

```php
#[RequiredWith('other_field')]
public ?string $closure; 

#[RequiredWith(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWith('other_field', 'another_field')]
public ?string $closure; 
```

## RequiredWithAll

[Docs](https://laravel.com/docs/validation#rule-required-with-all)

```php
#[RequiredWithAll('other_field')]
public ?string $closure; 

#[RequiredWithAll(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWithAll('other_field', 'another_field')]
public ?string $closure; 
```

## RequiredWithout

[Docs](https://laravel.com/docs/validation#rule-required-without)

```php
#[RequiredWithout('other_field')]
public ?string $closure; 

#[RequiredWithout(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWithout('other_field', 'another_field')]
public ?string $closure; 
```

## RequiredWithoutAll

[Docs](https://laravel.com/docs/validation#rule-required-without-all)

```php
#[RequiredWithoutAll('other_field')]
public ?string $closure; 

#[RequiredWithoutAll(['other_field', 'another_field'])]
public ?string $closure; 

#[RequiredWithoutAll('other_field', 'another_field')]
public ?string $closure; 
```

## RequiredArrayKeys

[Docs](https://laravel.com/docs/validation#rule-required-array-keys)

```php
#[RequiredArrayKeys('a')]
public array $closure;

#[RequiredArrayKeys(['a', 'b'])]
public array $closure;

#[RequiredArrayKeys('a', 'b')]
public array $closure;
```

## Rule

```php
#[Rule('string|uuid')]
public string $closure; 

#[Rule(['string','uuid'])]
public string $closure; 
```

## Same

[Docs](https://laravel.com/docs/validation#rule-same)

```php
#[Same('other_field')]
public string $closure; 
```

## Size

[Docs](https://laravel.com/docs/validation#rule-size)

```php
#[Size(10)]
public string $closure; 
```

## Sometimes

[Docs](https://laravel.com/docs/validation#validating-when-present)

```php
#[Sometimes]
public string $closure; 
```

## StartsWith

[Docs](https://laravel.com/docs/validation#rule-starts-with)

```php
#[StartsWith('a')]
public string $closure;

#[StartsWith(['a', 'b'])]
public string $closure;

#[StartsWith('a', 'b')]
public string $closure;
```

## StringType

[Docs](https://laravel.com/docs/validation#rule-string)

```php
#[StringType()]
public string $closure; 
```

## TimeZone

[Docs](https://laravel.com/docs/validation#rule-timezone)

```php
#[TimeZone()]
public string $closure; 
```

## Unique

[Docs](https://laravel.com/docs/validation#rule-unique)

```php
#[Unique('users')]
public string $closure; 

#[Unique(User::class)]
public string $closure; 

#[Unique('users', 'email')]
public string $closure;

#[Unique('users', connection: 'tenant')]
public string $closure;

#[Unique('users', withoutTrashed: true)]
public string $closure;

#[Unique('users', ignore: 5)]
public string $closure;

#[Unique('users', ignore: new AuthenticatedUserReference())]
public string $closure;

#[Unique('posts', ignore: new RouteParameterReference('post'))]
public string $closure;
```

## Uppercase

[Docs](https://laravel.com/docs/validation#rule-uppercase)

```php
#[Uppercase]
public string $closure; 
```

## Url

[Docs](https://laravel.com/docs/validation#rule-url)

```php
#[Url]
public string $closure; 
```

## Ulid

[Docs](https://laravel.com/docs/validation#rule-ulid)

```php
#[Ulid]
public string $closure; 
```

## Uuid

[Docs](https://laravel.com/docs/validation#rule-uuid)

```php
#[Uuid]
public string $closure; 
```
`````

## File: advanced-usage/working-with-dates.md
`````markdown
---
title: Working with dates
weight: 3
---

Dates can be hard, there are tons of formats to cast them from or transform them to. Within the `data.php` config file a
default date format can be set:

```php
    'date_format' => DATE_ATOM,
```

Now when using the `DateTimeInterfaceCast` or `DateTimeInterfaceTransformer` the format defined will be used

```php
#[WithCast(DateTimeInterfaceCast::class)]
#[WithTransformer(DateTimeInterfaceTransformer::class)]
public DateTime $date
```

It is also possible to manually set the format;

```php
#[WithCast(DateTimeInterfaceCast::class, format: DATE_ATOM)]
#[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
public DateTime $date
```

When casting the data object will use the type of the property to cast a date string into, so if you want to
use `Carbon`, that's perfectly possible:

```php
#[WithCast(DateTimeInterfaceCast::class)]
public Carbon $date
```

You can even manually specify the type the date string should be cast to:

```php

#[WithCast(DateTimeInterfaceCast::class, type: CarbonImmutable::class)]
public $date
```

## Multiple date formats

Sometimes your application might use different date formats, for example, you receive dates from an IOS and React
application. These use different underlying date formats. In such case you can add an array to the `date_format` key
within the `data.php` config file:

```php
    'date_format' => [DATE_ATOM, 'Y-m-d'],
```

Now when casting a date, a valid format will be searched. When none can be found, an exception is thrown.

When a transformers hasn't explicitly stated its format, the first format within the array is used.

## Casting dates in a different time zone

Sometimes a date can be in a different timezone than the timezone you application uses. For example, if your application uses `Europe/Brussels` but your date is in `UTC`:

```php
#[WithCast(DateTimeInterfaceCast::class, timeZone: 'UTC')]
public DateTime $date
```

The date will be created with the `UTC` timezone but will be the same as in the `Europe/Brussels` timezone.

## Changing time zones

When casting a date you may want to set an alternative timezone this can be achieved as such:

```php
#[WithCast(DateTimeInterfaceCast::class, setTimeZone: 'Europe/Brussels')]
public DateTime $date
```

In this case the time will be transformed, if our original time was in `UTC` then one or two hours (depending on summer time) will be added.

You can also change the timezone of a property which is getting transformed:

```php
#[WithTransformer(DateTimeInterfaceTransformer::class, setTimeZone: 'Europe/Brussels')]
public DateTime $date
```
`````

## File: as-a-data-transfer-object/_index.md
`````markdown
---
title: As a DTO
weight: 2
---
`````

## File: as-a-data-transfer-object/abstract-data.md
`````markdown
---
title: Abstract Data
weight: 4
---

It is possible to create an abstract data class with subclasses extending it:

```php
abstract class Person extends Data
{
    public string $name;
}

class Singer extends Person
{
   public function __construct(
        public string $voice,
   ) {}
}

class Musician extends Person
{
   public function __construct(
        public string $instrument,
   ) {}
}
```

It is perfectly possible now to create individual instances as follows:

```php
Singer::from(['name' => 'Rick Astley', 'voice' => 'tenor']);
Musician::from(['name' => 'Rick Astley', 'instrument' => 'guitar']);
```

But what if you want to use this abstract type in another data class like this:

```php
class Contract extends Data
{
    public string $label;
    public Person $artist;
}
```

While the following may both be valid:

```php
Contract::from(['label' => 'PIAS', 'artist' => ['name' => 'Rick Astley', 'voice' => 'tenor']]);
Contract::from(['label' => 'PIAS', 'artist' => ['name' => 'Rick Astley', 'instrument' => 'guitar']]);
```

The package can't decide which subclass to construct for the property.

You can implement the `PropertyMorphableData` interface on the abstract class to solve this. This interface adds a `morph` method that will be used to determine which subclass to use. The `morph` method receives an array of properties limited to properties tagged by a  `PropertyForMorph` attribute.

```php
use Spatie\LaravelData\Attributes\PropertyForMorph;
use Spatie\LaravelData\Contracts\PropertyMorphableData;

abstract class Person extends Data implements PropertyMorphableData
{
    #[PropertyForMorph]
    public string $type;

    public string $name;
    
    public static function morph(array $properties): ?string
    {
        return match ($properties['type']) {
            'singer' => Singer::class,
            'musician' => Musician::class,
            default => null
        };
    }
}
```

The example above will work by adding this code, and the correct Data class will be constructed.

Since the morph functionality needs to run early within the data construction process, it bypasses the normal flow of constructing data objects so there are a few limitations:

- it is only allowed to use properties typed as string, int, or BackedEnum(int or string)
- When a property is typed as an enum, the value passed to the morph method will be an enum
- it can be that the value of a property within the morph method is null or a different type than expected since it runs before validation
- properties with mapped property names are still supported

It is also possible to use abstract data classes as collections as such:

```php
class Band extends Data
{
    public string $name;
    
    /**  @var array<Person> */
    public array $members;
}
```
`````

## File: as-a-data-transfer-object/casts.md
`````markdown
---
title: Casts
weight: 5
---

We extend our example data object just a little bit:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
        public DateTime $date,
        public Format $format,
    ) {
    }
}
```

The `Format` property here is an `Enum` and looks like this:

```php
enum Format: string {
    case cd = 'cd';
    case vinyl = 'vinyl';
    case cassette = 'cassette';
}
```

When we now try to construct a data object like this:

```php
SongData::from([
    'title' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'date' => '27-07-1987',
    'format' => 'vinyl',
]);
```

And get an error because the first two properties are simple PHP types(strings, ints, floats, booleans, arrays), but the following two properties are more complex types: `DateTime` and `Enum`, respectively.

These types cannot be automatically created. A cast is needed to construct them from a string.

There are two types of casts, local and global casts.

## Local casts

Local casts are defined within the data object itself and can be added using attributes:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
        #[WithCast(DateTimeInterfaceCast::class)]
        public DateTime $date,
        #[WithCast(EnumCast::class)]
        public Format $format,
    ) {
    }
}
```

Now it is possible to create a data object like this without exceptions:

```php
SongData::from([
    'title' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'date' => '27-07-1987',
    'format' => 'vinyl',
]);
```

It is possible to provide parameters to the casts like this:

```php
#[WithCast(EnumCast::class, type: Format::class)]
public Format $format
```

## Global casts

Global casts are not defined on the data object but in your `data.php` config file:

```php
'casts' => [
    DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
],
```

When the data object can find no local cast for the property, the package will look through the global casts and tries to find a suitable cast. You can define casts for:

- a **specific implementation** (e.g. CarbonImmutable)
- an **interface** (e.g. DateTimeInterface)
- a **base class** (e.g. Enum)

As you can see, the package by default already provides a `DateTimeInterface` cast, this means we can update our data object like this:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
        public DateTime $date,
        #[WithCast(EnumCast::class)]
        public Format $format,
    ) {
    }
}
```

Tip: we can also remove the `EnumCast` since the package will automatically cast enums because they're a native PHP type, but this made the example easy to understand.

## Creating your own casts

It is possible to create your casts. You can read more about this in the [advanced chapter](/docs/laravel-data/v4/advanced-usage/creating-a-cast).

## Casting arrays or collections of non-data types

We've already seen how collections of data can be made of data objects, the same is true for all other types if correctly
typed.

Let say we have an array of DateTime objects:

```php
class ReleaseData extends Data
{
    public string $title;
    /** @var array<int, DateTime> */
    public array $releaseDates;
}
```

By enabling the `cast_and_transform_iterables` feature in the `data` config file (this feature will be enabled by default in laravel-data v5):

```php
'features' => [
    'cast_and_transform_iterables' => true,
],
```

We now can create a `ReleaseData` object with an array of strings which will be cast into an array DateTime objects:

```php
ReleaseData::from([
    'title' => 'Never Gonna Give You Up',
    'releaseDates' => [
        '1987-07-27T12:00:00Z',
        '1987-07-28T12:00:00Z',
        '1987-07-29T12:00:00Z',
    ],
]);
```

For this feature to work, a cast should not only implement the `Cast` interface but also the `IterableItemCast`. The
signatures of the `cast` and `castIterableItem` methods are exactly the same, but they're called on different times.
When casting a property like a DateTime from a string, the `cast` method will be used, when transforming an iterable
property like an array or Laravel Collection where the iterable item is typed using an annotation, then each item of the
provided iterable will trigger a call to the `castIterableItem` method.
`````

## File: as-a-data-transfer-object/collections.md
`````markdown
---
title: Collections
weight: 3
---

It is possible to create a collection of data objects by using the `collect` method:

```php
SongData::collect([
    ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
    ['title' => 'Giving Up on Love', 'artist' => 'Rick Astley'],
]); // returns an array of SongData objects
```

Whatever type of collection you pass in, the package will return the same type of collection with the freshly created
data objects within it. As long as this type is an array, Laravel collection or paginator or a class extending from it.

This opens up possibilities to create collections of Eloquent models:

```php
SongData::collect(Song::all()); // return an Eloquent collection of SongData objects
```

Or use a paginator:

```php
SongData::collect(Song::paginate()); // return a LengthAwarePaginator of SongData objects

// or

SongData::collect(Song::cursorPaginate()); // return a CursorPaginator of SongData objects
```

Internally the `from` method of the data class will be used to create a new data object for each item in the collection.

When the collection already contains data objects, the `collect` method will return the same collection:

```php
SongData::collect([
    SongData::from(['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley']),
    SongData::from(['title' => 'Giving Up on Love', 'artist' => 'Rick Astley']),
]); // returns an array of SongData objects
```

The collect method also allows you to cast collections from one type into another. For example, you can pass in
an `array`and get back a Laravel collection:

```php
SongData::collect($songs, Collection::class); // returns a Laravel collection of SongData objects
```

This transformation will only work with non-paginator collections.

## Magically creating collections

We've already seen that `from` can create data objects magically. It is also possible to create a collection of data
objects magically when using `collect`.

Let's say you've implemented a custom collection class called `SongCollection`:

```php
class SongCollection extends Collection
{
    public function __construct(
        $items = [],
        public array $artists = [],
    ) {
        parent::__construct($items);
    }
}
```

Since the constructor of this collection requires an extra property it cannot be created automatically. However, it is
possible to define a custom collect method which can create it:

```php
class SongData extends Data
{
    public string $title;
    public string $artist;

    public static function collectArray(array $items): SongCollection
    {
        return new SongCollection(
            parent::collect($items),
            array_unique(array_map(fn(SongData $song) => $song->artist, $items))
        );
    }
}
```

Now when collecting an array data objects a `SongCollection` will be returned:

```php
SongData::collectArray([
    ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
    ['title' => 'Living on a prayer', 'artist' => 'Bon Jovi'],
]); // returns an SongCollection of SongData objects
```

There are a few requirements for this to work:

- The method must be **static**
- The method must be **public**
- The method must have a **return type**
- The method name must **start with collect**
- The method name must not be **collect**

## Creating a data object with collections

You can create a data object with a collection of data objects just like you would create a data object with a nested
data object:

```php
use App\Data\SongData;
use Illuminate\Support\Collection;

class AlbumData extends Data
{    
    public string $title;
    /** @var Collection<int, SongData> */
    public Collection $songs;
}

AlbumData::from([
    'title' => 'Never Gonna Give You Up',
    'songs' => [
        ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
        ['title' => 'Giving Up on Love', 'artist' => 'Rick Astley'],
    ]
]);
```

Since the collection type here is a `Collection`, the package will automatically convert the array into a collection of
data objects.

## DataCollections, PaginatedDataCollections and CursorPaginatedCollections

The package also provides a few collection classes which can be used to create collections of data objects. It was a
requirement to use these classes in the past versions of the package when nesting data objects collections in data
objects. This is no longer the case, but there are still valid use cases for them.

You can create a DataCollection like this:

```php
use Spatie\LaravelData\DataCollection;

SongData::collect(Song::all(), DataCollection::class);
```

A PaginatedDataCollection can be created like this:

```php
use Spatie\LaravelData\PaginatedDataCollection;

SongData::collect(Song::paginate(), PaginatedDataCollection::class);
````

And a CursorPaginatedCollection can be created like this:

```php
use Spatie\LaravelData\CursorPaginatedCollection;

SongData::collect(Song::cursorPaginate(), CursorPaginatedCollection::class);
```

### Why using these collection classes?

We advise you to always use arrays, Laravel collections and paginators within your data objects. But let's say you have
a controller like this:

```php
class SongController
{
    public function index()
    {
        return SongData::collect(Song::all());    
    }
}
```

In the next chapters of this documentation, we'll see that it is possible to include or exclude properties from the data
objects like this:

```php
class SongController
{
    public function index()
    {
        return SongData::collect(Song::all(), DataCollection::class)->include('artist');    
    }
}
```

This will only work when you're using a `DataCollection`, `PaginatedDataCollection` or `CursorPaginatedCollection`.

### DataCollections

DataCollections provide some extra functionalities like:

```php
// Counting the amount of items in the collection
count($collection);

// Changing an item in the collection
$collection[0]->title = 'Giving Up on Love';

// Adding an item to the collection
$collection[] = SongData::from(['title' => 'Never Knew Love', 'artist' => 'Rick Astley']);

// Removing an item from the collection
unset($collection[0]);
```

It is even possible to loop over it with a foreach:

```php
foreach ($songs as $song){
    echo $song->title;
}
```

The `DataCollection` class implements a few of the Laravel collection methods:

- through
- map
- filter
- first
- each
- values
- where
- reduce
- sole

You can, for example, get the first item within a collection like this:

```php
SongData::collect(Song::all(), DataCollection::class)->first(); // SongData object
```

### The `collection` method

In previous versions of the package it was possible to use the `collection` method to create a collection of data
objects:

```php
SongData::collection(Song::all()); // returns a DataCollection of SongData objects
SongData::collection(Song::paginate()); // returns a PaginatedDataCollection of SongData objects
SongData::collection(Song::cursorPaginate()); // returns a CursorPaginatedCollection of SongData objects
```

This method was removed with version v4 of the package in favor for the more powerful `collect` method. The `collection`
method can still be used by using the `WithDeprecatedCollectionMethod` trait:

```php
use Spatie\LaravelData\Concerns\WithDeprecatedCollectionMethod;

class SongData extends Data
{
    use WithDeprecatedCollectionMethod;
    
    // ...
}
```

Please note that this trait will be removed in the next major version of the package.
`````

## File: as-a-data-transfer-object/computed.md
`````markdown
---
title: Computed values
weight: 9
---

Earlier we saw how default values can be set for a data object, sometimes you want to set a default value based on other properties. For example, you might want to set a `full_name` property based on a `first_name` and `last_name` property. You can do this by using a computed property:

```php
use Spatie\LaravelData\Attributes\Computed;

class SongData extends Data
{
    #[Computed]
    public string $full_name;

    public function __construct(
        public string $first_name,
        public string $last_name,
    ) {
        $this->full_name = "{$this->first_name} {$this->last_name}";
    }
}
```

You can now do the following:

```php
SongData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche']);
```

Please notice: the computed property won't be reevaluated when its dependencies change. If you want to update a computed property, you'll have to create a new object.

Again there are a few conditions for this approach:

- You must always use a sole property, a property within the constructor definition won't work
- Computed properties cannot be defined in the payload, a `CannotSetComputedValue` will be thrown if this is the case
- If the `ignore_exception_when_trying_to_set_computed_property_value` configuration option is set to `true`, the computed property will be silently ignored when trying to set it in the payload and no `CannotSetComputedValue` exception will be thrown.
`````

## File: as-a-data-transfer-object/creating-a-data-object.md
`````markdown
---
title: Creating a data object
weight: 1
---

Let's get started with the following simple data object:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

Since this is just a simple PHP object, it can be initialized as such:

```php
new SongData('Never gonna give you up', 'Rick Astley');
```

But with this package, you can initialize the data object also with an array:

```php
SongData::from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

You can use the `from` method to create a data object from nearly anything. For example, let's say you have an Eloquent
model like this:

```php
class Song extends Model
{
    // Your model code
}
```

You can create a data object from such a model like this:

```php
SongData::from(Song::firstOrFail($id));
```

The package will find the required properties within the model and use them to construct the data object.

Data can also be created from JSON strings:

```php
SongData::from('{"title" : "Never Gonna Give You Up","artist" : "Rick Astley"}');
```

Although the PHP 8.0 constructor properties look great in data objects, it is perfectly valid to use regular properties
without a constructor like so:

```php
class SongData extends Data
{
    public string $title;
    public string $artist;
}
```

## Magical creation

It is possible to overwrite or extend the behaviour of the `from` method for specific types. So you can construct a data
object in a specific manner for that type. This can be done by adding a static method starting with 'from' to the data
object.

For example, we want to change how we create a data object from a model. We can add a `fromModel` static method that
takes the model we want to use as a parameter:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function fromModel(Song $song): self
    {
        return new self("{$song->title} ({$song->year})", $song->artist);
    }
}
```

Now when creating a data object from a model like this:

```php
SongData::from(Song::firstOrFail($id));
```

Instead of the default method, the `fromModel` method will be called to create a data object from the found model.

You're truly free to add as many from methods as you want. For example, you could add one to create a data object from a
string:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function fromString(string $string): self
    {
        [$title, $artist] = explode('|', $string);
    
        return new self($title, $artist);
    }
}
```

From now on, you can create a data object like this:

```php
SongData::from('Never gonna give you up|Rick Astley');
```

It is also possible to use multiple arguments in a magical creation method:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function fromMultiple(string $title, string $artist): self
    {
        return new self($title, $artist);
    }
}
```

Now we can create the data object like this:

```php
SongData::from('Never gonna give you up', 'Rick Astley');
```

There are a few requirements to enable magical data object creation:

- The method must be **static and public**
- The method must **start with from**
- The method cannot be called **from**

When the package cannot find such a method for a type given to the data object's `from` method. Then the data object
will try to create itself from the following types:

- An *Eloquent model* by calling `toArray` on it
- A *Laravel request* by calling `all` on it
- An *Arrayable* by calling `toArray` on it
- An *array*

This list can be extended using extra normalizers, find more about
it [here](/docs/laravel-data/v4/advanced-usage/normalizers).

When a data object cannot be created using magical methods or the default methods, a `CannotCreateData`
exception will be thrown.

## Optional creation

It is impossible to return `null` from a data object's `from` method since we always expect a data object when
calling `from`. To solve this, you can call the `optional` method:

```php
SongData::optional(null); // returns null
```

Underneath the optional method will call the `from` method when a value is given, so you can still magically create data
objects. When a null value is given, it will return null.

## Creation without magic methods

You can ignore the magical creation methods when creating a data object as such:

```php
SongData::factory()->withoutMagicalCreation()->from($song);
```

## Advanced creation using factories

It is possible to configure how a data object is created, whether it will be validated, which casts to use and more. You
can read more about it [here](/docs/laravel-data/v4/as-a-data-transfer-object/factories).

## DTO classes

The default `Data` class from which you extend your data objects is a multi versatile class, it packs a lot of
functionality. But sometimes you just want a simple DTO class. You can use the `Dto` class for this:

```php
class SongData extends Dto
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

The `Dto` class is a data class in its most basic form. It can be created from anything using magical methods, can
validate payloads before creating the data object and can be created using factories. But it doesn't have any of the
other functionality that the `Data` class has.
`````

## File: as-a-data-transfer-object/defaults.md
`````markdown
---
title: Default values
weight: 8
---

There are a few ways to define default values for a data object. Since a data object is just a regular PHP class, you can use the constructor to set default values:

```php
class SongData extends Data
{
    public function __construct(
        public string $title = 'Never Gonna Give You Up',
        public string $artist = 'Rick Astley',
    ) {
    }
}
```

This works for simple types like strings, integers, floats, booleans, enums and arrays. But what if you want to set a default value for a more complex type like a `CarbonImmutable` object? You can use the constructor to do this:

```php
class SongData extends Data
{
    #[Date]
    public CarbonImmutable|Optional $date;

    public function __construct(
        public string $title = 'Never Gonna Give You Up',
        public string $artist = 'Rick Astley',
    ) {
        $this->date = CarbonImmutable::create(1987, 7, 27);
    }
}
```

You can now do the following:

```php
SongData::from();
SongData::from(['title' => 'Giving Up On Love', 'date' => CarbonImmutable::create(1988, 4, 15)]);
```

Even validation will work:

```php
SongData::validateAndCreate();
SongData::validateAndCreate(['title' => 'Giving Up On Love', 'date' => CarbonImmutable::create(1988, 4, 15)]);
```

There are a few conditions for this approach:

- You must always use a sole property, a property within the constructor definition won't work
- The optional type is technically not required, but it's a good idea to use it otherwise the validation won't work
- Validation won't be performed on the default value, so make sure it is valid
`````

## File: as-a-data-transfer-object/factories.md
`````markdown
---
title: Factories
weight: 13
---

It is possible to automatically create data objects in all sorts of forms with this package. Sometimes a little bit more
control is required when a data object is being created. This is where factories come in.

Factories allow you to create data objects like before but allow you to customize the creation process.

For example, we can create a data object using a factory like this:

```php
SongData::factory()->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

Collecting a bunch of data objects using a factory can be done as such:

```php
SongData::factory()->collect(Song::all())
```

## Disable property name mapping

We saw [earlier](/docs/laravel-data/v4/as-a-data-transfer-object/mapping-property-names) that it is possible to map
property names when creating a data object from an array. This can be disabled when using a factory:

```php
ContractData::factory()->withoutPropertyNameMapping()->from(['name' => 'Rick Astley', 'record_company' => 'RCA Records']); // record_company will not be mapped to recordCompany
```

## Changing the validation strategy

By default, the package will only validate Requests when creating a data object it is possible to change the validation
strategy to always validate for each type:

```php
SongData::factory()->alwaysValidate()->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

Or completely disable validation:

```php
SongData::factory()->withoutValidation()->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

## Disabling magic methods

A data object can be created
using [magic methods](/docs/laravel-data/v4/as-a-data-transfer-object/creating-a-data-object.md#magical-creation) , this can be disabled
when using a factory:

```php
SongData::factory()->withoutMagicalCreation()->from('Never gonna give you up'); // Won't work since the magical method creation is disabled
```

It is also possible to ignore the magical creation methods when creating a data object as such:

```php
SongData::factory()->ignoreMagicalMethod('fromString')->from('Never gonna give you up'); // Won't work since the magical method is ignored
```

## Disabling optional values

When creating a data object that has optional properties, it is possible choose whether missing properties from the payload should be created as `Optional`. This can be helpful when you want to have a `null` value instead of an `Optional` object - for example, when creating the DTO from an Eloquent model with `null` values. 

```php
class SongData extends Data {
    public function __construct(
        public string $title,
        public string $artist,
        public Optional|null|string $album,
    ) {
    }
}

SongData::factory()
    ->withoutOptionalValues()
    ->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']); // album will `null` instead of `Optional`
```

Note that when an Optional property has no default value, and is not nullable, and the payload does not contain a value for this property, the DTO will not have the property set - so accessing it can throw `Typed property must not be accessed before initialization` error. Therefore, it's advisable to either set a default value or make the property nullable, when using `withoutOptionalValues`.

```php
class SongData extends Data {
    public function __construct(
        public string $title,
        public string $artist,
        public Optional|string $album, // careful here!
        public Optional|string $publisher = 'unknown',
        public Optional|string|null $label,
    ) {
    }
}

$data = SongData::factory()
    ->withoutOptionalValues()
    ->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
    
$data->toArray(); // ['title' => 'Never gonna give you up', 'artist' => 'Rick Astley', 'publisher' => 'unknown', 'label' => null]

$data->album; // accessing the album will throw an error, unless the property is set before accessing it
```

## Adding additional global casts

When creating a data object, it is possible to add additional casts to the data object:

```php
SongData::factory()->withCast('string', StringToUpperCast::class)->from(['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']);
```

These casts will not replace the other global casts defined in the `data.php` config file, they will though run before
the other global casts. You define them just like you would define them in the config file, the first parameter is the
type of the property that should be cast and the second parameter is the cast class.

## Using the creation context

Internally the package uses a creation context to create data objects. The factory allows you to use this context manually, but when using the from method it will be used automatically.

It is possible to inject the creation context into a magical method by adding it as a parameter:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function fromModel(Song $song, CreationContext $context): self
    {
        // Do something with the context
    }
}
```

You can read more about creation contexts [here](/docs/laravel-data/v4/advanced-usage/pipeline).
`````

## File: as-a-data-transfer-object/injecting-property-values.md
`````markdown
---
title: Injecting property values
weight: 12
---

When creating a data object, it is possible to inject values into properties from all kinds of sources like route
parameters, the current user or dependencies in the container.

## Filling properties from a route parameter

When creating data objects from requests, it's possible to automatically fill data properties from request route
parameters, such as route models.

The `FromRouteParameter` attribute allows filling properties with route parameter values.

### Using scalar route parameters

```php
Route::patch('/songs/{songId}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameter('songId')]
    public int $id;
    public string $name;
}
```

Here, the `$id` property will be filled with the `songId` route parameter value (which most likely is a string or
integer).

### Using Models, objects or arrays as route parameters

Given that we have a route to create songs for a specific author, and that the `{author}` route parameter uses route
model binding to automatically bind to an `Author` model:

```php
Route::post('/songs/{artist}', [SongController::class, 'store']);

class SongData extends Data {
    public int $id;
    #[FromRouteParameter('artist')]
    public ArtistData $author;
}
```

Here, the `$artist` property will be filled with the `artist` route parameter value, which will be an instance of the
`Artist` model. Note that the package will automatically cast the model to `ArtistData`.

## Filling properties from route parameter properties

The `FromRouteParameterProperty` attribute allows filling properties with values from route parameter properties. The
main difference from `FromRouteParameter` is that the former uses the full route parameter value, while
`FromRouteParameterProperty` uses a single property from the route parameter.

In the example below, we're using route model binding. `{song}` represents an instance of the `Song` model.
`FromRouteParameterProperty` automatically attempts to fill the `SongData` `$id` property from `$song->id`.

```php
Route::patch('/songs/{song}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameterProperty('song')]
    public int $id;
    public string $name;
}
```

### Using custom property mapping

In the example below, `$name` property will be filled with `$song->title` (instead of `$song->name).

```php
Route::patch('/songs/{song}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameterProperty('song')]
    public int $id;
    #[FromRouteParameterProperty('song', 'title')]
    public string $name;
}
```

### Nested property mapping

Nested properties are supported as well. Here, we fill `$singerName` from `$artist->leadSinger->name`:

```php
Route::patch('/artists/{artist}/songs/{song}', [SongController::class, 'update']);

class SongData extends Data {
    #[FromRouteParameterProperty('song')]
    public int $id;
    #[FromRouteParameterProperty('artist', 'leadSinger.name')]
    public string $singerName;
}
```

## Route parameters take priority over request body

By default, route parameters take priority over values in the request body. For example, when the song ID is present in
the route model as well as request body, the ID from route model is used.

```php
Route::patch('/songs/{song}', [SongController::class, 'update']);

// PATCH /songs/123
// { "id": 321, "name": "Never gonna give you up" }

class SongData extends Data {
    #[FromRouteParameterProperty('song')]
    public int $id;
    public string $name;
}
```

Here, `$id` will be `123` even though the request body has `321` as the ID value.

In most cases, this is useful - especially when you need the ID for a validation rule. However, there may be cases when
the exact opposite is required.

The above behavior can be turned off by switching the `replaceWhenPresentInPayload` flag off. This can be useful when
you _intend_ to allow updating a property that is present in a route parameter, such as a slug:

```php
Route::patch('/songs/{slug}', [SongController::class, 'update']);

// PATCH /songs/never
// { "slug": "never-gonna-give-you-up", "name": "Never gonna give you up" }

class SongData extends Data {
    #[FromRouteParameter('slug', replaceWhenPresentInPayload: false )]
    public string $slug;
}
```

Here, `$slug` will be `never-gonna-give-you-up` even though the route parameter value is `never`.

## Filling properties from the authenticated user

The `FromCurrentUser` attribute allows filling properties with values from the authenticated user.

```php
class SongData extends Data {
    #[FromAuthenticatedUser]
    public UserData $user;
}
```

It is possible to specify the guard to use when fetching the user:

```php
class SongData extends Data {
    #[FromAuthenticatedUser('api')]
    public UserData $user;
}
```

Just like with route parameters, it is possible to fill properties with specific user properties using
`FromAuthenticatedUserProperty`:

```php
class SongData extends Data {
    #[FromAuthenticatedUserProperty('api','name')]
    public string $username;
}
```

All the other features like custom property mapping and not replacing values when present in the payload are supported
as well.

## Filling properties from the container

The `FromContainer` attribute allows filling properties with dependencies from the container.

```php
class SongData extends Data {
    #[FromContainer(SongService::class)]
    public SongService $song_service;
}
```

When a dependency requires additional parameters these can be provided as such:

```php
class SongData extends Data {
    #[FromContainer(SongService::class, parameters: ['year' => 1984])]
    public SongService $song_service;
}
```

It is even possible to completely inject the container itself:

```php
class SongData extends Data {
    #[FromContainer]
    public Container $container;
}
```

Selecting a property from a dependency can be done using `FromContainerProperty`:

```php
class SongData extends Data {
    #[FromContainerProperty(SongService::class, 'name')]
    public string $service_name;
}
```

Again, all the other features like custom property mapping and not replacing values when present in the payload are
supported as well.

## Creating your own injectable attributes

All the attributes we saw earlier implement the `InjectsPropertyValue` interface:

```php
interface InjectsPropertyValue
{
    public function resolve(
        DataProperty $dataProperty,
        mixed $payload,
        array $properties,
        CreationContext $creationContext
    ): mixed;

    public function shouldBeReplacedWhenPresentInPayload() : bool;
}
```

It is possible to create your own attribute by implementing this interface. The `resolve` method is responsible for
returning the value that should be injected into the property. The `shouldBeReplacedWhenPresentInPayload` method should
return `true` if the value should be replaced when present in the payload.
`````

## File: as-a-data-transfer-object/mapping-property-names.md
`````markdown
---
title: Mapping property names
weight: 7
---

Sometimes the property names in the array from which you're creating a data object might be different. You can define another name for a property when it is created from an array using attributes:

```php
class ContractData extends Data
{
    public function __construct(
        public string $name,
        #[MapInputName('record_company')]
        public string $recordCompany,
    ) {
    }
}
```

Creating the data object can now be done as such:

```php
ContractData::from(['name' => 'Rick Astley', 'record_company' => 'RCA Records']);
```

Changing all property names in a data object to snake_case in the data the object is created from can be done as such:

```php
#[MapInputName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

You can also use the `MapName` attribute when you want to combine input (see [transforming data objects](/docs/laravel-data/v4/as-a-resource/mapping-property-names)) and output property name mapping:

```php
#[MapName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

It is possible to set a default name mapping strategy for all data objects in the `data.php` config file:

```php
'name_mapping_strategy' => [
    'input' => SnakeCaseMapper::class,
    'output' => null,
],
```


## Mapping Nested Properties

You can also map nested properties using dot notation in the `MapInputName` attribute. This is useful when you want to extract a nested value from an array and assign it to a property in your data object:

```php
class SongData extends Data
{
    public function __construct(
        #[MapInputName("title.name")]
        public string $title,
        #[MapInputName("artists.0.name")]
        public string $artist
    ) {
    }
}
```

You can create the data object from an array with nested structures:

```php
SongData::from([
    "title" => [
        "name" => "Never gonna give you up"
    ],
    "artists" => [
        ["name" => "Rick Astley"]
    ]
]);
```

The package has a set of default mappers available, you can find them [here](/docs/laravel-data/v4/advanced-usage/available-property-mappers).
`````

## File: as-a-data-transfer-object/model-to-data-object.md
`````markdown
---
title: From a model
weight: 11
---

It is possible to create a data object from a model, let's say we have the following model:

```php
class Artist extends Model
{
    
}
```

It has the following columns in the database:

- id
- first_name
- last_name
- created_at
- updated_at

We can create a data object from this model like this:

```php
class ArtistData extends Data
{
    public int $id;
    public string $first_name;
    public string $last_name;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

We now can create a data object from the model like this:

```php
$artist = ArtistData::from(Artist::find(1));
```

## Casts

A model can have casts, these casts will be called before a data object is created. Let's extend the model:

```php
class Artist extends Model
{
    public function casts(): array
    {
       return [
            'properties' => 'array'
       ];
    }
}
```

Within the database the new column will be stored as a JSON string, but in the data object we can just use the array
type:

```php
class ArtistData extends Data
{
    public int $id;
    public string $first_name;
    public string $last_name;
    public array $properties;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

## Attributes & Accessors

Laravel allows you to define attributes on a model, these will be called before a data object is created. Let's extend
the model:

```php
class Artist extends Model
{
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
```

We now can use the attribute in the data object:

```php
class ArtistData extends Data
{
    public int $id;
    public string $full_name;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

Remember: we need to use the snake_case version of the attribute in the data object since that's how it is stored in the
model. Read on for a more elegant solution when you want to use camelCase property names in your data object.

It is also possible to define accessors on a model which are the successor of the attributes:

```php
class Artist extends Model
{
    public function getFullName(): Attribute
    {
        return Attribute::get(fn () => "{$this->first_name} {$this->last_name}");
    }
}
```

With the same data object we created earlier we can now use the accessor.

## Mapping property names

Sometimes you want to use camelCase property names in your data object, but the model uses snake_case. You can use
an `MapInputName` to map the property names:

```php
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

class ArtistData extends Data
{
    public int $id;
    #[MapInputName(SnakeCaseMapper::class)]
    public string $fullName;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

An even more elegant solution would be to map every property within the data object:

```php
#[MapInputName(SnakeCaseMapper::class)]
class ArtistData extends Data
{
    public int $id;
    public string $fullName;
    public CarbonImmutable $createdAt;
    public CarbonImmutable $updatedAt;
}
```

## Relations

Let's create a new model:

```php
class Song extends Model
{
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }
}
```

Which has the following columns in the database:

- id
- artist_id
- title

We update our previous model as such:

```php
class Artist extends Model
{
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }
}
```

We can now create a data object like this:

```php
class SongData extends Data
{
    public int $id;
    public string $title;
}
```

And update our previous data object like this:

```php
class ArtistData extends Data
{
    public int $id;
    /** @var array<SongData>  */
    public array $songs;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

We can now create a data object with the relations like this:

```php
$artist = ArtistData::from(Artist::with('songs')->find(1));
```

When you're not loading the relations in advance, `null` will be returned for the relation.

It is however possible to load the relation on the fly by adding the `LoadRelation` attribute to the property:

```php
class ArtistData extends Data
{
    public int $id;
    /** @var array<SongData>  */
    #[LoadRelation]
    public array $songs;
    public CarbonImmutable $created_at;
    public CarbonImmutable $updated_at;
}
```

Now the data object with relations can be created like this:

```php
$artist = ArtistData::from(Artist::find(1));
```

We even eager-load the relation for performance, neat!

### Be careful with automatic loading of relations

Let's update the `SongData` class like this:

```php
class SongData extends Data
{
    public int $id;
    public string $title;
    #[LoadRelation]
    public ArtistData $artist;
}
```

When we now create a data object like this:

```php
$song = SongData::from(Song::find(1));
```

We'll end up in an infinite loop, since the `SongData` class will try to load the `ArtistData` class, which will try to
load the `SongData` class, and so on.

## Missing attributes

When a model is missing attributes and `preventAccessingMissingAttributes` is enabled for a model the `MissingAttributeException` won't be thrown when creating a data object with a property that can be null or Optional.
`````

## File: as-a-data-transfer-object/nesting.md
`````markdown
---
title: Nesting 
weight: 2
---

It is possible to nest multiple data objects:

```php
class ArtistData extends Data
{
    public function __construct(
        public string $name,
        public int $age,
    ) {
    }
}

class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public ArtistData $artist,
    ) {
    }
}
```

You can now create a data object as such:

```php
new AlbumData(
    'Never gonna give you up',
    new ArtistData('Rick Astley', 22)
);
```

Or you could create it from an array using a magic creation method:

```php
AlbumData::from([
    'title' => 'Never gonna give you up',
    'artist' => [
        'name' => 'Rick Astley',
        'age' => 22
    ]
]);
```

## Collections of data objects

What if you want to nest a collection of data objects within a data object?

That's perfectly possible, but there's a small catch; you should always define what kind of data objects will be stored
within the collection. This is really important later on to create validation rules for data objects or partially
transforming data objects.

There are a few different ways to define what kind of data objects will be stored within a collection. You could use an
annotation, for example, which has an advantage that your IDE will have better suggestions when working with the data
object. And as an extra benefit, static analyzers like PHPStan will also be able to detect errors when your code
is using the wrong types.

A collection of data objects defined by annotation looks like this:

```php
/**
 * @property \App\Data\SongData[] $songs
 */
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public array $songs,
    ) {
    }
}
```

or like this when using properties:

```php
class AlbumData extends Data
{
    public string $title;
    
    /** @var \App\Data\SongData[] */
    public array $songs;
}
```

If you've imported the data class you can use the short notation:

```php
use App\Data\SongData;

class AlbumData extends Data
{    
    /** @var SongData[] */
    public array $songs;
}
```

It is also possible to use generics:

```php
use App\Data\SongData;

class AlbumData extends Data
{    
    /** @var array<SongData> */
    public array $songs;
}
```

The same is true for Laravel collections, but be sure to use two generic parameters to describe the collection. One for the collection key type and one for the data object type.

```php
use App\Data\SongData;
use Illuminate\Support\Collection;

class AlbumData extends Data
{    
    /** @var Collection<int, SongData> */
    public Collection $songs;
}
```

If the collection is well-annotated, the `Data` class doesn't need to use annotations:

```php
/**
 * @template TKey of array-key
 * @template TData of \App\Data\SongData
 *
 * @extends \Illuminate\Support\Collection<TKey, TData>
 */
class SongDataCollection extends Collection
{
}

class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public SongDataCollection $songs,
    ) {
    }
}
```

You can also use an attribute to define the type of data objects that will be stored within a collection:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        #[DataCollectionOf(SongData::class)]
        public array $songs,
    ) {
    }
}
```

This was the old way to define the type of data objects that will be stored within a collection. It is still supported, but we recommend using the annotation.
`````

## File: as-a-data-transfer-object/optional-properties.md
`````markdown
---
title: Optional properties
weight: 6
---

Sometimes you have a data object with properties which shouldn't always be set, for example in a partial API update where you only want to update certain fields. In this case you can make a property `Optional` as such:

```php
use Spatie\LaravelData\Optional;

class SongData extends Data
{
    public function __construct(
        public string $title,
        public string|Optional $artist,
    ) {
    }
}
```

You can now create the data object as such:

```php
SongData::from([
    'title' => 'Never gonna give you up'
]);
```

The value of `artist` will automatically be set to `Optional`. When you transform this data object to an array, it will look like this:

```php
[
    'title' => 'Never gonna give you up'
]
```

You can manually use `Optional` values within magical creation methods as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string|Optional $artist,
    ) {
    }
    
    public static function fromTitle(string $title): static
    {
        return new self($title, Optional::create());
    }
}
```

It is possible to automatically update `Optional` values to `null`:

```php
class SongData extends Data {
    public function __construct(
        public string $title,
        public Optional|null|string $artist,
    ) {
    }
}

SongData::factory()
    ->withoutOptionalValues()
    ->from(['title' => 'Never gonna give you up']); // artist will `null` instead of `Optional`
```

You can read more about this [here](/docs/laravel-data/v4/as-a-data-transfer-object/factories#disabling-optional-values).
`````

## File: as-a-data-transfer-object/request-to-data-object.md
`````markdown
---
title: From a request
weight: 10
---

You can create a data object by the values given in the request.

For example, let's say you send a POST request to an endpoint with the following data:

```json
{
    "title" : "Never gonna give you up",
    "artist" : "Rick Astley"
}
```

This package can automatically resolve a `SongData` object from these values by using the `SongData` class we saw in an
earlier chapter:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

You can now inject the `SongData` class in your controller. It will already be filled with the values found in the
request.

```php
class UpdateSongController
{
    public function __invoke(
        Song $model,
        SongData $data
    ){
        $model->update($data->all());
        
        return redirect()->back();
    }
}
```

As an added benefit, these values will be validated before the data object is created. If the validation fails, a `ValidationException` will be thrown which will look like you've written the validation rules yourself.

The package will also automatically validate all requests when passed to the from method:

```php
class UpdateSongController
{
    public function __invoke(
        Song $model,
        SongRequest $request
    ){
        $model->update(SongData::from($request)->all());
        
        return redirect()->back();
    }
}
```

We have a complete section within these docs dedicated to validation, you can find it [here](/docs/laravel-data/v4/validation/introduction).

## Getting the data object filled with request data from anywhere

You can resolve a data object from the container.

```php
app(SongData::class);
```

We resolve a data object from the container, its properties will already be filled by the values of the request with matching key names.
If the request contains data that is not compatible with the data object, a validation exception will be thrown.


## Validating a collection of data objects:

Let's say we want to create a data object like this from a request:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        #[DataCollectionOf(SongData::class)]
        public DataCollection $songs,
    ) {
    }
}
```

Since the `SongData` has its own validation rules, the package will automatically apply them when resolving validation
rules for this object.

In this case the validation rules for `AlbumData` would look like this:

```php
[
    'title' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'songs.*.title' => ['required', 'string'],
    'songs.*.artist' => ['required', 'string'],
]
```
`````

## File: as-a-resource/_index.md
`````markdown
---
title: As a resource

weight: 4
---
`````

## File: as-a-resource/appending-properties.md
`````markdown
---
title: Appending properties
weight: 4
---

It is possible to add some extra properties to your data objects when they are transformed into a resource:

```php
SongData::from(Song::first())->additional([
    'year' => 1987,
]);
```

This will output the following array:

```php
[
    'name' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'year' => 1987,
]
```

When using a closure, you have access to the underlying data object:

```php
SongData::from(Song::first())->additional([
    'slug' => fn(SongData $songData) => Str::slug($songData->title),
]);
```

Which produces the following array:

```php
[
    'name' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'slug' => 'never-gonna-give-you-up',
]
```

It is also possible to add extra properties by overwriting the `with` method within your data object:

```php
class SongData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $artist
    ) {
    }

    public static function fromModel(Song $song): self
    {
        return new self(
            $song->id,
            $song->title,
            $song->artist
        );
    }
    
    public function with()
    {
        return [
            'endpoints' => [
                'show' => action([SongsController::class, 'show'], $this->id),
                'edit' => action([SongsController::class, 'edit'], $this->id),
                'delete' => action([SongsController::class, 'delete'], $this->id),
            ]
        ];
    }
}
```

Now each transformed data object contains an `endpoints` key with all the endpoints for that data object:

```php
[
    'id' => 1,
    'name' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'endpoints' => [
        'show' => 'https://spatie.be/songs/1',
        'edit' => 'https://spatie.be/songs/1',
        'delete' => 'https://spatie.be/songs/1',
    ],
]
```
`````

## File: as-a-resource/from-data-to-array.md
`````markdown
---
title: From data to array
weight: 1
---

A data object can automatically be transformed into an array as such:

```php
SongData::from(Song::first())->toArray();
```

Which will output the following array:

```php
[
    'name' => 'Never gonna give you up',
    'artist' => 'Rick Astley'
]
```

By default, calling `toArray` on a data object will recursively transform all properties to an array. This means that nested data objects and collections of data objects will also be transformed to arrays. Other complex types like `Carbon`, `DateTime`, `Enums`, etc... will be transformed into a string. We'll see in the [transformers](/docs/laravel-data/v4/as-a-resource/transformers) section how to configure and customize this behavior.

If you only want to transform a data object to an array without transforming the properties, you can call the `all` method:

```php
SongData::from(Song::first())->all();
```

You can also manually transform a data object to JSON:

```php
SongData::from(Song::first())->toJson();
```

## Using collections

Here's how to create a collection of data objects:

```php
SongData::collect(Song::all());
```

A collection can be transformed to array:

```php
SongData::collect(Song::all())->toArray();
```

Which will output the following array:

```php
[
    [
        "name": "Never Gonna Give You Up",
        "artist": "Rick Astley"
    ],
    [
        "name": "Giving Up on Love",
        "artist": "Rick Astley"
    ] 
]
```

## Nesting

It is possible to nest data objects.

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        public string $email,
        public SongData $favorite_song,
    ) {
    }
    
    public static function fromModel(User $user): self
    {
        return new self(
            $user->title,
            $user->email,
            SongData::from($user->favorite_song)
        );
    }
}
```

When transformed to an array, this will look like the following:

```php
[
    "name": "Ruben",
    "email": "ruben@spatie.be",
    "favorite_song": [
        "name" : "Never Gonna Give You Up",
        "artist" : "Rick Astley"
    ]
]
```

You can also nest a collection of data objects:

```php
class AlbumData extends Data
{
    /**
    * @param Collection<int, SongData> $songs
    */
    public function __construct(
        public string $title,
        public array $songs,
    ) {
    }

    public static function fromModel(Album $album): self
    {
        return new self(
            $album->title,
            SongData::collect($album->songs)
        );
    }
}
```

As always, remember to type collections of data objects by annotation or the `DataCollectionOf` attribute, this is essential to transform these collections correctly.
`````

## File: as-a-resource/from-data-to-resource.md
`````markdown
---
title: From data to resource
weight: 2
---

A data object will automatically be transformed to a JSON response when returned in a controller:

```php
class SongController
{
    public function show(Song $model)
    {
        return SongData::from($model);
    }
}
```

The JSON then will look like this:

```json
{
    "name": "Never gonna give you up",
    "artist": "Rick Astley"
}
```

### Collections

Returning a data collection from the controller like this:

```php
SongData::collect(Song::all());
```

Will return a collection automatically transformed to JSON:

```json
[
    {
        "name": "Never Gonna Give You Up",
        "artist": "Rick Astley"
    },
    {
        "name": "Giving Up on Love",
        "artist": "Rick Astley"
    }
]
```

### Paginators

It is also possible to provide a paginator:

```php
SongData::collect(Song::paginate());
```

The data object is smart enough to create a paginated response from this with links to the next, previous, last, ... pages:

```json
{
    "data" : [
        {
            "name" : "Never Gonna Give You Up",
            "artist" : "Rick Astley"
        },
        {
            "name" : "Giving Up on Love",
            "artist" : "Rick Astley"
        }
    ],
    "meta" : {
        "current_page": 1,
        "first_page_url": "https://spatie.be/?page=1",
        "from": 1,
        "last_page": 7,
        "last_page_url": "https://spatie.be/?page=7",
        "next_page_url": "https://spatie.be/?page=2",
        "path": "https://spatie.be/",
        "per_page": 15,
        "prev_page_url": null,
        "to": 15,
        "total": 100
    }
}
```


## Transforming empty objects

When creating a new model, you probably want to provide a blueprint to the frontend with the required data to create a model. For example:

```json
{
    "name": null,
    "artist": null
}
```

You could make each property of the data object nullable like this:

```php
class SongData extends Data
{
    public function __construct(
        public ?string $title,
        public ?string $artist,
    ) {
    }

    // ...
}
```

This approach would work, but as soon as the model is created, the properties won't be `null`, which doesn't follow our data model. So it is considered a bad practice.

That's why in such cases, you can return an empty representation of the data object:

```php
class SongsController
{
    public function create(): array
    {
        return SongData::empty();
    }
}
```

Which will output the following JSON:

```json
{
    "name": null,
    "artist": null
}
```

The `empty` method on a data object will return an array with default empty values for the properties in the data object.

It is possible to change the default values within this array by providing them in the constructor of the data object:

 ```php
 class SongData extends Data
{
    public function __construct(
        public string $title = 'Title of the song here',
        public string $artist = "An artist",
    ) {
    }
    
    // ...
}
 ```

Now when we call `empty`, our JSON looks like this:

```json
{
    "name": "Title of the song here",
    "artist": "An artist"
}
``` 

You can also pass defaults within the `empty` call:

```php
SongData::empty([
    'name' => 'Title of the song here',
    'artist' => 'An artist'
]);
```

Or filter the properties that should be included in the empty response:

```php
SongData::empty(only: ['name']); // Will only return the `name` property

SongData::empty(except: ['name']); // Will return the `artist` property
```

## Response status code

When a resource is being returned from a controller, the status code of the response will automatically be set to `201 CREATED` when Laravel data detects that the request's method is `POST`.  In all other cases, `200 OK` will be returned.

## Resource classes

To make it a bit more clear that a data object is a resource, you can use the `Resource` class instead of the `Data` class:

```php
use Spatie\LaravelData\Resource;

class SongResource extends Resource
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

These resource classes have as an advantage that they won't validate data or check authorization, They are only used to transform data which makes them a bit faster.
`````

## File: as-a-resource/lazy-properties.md
`````markdown
---
title: Including and excluding properties
weight: 6
---

Sometimes you don't want all the properties included when transforming a data object to an array, for example:

```php
class AlbumData extends Data
{
    /**
    * @param Collection<int, SongData> $songs
    */
    public function __construct(
        public string $title,
        public Collection $songs,
    ) {
    }
}
```

This will always output a collection of songs, which can become quite large. With lazy properties, we can include
properties when we want to:

```php
class AlbumData extends Data
{
    /**
    * @param Lazy|Collection<int, SongData> $songs
    */
    public function __construct(
        public string $title,
        public Lazy|Collection $songs,
    ) {
    }
    
    public static function fromModel(Album $album): self
    {
        return new self(
            $album->title,
            Lazy::create(fn() => SongData::collect($album->songs))
        );
    }
}
```

The `songs` key won't be included in the resource when transforming it from a model. Because the closure that provides
the data won't be called when transforming the data object unless we explicitly demand it.

Now when we transform the data object as such:

```php
AlbumData::from(Album::first())->toArray();
```

We get the following array:

```php
[
    'title' => 'Together Forever',
]
```

As you can see, the `songs` property is missing in the array output. Here's how you can include it.

```php
AlbumData::from(Album::first())->include('songs');
```

## Including lazy properties

Lazy properties will only be included when the `include` method is called on the data object with the property's name.

It is also possible to nest these includes. For example, let's update the `SongData` class and make all of its
properties lazy:

```php
class SongData extends Data
{
    public function __construct(
        public Lazy|string $title,
        public Lazy|string $artist,
    ) {
    }

    public static function fromModel(Song $song): self
    {
        return new self(
            Lazy::create(fn() => $song->title),
            Lazy::create(fn() => $song->artist)
        );
    }
}
```

Now `name` or `artist` should be explicitly included. This can be done as such on the `AlbumData`:

```php
AlbumData::from(Album::first())->include('songs.name', 'songs.artist');
```

Or you could combine these includes:

```php
AlbumData::from(Album::first())->include('songs.{name, artist}');
```

If you want to include all the properties of a data object, you can do the following:

```php
AlbumData::from(Album::first())->include('songs.*');
```

Explicitly including properties of data objects also works on a single data object. For example, our `UserData` looks
like this:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        public Lazy|SongData $favorite_song,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->title,
            Lazy::create(fn() => SongData::from($user->favorite_song))
        );
    }
}
```

We can include properties of the data object just like we would with collections of data objects:

```php
return UserData::from(Auth::user())->include('favorite_song.name');
```

## Types of Lazy properties

### Conditional Lazy properties

You can include lazy properties in different ways:

```php
Lazy::create(fn() => SongData::collect($album->songs));
```

With a basic `Lazy` property, you must explicitly include it when the data object is transformed.

Sometimes you only want to include a property when a specific condition is true. This can be done with conditional lazy
properties:

```php
Lazy::when(fn() => $this->is_admin, fn() => SongData::collect($album->songs));
```

The property will only be included when the `is_admin` property of the data object is true. It is not possible to
include the property later on with the `include` method when a condition is not accepted.

### Relational Lazy properties

You can also only include a lazy property when a particular relation is loaded on the model as such:

```php
Lazy::whenLoaded('songs', $album, fn() => SongData::collect($album->songs));
```

Now the property will only be included when the song's relation is loaded on the model.

## Default included lazy properties

It is possible to mark a lazy property as included by default:

```php
Lazy::create(fn() => SongData::collect($album->songs))->defaultIncluded();
```

The property will now always be included when the data object is transformed. You can explicitly exclude properties that
were default included as such:

```php
AlbumData::create(Album::first())->exclude('songs');
```

## Auto Lazy

Writing Lazy properties can be a bit cumbersome. It is often a repetitive task to write the same code over and over
again while the package can infer almost everything.

Let's take a look at our previous example:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        public Lazy|SongData $favorite_song,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->title,
            Lazy::create(fn() => SongData::from($user->favorite_song))
        );
    }
}
```

The package knows how to get the property from the model and wrap it into a data object, but since we're using a lazy
property, we need to write our own magic creation method with a lot of repetitive code.

In such a situation auto lazy might be a good fit, instead of casting the property directly into the data object, the
casting process is wrapped in a lazy Closure.

This makes it possible to rewrite the example as such:

```php
#[AutoLazy]
class UserData extends Data
{
    public function __construct(
        public string $title,
        public Lazy|SongData $favorite_song,
    ) {
    }
}
```

While achieving the same result!

Auto Lazy wraps the casting process of a value for every property typed as `Lazy` into a Lazy Closure when the
`AutoLazy` attribute is present on the class.

It is also possible to use the `AutoLazy` attribute on a property level:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        #[AutoLazy]
        public Lazy|SongData $favorite_song,
    ) {
    }
}
```

The auto lazy process won't be applied in the following situations:

- When a null value is passed to the property
- When the property value isn't present in the input payload and the property typed as `Optional`
- When a Lazy Closure is passed to the property

### Auto lazy with model relations

When you're constructing a data object from an Eloquent model, it is also possible to automatically create lazy
properties for model relations which are only resolved when the relation is loaded:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        #[AutoWhenLoadedLazy]
        public Lazy|SongData $favoriteSong,
    ) {
    }
}
```

When the `favoriteSong` relation is loaded on the model, the property will be included in the data object.

If the name of the relation doesn't match the property name, you can specify the relation name:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        #[AutoWhenLoadedLazy('favoriteSong')]
        public Lazy|SongData $favorite_song,
    ) {
    }
}
```

The package will use the regular casting process when the relation is loaded, so it is also perfectly possible to create a collection of data objects:

```php
class UserData extends Data
{
    /**
    * @param Lazy|array<int, SongData> $favoriteSongs
     */
    public function __construct(
        public string $title,
        #[AutoWhenLoadedLazy]
        public Lazy|array $favoriteSongs,
    ) {
    }
}
```

## Only and Except

Lazy properties are great for reducing payloads sent over the wire. However, when you completely want to remove a
property Laravel's `only` and `except` methods can be used:

```php
AlbumData::from(Album::first())->only('songs'); // will only show `songs`
AlbumData::from(Album::first())->except('songs'); // will show everything except `songs`
```

It is also possible to use multiple keys:

```php
AlbumData::from(Album::first())->only('songs.name', 'songs.artist');
AlbumData::from(Album::first())->except('songs.name', 'songs.artist');
```

And special keys like described above:

```php
AlbumData::from(Album::first())->only('songs.{name, artist}');
AlbumData::from(Album::first())->except('songs.{name, artist}');
```

Only and except always take precedence over include and exclude, which means that when a property is hidden by `only` or
`except` it is impossible to show it again using `include`.

### Conditionally

It is possible to add an `include`, `exclude`, `only` or `except` if a certain condition is met:

```php
AlbumData::from(Album::first())->includeWhen('songs', auth()->user()->isAdmin);
AlbumData::from(Album::first())->excludeWhen('songs', auth()->user()->isAdmin);
AlbumData::from(Album::first())->onlyWhen('songs', auth()->user()->isAdmin);
AlbumData::from(Album::first())->except('songs', auth()->user()->isAdmin);
```

You can also use the values of the data object in such condition:

```php
AlbumData::from(Album::first())->includeWhen('songs', fn(AlbumData $data) => count($data->songs) > 0);
AlbumData::from(Album::first())->excludeWhen('songs', fn(AlbumData $data) => count($data->songs) > 0);
AlbumData::from(Album::first())->onlyWhen('songs', fn(AlbumData $data) => count($data->songs) > 0);
AlbumData::from(Album::first())->exceptWhen('songs', fn(AlbumData $data) => count($data->songs) > 0);
```

In some cases, you may want to define an include on a class level by implementing a method:

```php
class AlbumData extends Data
{
    /**
    * @param Lazy|Collection<SongData> $songs
    */
    public function __construct(
        public string $title,
        public Lazy|Collection $songs,
    ) {
    }
    
    public function includeProperties(): array
    {
        return [
            'songs' => $this->title === 'Together Forever',
        ];
    }
}
```

It is even possible to include nested properties:

```php
class AlbumData extends Data
{
    /**
    * @param Lazy|Collection<SongData> $songs
    */
    public function __construct(
        public string $title,
        public Lazy|Collection $songs,
    ) {
    }
    
    public function includeProperties(): array
    {
        return [
            'songs.title' => $this->title === 'Together Forever',
        ];
    }
}
```

You can define exclude, except and only partials on a data class:

- You can define **excludes** in a `excludeProperties` method
- You can define **except** in a `exceptProperties` method
- You can define **only** in a `onlyProperties` method

## Using query strings

It is possible to include or exclude lazy properties by the URL query string:

For example, when we create a route `my-account`:

```php
// in web.php

Route::get('my-account', fn() => UserData::from(User::first()));
```

We now specify that a key of the data object is allowed to be included by query string on the data object:

```php
class UserData extends Data
{
    public static function allowedRequestIncludes(): ?array
    {
        return ['favorite_song'];
    }

    // ...
}
```

Our JSON would look like this when we request `https://spatie.be/my-account`:

```json
{
    "name" : "Ruben Van Assche"
}
```

We can include `favorite_song` by adding it to the query in the URL as such:

```
https://spatie.be/my-account?include=favorite_song
```

```json
{
    "name" : "Ruben Van Assche",
    "favorite_song" : {
        "name" : "Never Gonna Give You Up",
        "artist" : "Rick Astley"
    }
}
```

We can also include multiple properties by separating them with a comma:

```
https://spatie.be/my-account?include=favorite_song,favorite_movie
```

Or by using a group input:

```
https://spatie.be/my-account?include[]=favorite_song&include[]=favorite_movie
```

Including properties works for data objects and data collections.

### Allowing includes by query string

By default, it is disallowed to include properties by query string:

```php
class UserData extends Data
{
    public static function allowedRequestIncludes(): ?array
    {
        return [];
    }
}
```

You can pass several names of properties which are allowed to be included by query string:

```php
class UserData extends Data
{
    public static function allowedRequestIncludes(): ?array
    {
        return ['favorite_song', 'name'];
    }
}
```

Or you can allow all properties to be included by query string:

```php
class UserData extends Data
{
    public static function allowedRequestIncludes(): ?array
    {
        return null;
    }
}
```

### Other operations

It is also possible to run exclude, except and only operations on a data object:

- You can define **excludes** in `allowedRequestExcludes` and use the `exclude` key in your query string
- You can define **except** in `allowedRequestExcept` and use the `except` key in your query string
- You can define **only** in `allowedRequestOnly` and use the `only` key in your query string

## Mutability

Adding includes/excludes/only/except to a data object will only affect the data object (and its nested chain) once:

```php
AlbumData::from(Album::first())->include('songs')->toArray(); // will include songs
AlbumData::from(Album::first())->toArray(); // will not include songs
```

If you want to add includes/excludes/only/except to a data object and its nested chain that will be used for all future
transformations, you can define them in their respective *properties methods:

```php
class AlbumData extends Data
{
    /**
    * @param Lazy|Collection<SongData> $songs
    */
    public function __construct(
        public string $title,
        public Lazy|Collection $songs,
    ) {
    }
    
    public function includeProperties(): array
    {
        return [
            'songs'
        ];
    }
}
```

Or use the permanent methods:

```php
AlbumData::from(Album::first())->includePermanently('songs');
AlbumData::from(Album::first())->excludePermanently('songs');
AlbumData::from(Album::first())->onlyPermanently('songs');
AlbumData::from(Album::first())->exceptPermanently('songs');
```

When using a conditional includes/excludes/only/except, you can set the permanent flag:

```php
AlbumData::from(Album::first())->includeWhen('songs', fn(AlbumData $data) => count($data->songs) > 0, permanent: true);
AlbumData::from(Album::first())->excludeWhen('songs', fn(AlbumData $data) => count($data->songs) > 0, permanent: true);
AlbumData::from(Album::first())->onlyWhen('songs', fn(AlbumData $data) => count($data->songs) > 0), permanent: true);
AlbumData::from(Album::first())->except('songs', fn(AlbumData $data) => count($data->songs) > 0, permanent: true);
```
`````

## File: as-a-resource/mapping-property-names.md
`````markdown
---
title: Mapping property names
weight: 3
---

Sometimes you might want to change the name of a property in the transformed payload, with attributes this is possible:

```php
class ContractData extends Data
{
    public function __construct(
        public string $name,
        #[MapOutputName('record_company')]
        public string $recordCompany,
    ) {
    }
}
```

Now our array looks like this:

```php
[
    'name' => 'Rick Astley',
    'record_company' => 'RCA Records',
]
```

Changing all property names in a data object to snake_case as output data can be done as such:

```php
#[MapOutputName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

You can also use the `MapName` attribute when you want to combine input and output property name mapping:

```php
#[MapName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

It is possible to set a default name mapping strategy for all data objects in the `data.php` config file:

```php
'name_mapping_strategy' => [
    'input' => null,
    'output' => SnakeCaseMapper::class,
],
```

You can now create a data object as such:

```php
$contract = new ContractData(
    name: 'Rick Astley',
    recordCompany: 'RCA Records',
);
```

And a transformed version of the data object will look like this:

```php
[
    'name' => 'Rick Astley',
    'record_company' => 'RCA Records',
]
```

The package has a set of default mappers available, you can find them [here](/docs/laravel-data/v4/advanced-usage/available-property-mappers).
`````

## File: as-a-resource/transformers.md
`````markdown
---
title: Transforming data
weight: 7
---

Transformers allow you to transform complex types to simple types. This is useful when you want to transform a data object to an array or JSON.

No complex transformations are required for the default types (string, bool, int, float, enum and array), but special types like `Carbon` or a Laravel Model will need extra attention.

Transformers are simple classes that will convert a such complex types to something simple like a `string` or `int`. For example, we can transform a `Carbon` object to `16-05-1994`, `16-05-1994T00:00:00+00` or something completely different.

There are two ways you can define transformers: locally and globally.

## Local transformers

When you want to transform a specific property, you can use an attribute with the transformer you want to use:

```php
class ArtistData extends Data{
    public function __construct(
        public string $name,
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        public Carbon $birth_date
    ) {
    }
}
```

The `DateTimeInterfaceTransformer` is shipped with the package and will transform objects of type `Carbon`, `CarbonImmutable`, `DateTime` and `DateTimeImmutable` to a string.

The format used for converting the date to string can be set in the `data.php` config file. It is also possible to manually define a format:

```php
class ArtistData extends Data{
    public function __construct(
        public string $name,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'm-Y')]
        public Carbon $birth_date
    ) {
    }
}
```

Next to a `DateTimeInterfaceTransformer` the package also ships with an `ArrayableTransformer` that transforms an `Arrayable` object to an array.

It is possible to create transformers for your specific types. You can find more info [here](/docs/laravel-data/v4/advanced-usage/creating-a-transformer).

## Global transformers

Global transformers are defined in the `data.php` config file and are used when no local transformer for a property was added. By default, there are two transformers:

```php
use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Transformers\ArrayableTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/*
 * Global transformers will take complex types and transform them into simple
 * types.
 */
'transformers' => [
    DateTimeInterface::class => DateTimeInterfaceTransformer::class,
    Arrayable::class => ArrayableTransformer::class,
],
```

The package will look through these global transformers and tries to find a suitable transformer. You can define transformers for:

- a **specific implementation** (e.g. CarbonImmutable)
- an **interface** (e.g. DateTimeInterface)
- a **base class** (e.g. Enum)

## Getting a data object without transforming

It is possible to get an array representation of a data object without transforming the properties. This means `Carbon` objects won't be transformed into strings. And also, nested data objects and `DataCollection`s won't be transformed into arrays. You can do this by calling the `all` method on a data object like this:

```php
ArtistData::from($artist)->all();
```

## Getting a data object (on steroids)

Internally the package uses the `transform` method for operations like `toArray`, `all`, `toJson` and so on. This method is highly configurable, when calling it without any arguments it will behave like the `toArray` method:

```php
ArtistData::from($artist)->transform();
```

Producing the following result:

```php
[
    'name' => 'Rick Astley',
    'birth_date' => '06-02-1966',
]
```

It is possible to disable the transformation of values, which will make the `transform` method behave like the `all` method:

```php
use Spatie\LaravelData\Support\Transformation\TransformationContext;

ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->withoutValueTransformation()
);
```

Outputting the following array:

```php
[
    'name' => 'Rick Astley',
    'birth_date' => Carbon::parse('06-02-1966'),
]
```

The [mapping of property names](/docs/laravel-data/v4/as-a-resource/mapping-property-names) can also be disabled:

```php
ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->withoutPropertyNameMapping()
);
```

It is possible to enable [wrapping](/docs/laravel-data/v4/as-a-resource/wrapping-data) the data object:

```php
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->withWrapping()
);
```

Outputting the following array:

```php
[
    'data' => [
        'name' => 'Rick Astley',
        'birth_date' => '06-02-1966',
    ],
]
```

You can also add additional global transformers as such:

```php
ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->withGlobalTransformer(
        'string', 
        StringToUpperTransformer::class
    )
);
```

## Transformation depth

When transforming a complicated structure of nested data objects it is possible that an infinite loop is created of data objects including each other.
To prevent this, a transformation depth can be set, when that depth is reached when transforming, either an exception will be thrown or an empty
array is returned, stopping the transformation.

This transformation depth can be set globally in the `data.php` config file:

```php
'max_transformation_depth' => 20,
```

Setting the transformation depth to `null` will disable the transformation depth check:

```php
'max_transformation_depth' => null,
```

It is also possible if a `MaxTransformationDepthReached` exception should be thrown or an empty array should be returned:

```php
'throw_when_max_transformation_depth_reached' => true,
```

It is also possible to set the transformation depth on a specific transformation by using a `TransformationContextFactory`:

```php
ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->maxDepth(20)
);
```

By default, an exception will be thrown when the maximum transformation depth is reached. This can be changed to return an empty array as such:

```php
ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->maxDepth(20, throw: false)
);
```
`````

## File: as-a-resource/wrapping.md
`````markdown
---
title: Wrapping
weight: 5
---

By default, when a data object is transformed into JSON in your controller, it looks like this:

```json
{
    "name" : "Never gonna give you up",
    "artist" : "Rick Astley"
}
```

It is possible to wrap a data object:

```php
SongData::from(Song::first())->wrap('data');
```

Now the JSON looks like this:

```json
{
    "data" : {
        "name" : "Never gonna give you up",
        "artist" : "Rick Astley"
    }
}
```

Data objects and collections will only get wrapped when you're sending them as a response and never when calling `toArray` or `toJson` on it.

It is possible to define a default wrap key inside a data object:

```php
class SongData extends Data
{
    public function defaultWrap(): string
    {
        return 'data';
    }

    // ...
}
```

Or you can set a global wrap key inside the `data.php` config file:

```php
    /*
     * Data objects can be wrapped into a key like 'data' when used as a resource,
     * this key can be set globally here for all data objects. You can pass in
     * `null` if you want to disable wrapping.
     */
    'wrap' => 'data',
```

## Wrapping collections

Collections can be wrapped just like data objects:

```php
SongData::collect(Song::all(), DataCollection::class)->wrap('data');
```

Notice here, for now we only support wrapping `DataCollections`, `PaginatedDataCollections` and `CursorPaginatedDataCollections` on the root level. Wrapping won't work for Laravel Collections or arrays (for now) since the package cannot interfere. Nested properties with such types can be wrapped though (see further). 

The JSON will now look like this:

```json
{
    "data" : [
        {
            "name" : "Never Gonna Give You Up",
            "artist" : "Rick Astley"
        },
        {
            "name" : "Giving Up on Love",
            "artist" : "Rick Astley"
        }
    ]
}
```

It is possible to set the data key in paginated collections:

```php
SongData::collect(Song::paginate(), PaginatedDataCollection::class)->wrap('paginated_data');
```

Which will let the JSON look like this:

```json
{
    "paginated_data" : [
        {
            "name" : "Never Gonna Give You Up",
            "artist" : "Rick Astley"
        },
        {
            "name" : "Giving Up on Love",
            "artist" : "Rick Astley"
        }
    ],
    "meta" : {
        "current_page" : 1,
        "first_page_url" : "https://spatie.be/?page=1",
        "from" : 1,
        "last_page" : 7,
        "last_page_url" : "https://spatie.be/?page=7",
        "next_page_url" : "https://spatie.be/?page=2",
        "path" : "https://spatie.be/",
        "per_page" : 15,
        "prev_page_url" : null,
        "to" : 15,
        "total" : 100
    }
}
```

## Nested wrapping

A data object included inside another data object will never be wrapped even if a wrap is set:

```php
class UserData extends Data
{
    public function __construct(
        public string $title,
        public string $email,
        public SongData $favorite_song,
    ) {
    }
    
    public static function fromModel(User $user): self
    {
        return new self(
            $user->title,
            $user->email,
            SongData::create($user->favorite_song)->wrap('data')
        );
    }
}

UserData::from(User::first())->wrap('data');
```

```json
{
    "data" : {
        "name" : "Ruben",
        "email" : "ruben@spatie.be",
        "favorite_song" : {
            "name" : "Never Gonna Give You Up",
            "artist" : "Rick Astley"
        }
    }
}
```

A data collection inside a data object will get wrapped when a wrapping key is set (in order to mimic Laravel resources):

```php
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

class AlbumData extends Data
{
    public function __construct(
        public string $title,
        #[DataCollectionOf(SongData::class)]
        public DataCollection $songs,
    ) {
    }

    public static function fromModel(Album $album): self
    {
        return new self(
            $album->title,
            SongData::collect($album->songs, DataCollection::class)->wrap('data')
        );
    }
}

AlbumData::from(Album::first())->wrap('data');
```

The JSON will look like this:

```json
{
    "data" : {
        "title" : "Whenever You Need Somebody",
        "songs": {
            "data" : [
                {
                    "name" : "Never Gonna Give You Up",
                    "artist" : "Rick Astley"
                },
                {
                    "name" : "Giving Up on Love",
                    "artist" : "Rick Astley"
                }
            ]
        }
    }
}
```

## Disabling wrapping

Whenever a data object is wrapped due to the default wrap method or a global wrap key, it is possible to disable wrapping on a data object/collection:

```php
SongData::from(Song::first())->withoutWrapping();
```

## Getting a wrapped array

By default, `toArray` and `toJson` will never wrap a data object or collection, but it is possible to get a wrapped array:

```php
SongData::from(Song::first())->wrap('data')->transform(wrapExecutionType: WrapExecutionType::Enabled);
```
`````

## File: getting-started/_index.md
`````markdown
---
title: Getting started
weight: 1
---
`````

## File: getting-started/quickstart.md
`````markdown
---
title: Quickstart
weight: 1
---

In this quickstart, we'll guide you through the most important functionalities of the package and how to use them.

First, you should [install the package](/docs/laravel-data/v4/installation-setup).

We will create a blog with different posts, so let's start with the `PostData` object. A post has a title, some content, a status and a date when it was published:

```php
use Spatie\LaravelData\Data;

class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

Extending your data objects from the base `Data` object is the only requirement for using the package. We add the requirements for a post as public properties.

The `PostStatus` is a native enum:

```php
enum PostStatus: string
{
    case draft = 'draft';
    case published = 'published';
    case archived = 'archived';
}
```

We store this `PostData` object as `app/Data/PostData.php`, so we have all our data objects bundled in one directory, but you're free to store them wherever you want within your application.

Tip: you can also quickly make a data object using the CLI: `php artisan make:data Post`, it will create a file `app/Data/PostData.php`.

We can now create a `PostData` object just like any plain PHP object:

```php
$post = new PostData(
    'Hello laravel-data',
    'This is an introduction post for the new package',
    PostStatus::published,
    CarbonImmutable::now()
);
```

The package also allows you to create these data objects from any type, for example, an array:

```php
$post = PostData::from([
    'title' => 'Hello laravel-data',
    'content' => 'This is an introduction post for the new package',
    'status' => PostStatus::published,
    'published_at' => CarbonImmutable::now(),
]);
```

Or a `Post` model with the required properties:

```php
class Post extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => PostStatus::class,
        'published_at' => 'immutable_datetime',
    ];
}
```

Can be quickly transformed into a `PostData` object:

```php
PostData::from(Post::findOrFail($id));
```

## Using requests

Let's say we have a Laravel request coming from the frontend with these properties. Our controller would then validate these properties, and then it would store them in a model; this can be done as such:

```php
class DataController
{
    public function __invoke(Request $request)
    {
        $request->validate($this->rules());

        $postData = PostData::from([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'status' => $request->enum('status', PostStatus::class),
            'published_at' => $request->has('published_at')
                ? CarbonImmutable::createFromFormat(DATE_ATOM, $request->input('published_at'))
                : null,
        ]);

        Post::create($postData->toArray());

        return redirect()->back();
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'status' => ['required', new Enum(PostStatus::class)],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
```

That's a lot of code to fill a data object, using laravel data we can remove a lot of code:

```php
class DataController
{
    public function __invoke(PostData $postData)
    {
        Post::create($postData->toArray());

        return redirect()->back();
    }
}
```

Let's see what's happening:

1) Laravel boots up, and the router directs to the `DataController`
2) Because we're injecting `PostData`, two things happen
    - `PostData` will generate validation rules based on the property types and validate the request
    - The `PostData` object is automatically created from the request
3) We're now in the `__invoke` method with a valid `PostData` object

You can always check the generated validation rules of a data object like this:

```php
class DataController
{
    public function __invoke(Request $request)
    {
        dd(PostData::getValidationRules($request->toArray()));
    }
}
```

Which provides us with the following set of rules:

```php
array:4 [
  "title" => array:2 [
    0 => "required"
    1 => "string"
  ]
  "content" => array:2 [
    0 => "required"
    1 => "string"
  ]
  "status" => array:2 [
    0 => "required"
    1 => Illuminate\Validation\Rules\Enum {
      #type: "App\Enums\PostStatus"
    }
  ]
  "published_at" => array:1 [
    0 => "nullable"
  ]
]
```

As you can see, we're missing the `date` rule on the `published_at` property. By default, this package will automatically generate the following rules:

- `required` when a property cannot be `null`
- `nullable` when a property can be `null`
- `numeric` when a property type is `int`
- `string` when a property type is `string`
- `boolean` when a property type is `bool`
- `numeric` when a property type is `float`
- `array` when a property type is `array`
- `enum:*` when a property type is a native enum

You can read more about the process of automated rule generation [here](/docs/laravel-data/v4/as-a-data-transfer-object/request-to-data-object#content-automatically-inferring-rules-for-properties-1).

We can easily add the date rule by using an attribute to our data object:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

Now our validation rules look like this:

```php
array:4 [
  "title" => array:2 [
    0 => "required"
    1 => "string"
  ]
  "content" => array:2 [
    0 => "required"
    1 => "string"
  ]
  "status" => array:2 [
    0 => "required"
    1 => Illuminate\Validation\Rules\Enum {
      #type: "App\Enums\PostStatus"
    }
  ]
  "published_at" => array:2 [
    0 => "nullable"
    1 => "date"
  ]
]
```

There are [tons](/docs/laravel-data/v4/advanced-usage/validation-attributes) of validation rule attributes you can add to data properties. There's still much more you can do with validating data objects. Read more about it [here](/docs/laravel-data/v4/as-a-data-transfer-object/request-to-data-object#validating-a-request).

Tip: By default, when creating a data object in a non request context, no validation is executed:

```php
$post = PostData::from([
	// As long as PHP accepts the values for the properties, the object will be created
]);
```

You can create validated objects without requests like this:

```php
$post = PostData::validateAndCreate([
	// Before creating the object, each value will be validated
]);
```

## Casting data

Let's send the following payload to the controller:

```json
{
    "title" : "Hello laravel-data",
    "content" : "This is an introduction post for the new package",
    "status" : "published",
    "published_at" : "2021-09-24T13:31:20+00:00"
}
```

We get the `PostData` object populated with the values in the JSON payload, neat! But how did the package convert the `published_at` string into a `CarbonImmutable` object?

It is possible to define casts within the `data.php` config file. By default, the casts list looks like this:

```php
'casts' => [
    DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
],
```

This code means that if a class property is of type `DateTime`, `Carbon`, `CarbonImmutable`, ... it will be automatically cast.

You can create your own casts; read more about it [here](/docs/laravel-data/v4/advanced-usage/creating-a-cast).

### Local casts

Sometimes you need one specific cast in one specific data object; in such a case defining a local cast specific for the data object is a good option.

Let's say we have an `Image` class:

```php
class Image
{
    public function __construct(
        public string $file,
        public int $size,
    ) {
    }
}
```

There are two options how an `Image` can be created:

a) From a file upload
b) From an array when the image has been stored in the database

Let's create a cast for this:

```php
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;
use Str;

class ImageCast implements Cast
{
        public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): Image|Uncastable
    {
        // Scenario A
        if ($value instanceof UploadedFile) {
            $filename = $value->store('images', 'public');

            return new Image(
                $filename,
                $value->getSize(),
            );
        }

        // Scenario B
        if (is_array($value)) {
            return new Image(
                $value['filename'],
                $value['size'],
            );
        }

        return Uncastable::create();
    }
}

```

Ultimately, we return `Uncastable`, telling the package to try other casts (if available) because this cast cannot cast the value.

The last thing we need to do is add the cast to our property. We use the `WithCast` attribute for this:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        #[WithCast(ImageCast::class)]
        public ?Image $image,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

You can read more about casting [here](/docs/laravel-data/v4/as-a-data-transfer-object/casts).

## Customizing the creation of a data object

We've seen the powerful `from` method on data objects, you can throw anything at it, and it will cast the value into a data object. But what if it can't cast a specific type, or what if you want to change how a type is precisely cast into a data object?

It is possible to manually define how a type is converted into a data object. What if we would like to support to create posts via an email syntax like this:

```
title|status|content
```

Creating a `PostData` object would then look like this:

```php
PostData::from('Hello laravel-data|draft|This is an introduction post for the new package');
```

To make this work, we need to add a magic creation function within our data class:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        #[WithCast(ImageCast::class)]
        public ?Image $image,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
    
    public static function fromString(string $post): PostData
    {
        $fields = explode('|', $post);
    
        return new self(
            $fields[0],
            $fields[2],
            PostStatus::from($fields[1]),
            null,
            null
        );
    }
}
```

Magic creation methods allow you to create data objects from any type by passing them to the `from` method of a data
object, you can read more about it [here](/docs/laravel-data/v4/as-a-data-transfer-object/creating-a-data-object#content-magical-creation).

It can be convenient to transform more complex models than our `Post` into data objects because you can decide how a model
would be mapped onto a data object.

## Nesting data objects and arrays of data objects

Now that we have a fully functional post-data object. We're going to create a new data object, `AuthorData`, that will store the name of an author and an array of posts the author wrote:

```php
use Spatie\LaravelData\Attributes\DataCollectionOf;

class AuthorData extends Data
{
    /**
    * @param array<int, PostData> $posts
    */
    public function __construct(
        public string $name,
        public array $posts
    ) {
    }
}
```

Notice that we've typed the `$posts` property as an array of `PostData` objects using a docblock.  This will be very useful later on! The package always needs to know what type of data objects are stored in an array. Of course, when you're storing other types then data objects this is not required but recommended.

We can now create an author object as such:

```php
new AuthorData(
    'Ruben Van Assche',
    PostData::collect([
        [
            'title' => 'Hello laravel-data',
            'content' => 'This is an introduction post for the new package',
            'status' => PostStatus::draft,
        ],
        [
            'title' => 'What is a data object',
            'content' => 'How does it work',
            'status' => PostStatus::published,
        ],
    ])
);
```

As you can see, the `collect` method can create an array of the `PostData` objects.

But there's another way; thankfully, our `from` method makes this process even more straightforward:

```php
AuthorData::from([
    'name' => 'Ruben Van Assche',
    'posts' => [
        [
            'title' => 'Hello laravel-data',
            'content' => 'This is an introduction post for the new package',
            'status' => PostStatus::draft,
        ],
        [
            'title' => 'What is a data object',
            'content' => 'How does it work',
            'status' => PostStatus::published,
        ],
    ],
]);
```

The data object is smart enough to convert an array of posts into an array of post data. Mapping data coming from the front end was never that easy!

### Nesting objects

Nesting an individual data object into another data object is perfectly possible. Remember the `Image` class we created? We needed a cast for it, but it is a perfect fit for a data object; let's create it:

```php
class ImageData extends Data
{
    public function __construct(
        public string $filename,
        public string $size,
    ) {
    }

    public static function fromUploadedImage(UploadedFile $file): self
    {
        $stored = $file->store('images', 'public');

        return new ImageData(
            url($stored),
            $file->getSize(),
        );
    }
}
```

In our `ImageCast`, the image could be created from a file upload or an array; we'll handle that first case with the `fromUploadedImage` magic method. Because `Image` is now `ImageData,` the second case is automatically handled by the package, neat!

We'll update our `PostData` object as such:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        public ?ImageData $image,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

Creating a `PostData` object now can be done as such:

```php
return PostData::from([
    'title' => 'Hello laravel-data',
    'content' => 'This is an introduction post for the new package',
    'status' => PostStatus::published,
    'image' => [
        'filename' => 'images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png',
        'size' => 16524
    ],
    'published_at' => CarbonImmutable::create(2020, 05, 16),
]);
```

When we create the `PostData` object in a controller as such:

```php
public function __invoke(PostData $postData)
{
    return $postData;
}
```

We get a validation error:

```json
{
    "message": "The image must be an array. (and 2 more errors)",
    "errors": {
        "image": [
            "The image must be an array."
        ],
        "image.filename": [
            "The image.filename field is required."
        ],
        "image.size": [
            "The image.size field is required."
        ]
    }
}
```

This is a neat feature of data; it expects a nested `ImageData` data object when being created from the request, an array with the keys `filename` and `size`.

We can avoid this by manually defining the validation rules for this property:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        #[WithoutValidation]
        public ?ImageData $image,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'image' => ['nullable', 'image'],
        ];
    }
}
```

In the `rules` method, we explicitly define the rules for `image .`Due to how this package validates data, the nested fields `image.filename` and `image.size` would still generate validation rules, thus failing the validation. The `#[WithoutValidation]` explicitly tells the package only the use the custom rules defined in the `rules` method.

## Usage in controllers

We've been creating many data objects from all sorts of values, time to change course and go the other way around and start transforming data objects into arrays.

Let's say we have an API controller that returns a post:

```php
public function __invoke()
{
    return PostData::from([
        'title' => 'Hello laravel-data',
        'content' => 'This is an introduction post for the new package',
        'status' => PostStatus::published,
        'published_at' => CarbonImmutable::create(2020, 05, 16),
    ]);
}
```

By returning a data object in a controller, it is automatically converted to JSON:

```json
{
    "title": "Hello laravel-data",
    "content": "This is an introduction post for the new package",
    "status": "published",
    "image": null,
    "published_at": "2020-05-16T00:00:00+00:00"
}
```

You can also easily convert a data object into an array as such:

```php
$postData->toArray();
```

Which gives you an array like this:

```php
array:5 [
  "title" => "Hello laravel-data"
  "content" => "This is an introduction post for the new package"
  "status" => "published"
  "image" => null
  "published_at" => "2020-05-16T00:00:00+00:00"
]
```

It is possible to transform a data object into an array and keep complex types like the `PostStatus` and `CarbonImmutable`:

```php
$postData->all();
```

This will give the following array:

```php
array:5 [ 
  "title" => "Hello laravel-data"
  "content" => "This is an introduction post for the new package"
  "status" => App\Enums\PostStatus {
    +name: "published"
    +value: "published"
  }
  "image" => null
  "published_at" => Carbon\CarbonImmutable {
  		... 
  }
]

```

As you can see, if we transform a data object to JSON, the `CarbonImmutable` published at date is transformed into a string.

## Using transformers

A few sections ago, we used casts to cast simple types into complex types. Transformers work the other way around. They transform complex types into simple ones and transform a data object into a simpler structure like an array or JSON.

Like the `DateTimeInterfaceCast`, we also have a `DateTimeInterfaceTransformer` that converts `DateTime,` `Carbon,`... objects into strings.

This `DateTimeInterfaceTransformer` is registered in the `data.php` config file and will automatically be used when a data object needs to transform a `DateTimeInterface` object:

```php
'transformers' => [
    DateTimeInterface::class => \Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer::class,
    \Illuminate\Contracts\Support\Arrayable::class => \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
],
```

Remember the image object we created earlier; we stored a file size and filename in the object. But that could be more useful; let's provide the URL to the file when transforming the object. Just like casts, transformers also can be local. Let's implement one for `Image`:

```php
class ImageTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): string
    {
        if (! $value instanceof Image) {
            throw new Exception("Not an image");
        }

        return url($value->filename);
    }
}
```

We can now use this transformer in the data object like this:

```php
class PostData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public PostStatus $status,
        #[WithCast(ImageCast::class)]
        #[WithTransformer(ImageTransformer::class)]
        public ?Image $image,
        #[Date]
        public ?CarbonImmutable $published_at
    ) {
    }
}
```

In our controller, we return the object as such:

```php
public function __invoke()
{
    return PostData::from([
        'title' => 'Hello laravel-data',
        'content' => 'This is an introduction post for the new package',
        'status' => PostStatus::published,
        'image' => [
            'filename' => 'images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png',
            'size' => 16524
        ],
        'published_at' => CarbonImmutable::create(2020, 05, 16),
    ]);
}
```

Which leads to the following JSON:

```php
{
    "title": "Hello laravel-data",
    "content": "This is an introduction post for the new package",
    "status": "published",
    "image": "http://laravel-playbox.test/images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png",
    "published_at": "2020-05-16T00:00:00+00:00"
}
```

You can read more about transformers [here](/docs/laravel-data/v4/as-a-resource/transformers).

## Generating a blueprint

We can now send our posts as JSON to the front, but what if we want to create a new post? When using Inertia, for example, we might need an empty blueprint object like this that the user could fill in:

```json
{
    "title" : null,
    "content" : null,
    "status" : null,
    "image": null,
    "published_at" : null
}
```

Such an array can be generated with the `empty` method, which will return an empty array following the structure of your data object:

```php
PostData::empty();
```

Which will return the following array:

```php
[
  'title' => null,
  'content' => null,
  'status' => null,
  'image' => null,
  'published_at' => null,
]
```

It is possible to set the status of the post to draft by default:

```php
PostData::empty([
    'status' => PostStatus::draft;
]);
```

## Lazy properties

For the last section of this quickstart, we will look at the `AuthorData` object again; let's say that we want to compose a list of all the authors. What if we had 100+ authors who have all written more than 100+ posts:

```json
[
    {
        "name" : "Ruben Van Assche",
        "posts" : [
            {
                "title" : "Hello laravel-data",
                "content" : "This is an introduction post for the new package",
                "status" : "published",
                "image" : "http://laravel-playbox.test/images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png",
                "published_at" : "2021-09-24T13:31:20+00:00"
            }

            // ...
        ]
    },
    {
        "name" : "Freek van der Herten",
        "posts" : [
            {
                "title" : "Hello laravel-event-sourcing",
                "content" : "This is an introduction post for the new package",
                "status" : "published",
                "image" : "http://laravel-playbox.test/images/8JQtgd0XaPtt9CqkPJ3eWFVV4BAp6JR9ltYAIKqX.png"
                "published_at" : "2021-09-24T13:31:20+00:00"
            }

            // ...
        ]
    }

    // ...
]
```

As you can see, this will quickly be a large set of data we would send over JSON, which we don't want to do. Since each author includes his name and all the posts, he has written.

In the end, we only want something like this:

```json
[
    {
        "name" : "Ruben Van Assche"
    },
    {
        "name" : "Freek van der Herten"
    }

    // ...
]
```

This functionality can be achieved with lazy properties. Lazy properties are only added to a payload when we explicitly ask it. They work with closures that are executed only when this is required:

```php
class AuthorData extends Data
{
    /**
    * @param Collection<PostData>|Lazy $posts
    */
    public function __construct(
        public string $name,
        public Collection|Lazy $posts
    ) {
    }

    public static function fromModel(Author $author)
    {
        return new self(
            $author->name,
            Lazy::create(fn() => PostData::collect($author->posts))
        );
    }
}
```

When we now create a new author:

```php
$author = Author::create([
    'name' => 'Ruben Van Assche'
]);

$author->posts()->create([        
    [
        'title' => 'Hello laravel-data',
        'content' => 'This is an introduction post for the new package',
        'status' => 'draft',
        'published_at' => null,
    ]
]);

AuthorData::from($author);
```

Transforming it into JSON looks like this:

```json
{
    "name" : "Ruben Van Assche"
}
```

If we want to include the posts, the only thing we need to do is this:

```php
$postData->include('posts')->toJson();
```

Which will result in this JSON:

```json
{
    "name" : "Ruben Van Assche",
    "posts" : [
        {
            "title" : "Hello laravel-data",
            "content" : "This is an introduction post for the new package",
            "status" : "published",
            "published_at" : "2021-09-24T13:31:20+00:00"
        }
    ]
}
```

Let's take this one step further. What if we want to only include the title of each post? We can do this by making all the other properties within the post data object also lazy:

```php
class PostData extends Data
{
    public function __construct(
        public string|Lazy $title,
        public string|Lazy $content,
        public PostStatus|Lazy $status,
        #[WithoutValidation]
        #[WithCast(ImageCast::class)]
        #[WithTransformer(ImageTransformer::class)]
        public ImageData|Lazy|null $image,
        #[Date]
        public CarbonImmutable|Lazy|null $published_at
    ) {
    }
    
    public static function fromModel(Post $post): PostData
    {
        return new self(
            Lazy::create(fn() => $post->title),
            Lazy::create(fn() => $post->content),
            Lazy::create(fn() => $post->status),
            Lazy::create(fn() => $post->image),
            Lazy::create(fn() => $post->published_at)
        );
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'image' => ['nullable', 'image'],
        ];
    }
}
```

Now the only thing we need to do is include the title:

```php
$postData->include('posts.title')->toJson();
```

Which will result in this JSON:

```json
{
    "name" : "Ruben Van Assche",
    "posts" : [
        {
            "title" : "Hello laravel-data"
        }
    ]
}
```

If we also want to include the status, we can do the following:

```php
$postData->include('posts.{title,status}')->toJson();
```

It is also possible to include all properties of the posts like this:

```php
$postData->include('posts.*')->toJson();
```

You can do quite a lot with lazy properties like including them:

- when a model relation is loaded like Laravel API resources
- when they are requested in the URL query
- by default, with an option to exclude them

And a lot more. You can read all about it [here](/docs/laravel-data/v4/as-a-resource/lazy-properties).

## Conclusion

So that's it, a quick overview of this package. We barely scratched the surface of what's possible with the package. There's still a lot more you can do with data objects like:

- [casting](/docs/laravel-data/v4/advanced-usage/eloquent-casting) them into Eloquent models
- [transforming](/docs/laravel-data/v4/advanced-usage/typescript) the structure to typescript
- [working](/docs/laravel-data/v4/as-a-data-transfer-object/collections) with `DataCollections`
- [optional properties](/docs/laravel-data/v4/as-a-data-transfer-object/optional-properties) not always required when creating a data object
- [wrapping](/docs/laravel-data/v4/as-a-resource/wrapping) transformed data into keys
- [mapping](/docs/laravel-data/v4/as-a-data-transfer-object/mapping-property-names) property names when creating or transforming a data object
- [appending](/docs/laravel-data/v4/as-a-resource/appending-properties) extra data
- [including](/docs/laravel-data/v4/as-a-resource/lazy-properties#content-using-query-strings) properties using the URL query string
- [inertia](https://spatie.be/docs/laravel-data/v4/advanced-usage/use-with-inertia) support for lazy properties
- and so much more ... you'll find all the information here in the docs
`````

## File: validation/_index.md
`````markdown
---
title: Validation
weight: 3
---
`````

## File: validation/auto-rule-inferring.md
`````markdown
---
title: Auto rule inferring
weight: 2
---

The package will automatically infer validation rules from the data object. For example, for the following data class:

```php
class ArtistData extends Data{
    public function __construct(
        public string $name,
        public int $age,
        public ?string $genre,
    ) {
    }
}
```

The package will generate the following validation rules:

```php
[
    'name' => ['required', 'string'],
    'age' => ['required', 'integer'],
    'genre' => ['nullable', 'string'],
]
```

All these rules are inferred by `RuleInferrers`, special classes that will look at the types of properties and will add rules based upon that.

Rule inferrers are configured in the `data.php` config file:

```php
/*
 * Rule inferrers can be configured here. They will automatically add
 * validation rules to properties of a data object based upon
 * the type of the property.
 */
'rule_inferrers' => [
    Spatie\LaravelData\RuleInferrers\SometimesRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\NullableRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer::class,
],
```

By default, five rule inferrers are enabled:

- **SometimesRuleInferrer** will add a `sometimes` rule when the property is optional
- **NullableRuleInferrer** will add a `nullable` rule when the property is nullable
- **RequiredRuleInferrer** will add a `required` rule when the property is not nullable
- **BuiltInTypesRuleInferrer** will add a rules which are based upon the built-in php types:
    - An `int` or `float` type will add the `numeric` rule
    - A `bool` type will add the `boolean` rule
    - A `string` type will add the `string` rule
    - A `array` type will add the `array` rule
- **AttributesRuleInferrer** will make sure that rule attributes we described above will also add their rules

It is possible to write your rule inferrers. You can find more information [here](/docs/laravel-data/v4/advanced-usage/creating-a-rule-inferrer).
`````

## File: validation/introduction.md
`````markdown
---
title: Introduction
weight: 1
---

Laravel data, allows you to create data objects from all sorts of data. One of the most common ways to create a data object is from a request, and the data from a request cannot always be trusted.

That's why it is possible to validate the data before creating the data object. You can validate requests but also arrays and other structures.

The package will try to automatically infer validation rules from the data object, so you don't have to write them yourself. For example, a `?string` property will automatically have the `nullable` and `string` rules.

### Important notice

Validation is probably one of the coolest features of this package, but it is also the most complex one. We'll try to make it as straightforward as possible to validate data, but in the end, the Laravel validator was not written to be used in this way. So there are some limitations and quirks you should be aware of.

In a few cases it might be easier to just create a custom request class with validation rules and then call `toArray` on the request to create a data object than trying to validate the data with this package.

## When does validation happen?

Validation will always happen BEFORE a data object is created, once a data object is created, it is assumed that the data is valid.

At the moment, there isn't a way to validate data objects, so you should implement this logic yourself. We're looking into ways to make this easier in the future.

Validation runs automatically in the following cases:

- When injecting a data object somewhere and the data object gets created from the request
- When calling the `from` method on a data object with a request

On all other occasions, validation won't run automatically. You can always validate the data manually by calling the `validate` method on a data object:

```php
SongData::validate(
    ['title' => 'Never gonna give you up']
); // ValidationException will be thrown because 'artist' is missing
```

When you also want to create the object after validation was successful you can use `validateAndCreate`:

```php
SongData::validateAndCreate(
    ['title' => 'Never gonna give you up', 'artist' => 'Rick Astley']
); // returns a SongData object
```

### Validate everything

It is possible to validate all payloads injected or passed to the `from` method by setting the `validation_strategy` config option to `Always`:

```php
'validation_strategy' => \Spatie\LaravelData\Support\Creation\ValidationStrategy::Always->value,
```

Completely disabling validation can be done by setting the `validation_strategy` config option to `Disabled`:

```php
'validation_strategy' => \Spatie\LaravelData\Support\Creation\ValidationStrategy::Disabled->value,
```

If you require a more fine-grained control over when validation should happen, you can use [data factories](/docs/laravel-data/v4/as-a-data-transfer-object/factories) to manually specify the validation strategy.

## A quick glance at the validation functionality

We've got a lot of documentation about validation and we suggest you read it all, but if you want to get a quick glance at the validation functionality, here's a quick overview:

### Auto rule inferring

The package will automatically infer validation rules from the data object. For example, for the following data class:

```php
class ArtistData extends Data{
    public function __construct(
        public string $name,
        public int $age,
        public ?string $genre,
    ) {
    }
}
```

The package will generate the following validation rules:

```php
[
    'name' => ['required', 'string'],
    'age' => ['required', 'integer'],
    'genre' => ['nullable', 'string'],
]
```

The package follows an algorithm to infer rules from the data object. You can read more about it [here](/docs/laravel-data/v4/validation/auto-rule-inferring).

### Validation attributes

It is possible to add extra rules as attributes to properties of a data object:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Max(20)]
        public string $artist,
    ) {
    }
}
```

When you provide an artist with a length of more than 20 characters, the validation will fail.

There's a complete [chapter](/docs/laravel-data/v4/validation/using-validation-attributes) dedicated to validation attributes.

### Manual rules

Sometimes you want to add rules manually, this can be done as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'artist' => ['required', 'string'],
        ];
    }
}
```

You can read more about manual rules in its [dedicated chapter](/docs/laravel-data/v4/validation/manual-rules).

### Using the container

You can resolve a data object from the container.

```php
app(SongData::class);
```

We resolve a data object from the container, its properties will already be filled by the values of the request with matching key names.
If the request contains data that is not compatible with the data object, a validation exception will be thrown.

### Working with the validator

We provide a few points where you can hook into the validation process. You can read more about it in the [dedicated chapter](/docs/laravel-data/v4/validation/working-with-the-validator).

It is for example to:

- overwrite validation messages & attributes
- overwrite the validator itself
- overwrite the redirect when validation fails
- allow stopping validation after a failure
- overwrite the error bag

### Authorizing a request

Just like with Laravel requests, it is possible to authorize an action for certain people only:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function authorize(): bool
    {
        return Auth::user()->name === 'Ruben';
    }
}
```

If the method returns `false`, then an `AuthorizationException` is thrown.

## Validation of nested data objects

When a data object is nested inside another data object, the validation rules will also be generated for that nested object.

```php
class SingleData{
    public function __construct(
        public ArtistData $artist,
        public SongData $song,
    ) {
    }
}
```

The validation rules for this class will be:

```php
[
    'artist' => ['array'],
    'artist.name' => ['required', 'string'],
    'artist.age' => ['required', 'integer'],
    'artist.genre' => ['nullable', 'string'],
    'song' => ['array'],
    'song.title' => ['required', 'string'],
    'song.artist' => ['required', 'string'],
]
```

There are a few quirky things to keep in mind when working with nested data objects, you can read all about it [here](/docs/laravel-data/v4/validation/nesting-data).

## Validation of nested data collections

Let's say we want to create a data object like this from a request:

```php
class AlbumData extends Data
{
    /**
    * @param array<SongData> $songs
    */
    public function __construct(
        public string $title,
        public array $songs,
    ) {
    }
}
```

Since the `SongData` has its own validation rules, the package will automatically apply them when resolving validation
rules for this object.

In this case the validation rules for `AlbumData` would look like this:

```php
[
    'title' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'songs.*.title' => ['required', 'string'],
    'songs.*.artist' => ['required', 'string'],
]
```

More info about nested data collections can be found [here](/docs/laravel-data/v4/validation/nesting-data).

## Default values

When you've set some default values for a data object, the validation rules will only be generated if something else than the default is provided.

For example, when we have this data object:

```php
class SongData extends Data
{
    public function __construct(
        public string $title = 'Never Gonna Give You Up',
        public string $artist = 'Rick Astley',
    ) {
    }
}
```

And we try to validate the following data:

```php
SongData::validate(
    ['title' => 'Giving Up On Love']
);
```

Then the validation rules will be:

```php
[
    'title' => ['required', 'string'],
]
```

## Mapping property names

When mapping property names, the validation rules will be generated for the mapped property name:

```php
class SongData extends Data
{
    public function __construct(
        #[MapInputName('song_title')]
        public string $title,
    ) {
    }
}
```

The validation rules for this class will be:

```php
[
    'song_title' => ['required', 'string'],
]
```

There's one small catch when the validation fails; the error message will be for the original property name, not the mapped property name. This is a small quirk we hope to solve as soon as possible. 

## Retrieving validation rules for a data object

You can retrieve the validation rules a data object will generate as such:

```php
AlbumData::getValidationRules($payload);
```

This will produce the following array with rules:

```php
[
    'title' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'songs.*.title' => ['required', 'string'],
    'songs.*.artist' => ['required', 'string'],
]
```

### Payload requirement

We suggest always providing a payload when generating validation rules. Because such a payload is used to determine which rules will be generated and which can be skipped.
`````

## File: validation/manual-rules.md
`````markdown
---
title: Manual rules
weight: 4
---

It is also possible to write rules down manually in a dedicated method on the data object. This can come in handy when you want
to construct a custom rule object which isn't possible with attributes:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'artist' => ['required', 'string'],
        ];
    }
}
```

By overwriting a property's rules within the `rules` method, no other rules will be inferred automatically anymore for that property.

This means that in the following example, only a `max:20` rule will be added, and not a `string` and `required` rule:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(): array
    {
        return [
            'title' => ['max:20'],
            'artist' => ['max:20'],
        ];
    }
}

// The generated rules will look like this
[
    'title' => ['max:20'],
    'artist' => ['max:20'],
]
```

As a rule of thumb always follow these rules:

Always use the array syntax for defining rules and not a single string which spits the rules by | characters.
This is needed when using regexes those | can be seen as part of the regex

## Merging manual rules

Writing manual rules doesn't mean that you can't use the automatic rules inferring anymore. By adding the `MergeValidationRules` attribute to your data class, the rules will be merged:

```php
#[MergeValidationRules]
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(): array
    {
        return [
            'title' => ['max:20'],
            'artist' => ['max:20'],
        ];
    }
}

// The generated rules will look like this
[
    'title' => [required, 'string', 'max:20'],
    'artist' => [required, 'string', 'max:20'],
]
```

## Using attributes

It is even possible to use the validationAttribute objects within the `rules` method:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(): array
    {
        return [
            'title' => [new Required(), new StringType()],
            'artist' => [new Required(), new StringType()],
        ];
    }
}
```


You can even add dependencies to be automatically injected:

```php
use SongSettingsRepository;

class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(SongSettingsRepository $settings): array
    {
        return [
            'title' => [new RequiredIf($settings->forUser(auth()->user())->title_required), new StringType()],
            'artist' => [new Required(), new StringType()],
        ];
    }
}
```

## Using context

Sometimes a bit more context is required, in such a case a `ValidationContext` parameter can be injected as such:
Additionally, if you need to access the data payload, you can use `$payload` parameter:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function rules(ValidationContext $context): array
    {
        return [
            'title' => ['required'],
            'artist' => Rule::requiredIf($context->fullPayload['title'] !== 'Never Gonna Give You Up'),
        ];
    }
}
```

By default, the provided payload is the whole request payload provided to the data object. 
If you want to generate rules in nested data objects, then a relative payload can be more useful:

```php
class AlbumData extends Data
{
    /**
    * @param array<SongData> $songs
     */
    public function __construct(
        public string $title,
        public array $songs,
    ) {
    }
}

class SongData extends Data
{
    public function __construct(
        public string $title,
        public ?string $artist,
    ) {
    }
    
    public static function rules(ValidationContext $context): array
    {
        return [
            'title' => ['required'],
            'artist' => Rule::requiredIf($context->payload['title'] !== 'Never Gonna Give You Up'),
        ];
    }
}
```

When providing such a payload:

```php
[
    'title' => 'Best songs ever made',
    'songs' => [
        ['title' => 'Never Gonna Give You Up'],
        ['title' => 'Heroes', 'artist' => 'David Bowie'],
    ],
];
```

The rules will be:

```php
[
    'title' => ['string', 'required'],
    'songs' => ['present', 'array'],
    'songs.*.title' => ['string', 'required'],
    'songs.*.artist' => ['string', 'nullable'],
    'songs.*' => [NestedRules(...)],
]
```

It is also possible to retrieve the current path in the data object chain we're generating rules for right now by calling `$context->path`. In the case of our previous example this would be `songs.0` and `songs.1`;

Make sure the name of the parameter is `$context` in the `rules` method, otherwise no context will be injected.
`````

## File: validation/nesting-data.md
`````markdown
---
title: Nesting Data
weight: 6
---

A data object can contain other data objects or collections of data objects. The package will make sure that also for these data objects validation rules will be generated.

Let's take a look again at the data object from the [nesting](/docs/laravel-data/v4/as-a-data-transfer-object/nesting) section:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public ArtistData $artist,
    ) {
    }
}
```

The validation rules for this class would be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['array'],
    'artist.name' => ['required', 'string'],
    'artist.age' => ['required', 'integer'],
]
```

## Validating a nested collection of data objects

When validating a data object like this

```php
class AlbumData extends Data
{
    /**
    * @param array<int, SongData> $songs
    */
    public function __construct(
        public string $title,
        public array $songs,
    ) {
    }
}
```

In this case the validation rules for `AlbumData` would look like this:

```php
[
    'title' => ['required', 'string'],
    'songs' => ['present', 'array', new NestedRules()],
]
```

The `NestedRules` class is a Laravel validation rule that will validate each item within the collection for the rules defined on the data class for that collection. 

## Nullable and Optional nested data

If we make the nested data object nullable, the validation rules will change depending on the payload provided:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public ?ArtistData $artist,
    ) {
    }
}
```

If no value for the nested object key was provided or the value is `null`, the validation rules will be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['nullable'],
]
```

If, however, a value was provided (even an empty array), the validation rules will be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['array'],
    'artist.name' => ['required', 'string'],
    'artist.age' => ['required', 'integer'],
]
```

The same happens when a property is made optional:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $title,
        public ArtistData $artist,
    ) {
    }
}
```

There's a small difference compared against nullable, though. When no value was provided for the nested object key, the validation rules will be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['present', 'array', new NestedRules()],
]
```

However, when a value was provided (even an empty array or null), the validation rules will be:

```php
[
    'title' => ['required', 'string'],
    'artist' => ['array'],
    'artist.name' => ['required', 'string'],
    'artist.age' => ['required', 'integer'],
]
```

We've written a [blog post](https://flareapp.io/blog/fixing-nested-validation-in-laravel) on the reasoning behind these variable validation rules based upon payload. And they are also the reason why calling `getValidationRules` on a data object always requires a payload to be provided.
`````

## File: validation/skipping-validation.md
`````markdown
---
title: Skipping validation
weight: 7
---

Sometimes you don't want properties to be automatically validated, for instance when you're manually overwriting the
rules method like this:

```php
class SongData extends Data
{
    public function __construct(
        public string $name,
    ) {
    }
    
    public static function fromRequest(Request $request): static{
        return new self("{$request->input('first_name')} {$request->input('last_name')}")
    }
    
    public static function rules(): array
    {
        return [
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
        ];
    }
}
```

When a request is being validated, the rules will look like this:

```php
[
    'name' => ['required', 'string'],
    'first_name' => ['required', 'string'],
    'last_name' => ['required', 'string'],
]
```

We know we never want to validate the `name` property since it won't be in the request payload, this can be done as
such:

```php
class SongData extends Data
{
    public function __construct(
        #[WithoutValidation]
        public string $name,
    ) {
    }
}
```

Now the validation rules will look like this:

```php
[
    'first_name' => ['required', 'string'],
    'last_name' => ['required', 'string'],
]
```

## Skipping validation for all properties

By using [data factories](/docs/laravel-data/v4/as-a-data-transfer-object/factories) or setting the `validation_strategy` in the `data.php` config you can skip validation for all properties of a data class.
`````

## File: validation/using-validation-attributes.md
`````markdown
---
title: Using validation attributes
weight: 3
---

It is possible to add extra rules as attributes to properties of a data object:

```php
class SongData extends Data
{
    public function __construct(
        #[Uuid()]
        public string $uuid,
        #[Max(15), IP, StartsWith('192.')]
        public string $ip,
    ) {
    }
}
```

These rules will be merged together with the rules that are inferred from the data object.

So it is not required to add the `required` and `string` rule, these will be added automatically. The rules for the
above data object will look like this:

```php
[
    'uuid' => ['required', 'string', 'uuid'],
    'ip' => ['required', 'string', 'max:15', 'ip', 'starts_with:192.'],
]
```

For each Laravel validation rule we've got a matching validation attribute, you can find a list of
them [here](/docs/laravel-data/v4/advanced-usage/validation-attributes).

## Referencing route parameters

Sometimes you need a value within your validation attribute which is a route parameter.
Like the example below where the id should be unique ignoring the current id:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Unique('songs', ignore: new RouteParameterReference('song'))]
        public int $id,
    ) {
    }
}
```

If the parameter is a model and another property should be used, then you can do the following:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Unique('songs', ignore: new RouteParameterReference('song', 'uuid'))]
        public string $uuid,
    ) {
    }
}
```

## Referencing the current authenticated user

If you need to reference the current authenticated user in your validation attributes, you can use the
`AuthenticatedUserReference`:

```php
use Spatie\LaravelData\Support\Validation\References\AuthenticatedUserReference;

class UserData extends Data
{
    public function __construct(
        public string $name,
        #[Unique('users', 'email', ignore: new AuthenticatedUserReference())]
        public string $email,
    ) {   
    }
}
```

When you need to reference a specific property of the authenticated user, you can do so like this:

```php
class SongData extends Data
{
    public function __construct(
        #[Max(new AuthenticatedUserReference('max_song_title_length'))]
        public string $title,
    ) {
    }
}
```

Using a different guard than the default one can be done by passing the guard name to the constructor:

```php
class UserData extends Data
{
    public function __construct(
        public string $name,
        #[Unique('users', 'email', ignore: new AuthenticatedUserReference(guard: 'api'))]
        public string $email,
    ) {   
    }
}
```

## Referencing container dependencies

If you need to reference a container dependency in your validation attributes, you can use the `ContainerReference`:

```php
use Spatie\LaravelData\Support\Validation\References\ContainerReference;

class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Max(new ContainerReference('max_song_title_length'))]
        public string $artist,
    ) {
    }
}
```

It might be more useful to use a property of the container dependency, which can be done like this:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Max(new ContainerReference(SongSettings::class, 'max_song_title_length'))]
        public string $artist,
    ) {
    }
}
```

When your dependency requires specific parameters, you can pass them along:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[Max(new ContainerReference(SongSettings::class, 'max_song_title_length', parameters: ['repository' => 'redis']))]
        public string $artist,
    ) {
    }
}
```


## Referencing other fields

It is possible to reference other fields in validation attributes:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[RequiredIf('title', 'Never Gonna Give You Up')]
        public string $artist,
    ) {
    }
}
```

These references are always relative to the current data object. So when being nested like this:

```php
class AlbumData extends Data
{
    public function __construct(
        public string $album_name,
        public SongData $song,
    ) {
    }
}
```

The generated rules will look like this:

```php
[
    'album_name' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'song.title' => ['required', 'string'],
    'song.artist' => ['string', 'required_if:song.title,"Never Gonna Give You Up"'],
]
```

If you want to reference fields starting from the root data object you can do the following:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[RequiredIf(new FieldReference('album_name', fromRoot: true), 'Whenever You Need Somebody')]
        public string $artist,
    ) {
    }
}
```

The rules will now look like this:

```php
[
    'album_name' => ['required', 'string'],
    'songs' => ['required', 'array'],
    'song.title' => ['required', 'string'],
    'song.artist' => ['string', 'required_if:album_name,"Whenever You Need Somebody"'],
]
```

## Rule attribute

One special attribute is the `Rule` attribute. With it, you can write rules just like you would when creating a custom
Laravel request:

```php
// using an array
#[Rule(['required', 'string'])] 
public string $property

// using a string
#[Rule('required|string')]
public string $property

// using multiple arguments
#[Rule('required', 'string')]
public string $property
```

## Creating your validation attribute

It is possible to create your own validation attribute by extending the `CustomValidationAttribute` class, this class
has a `getRules` method that returns the rules that should be applied to the property.

```php
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class CustomRule extends CustomValidationAttribute
{
    /**
     * @return array<object|string>|object|string
     */
    public function getRules(ValidationPath $path): array|object|string
    {
        return [new CustomRule()];
    }
}
```

Quick note: you can only use these rules as an attribute, not as a class rule within the static `rules` method of the
data class.
`````

## File: validation/working-with-the-validator.md
`````markdown
---
title: Working with the validator
weight: 5
---

Sometimes a more fine-grained control over the validation is required. In such case you can hook into the validator.

## Overwriting messages

It is possible to overwrite the error messages that will be returned when an error fails:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function messages(): array
    {
        return [
            'title.required' => 'A title is required',
            'artist.required' => 'An artist is required',
        ];
    }
}
```

## Overwriting attributes

In the default Laravel validation rules, you can overwrite the name of the attribute as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function attributes(): array
    {
        return [
            'title' => 'titel',
            'artist' => 'artiest',
        ];
    }
}
```

## Overwriting other validation functionality

Next to overwriting the validator, attributes and messages it is also possible to overwrite the following functionality.

The redirect when a validation failed:

```php
class SongData extends Data
{
    // ...

    public static function redirect(): string
    {
        return action(HomeController::class);
    }
}
```

Or the route which will be used to redirect after a validation failed:

```php
class SongData extends Data
{
    // ...

    public static function redirectRoute(): string
    {
        return 'home';
    }
}
```

Whether to stop validating on the first failure:

```php
class SongData extends Data
{
    // ...

    public static function stopOnFirstFailure(): bool
    {
        return true;
    }
}
```

The name of the error bag:

```php
class SongData extends Data
{
    // ...

    public static function errorBag(): string
    {
        return 'never_gonna_give_an_error_up';
    }
}
```

### Using dependencies in overwritten functionality

You can also provide dependencies to be injected in the overwritten validator functionality methods like `messages`
, `attributes`, `redirect`, `redirectRoute`, `stopOnFirstFailure`, `errorBag`:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }

    public static function attributes(
        ValidationAttributesLanguageRepository $validationAttributesLanguageRepository
    ): array
    {
        return [
            'title' => $validationAttributesLanguageRepository->get('title'),
            'artist' => $validationAttributesLanguageRepository->get('artist'),
        ];
    }
}
```

## Overwriting the validator

Before validating the values, it is possible to plugin into the validator. This can be done as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
    
    public static function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $validator->errors()->add('field', 'Something is wrong with this field!');
        });
    }
}
```

Please note that this method will only be called on the root data object that is being validated, all the nested data objects and collections `withValidator` methods will not be called.
`````

## File: _index.md
`````markdown
---
title: v4
slogan: Powerful data objects for Laravel
githubUrl: https://github.com/spatie/laravel-data
branch: main
---
`````

## File: about-us.md
`````markdown
---
title: About us
weight: 9
---

[Spatie](https://spatie.be) is a webdesign agency based in Antwerp, Belgium.

Open source software is used in all projects we deliver. Laravel, Nginx, Ubuntu are just a few 
of the free pieces of software we use every single day. For this, we are very grateful. 
When we feel we have solved a problem in a way that can help other developers, 
we release our code as open source software [on GitHub](https://spatie.be/open-source).
`````

## File: changelog.md
`````markdown
---
title: Changelog
weight: 7
---

All notable changes to laravel-data are documented [on GitHub](https://github.com/spatie/laravel-data/blob/main/CHANGELOG.md)
`````

## File: installation-setup.md
`````markdown
---
title: Installation & setup
weight: 4
---

You can install the package via composer:

```bash
composer require spatie/laravel-data
```

Optionally, You can publish the config file with:

```bash
php artisan vendor:publish --provider="Spatie\LaravelData\LaravelDataServiceProvider" --tag="data-config"
```

This is the contents of the published config file:

```php

<?php

return [
    /*
     * The package will use this format when working with dates. If this option
     * is an array, it will try to convert from the first format that works,
     * and will serialize dates using the first format from the array.
     */
    'date_format' => DATE_ATOM,

    /*
     * When transforming or casting dates, the following timezone will be used to
     * convert the date to the correct timezone. If set to null no timezone will
     * be passed.
     */
    'date_timezone' => null,

    /*
     * It is possible to enable certain features of the package, these would otherwise
     * be breaking changes, and thus they are disabled by default. In the next major
     * version of the package, these features will be enabled by default.
     */
    'features' => [
        'cast_and_transform_iterables' => false,

        /*
         * When trying to set a computed property value, the package will throw an exception.
         * You can disable this behaviour by setting this option to true, which will then just
         * ignore the value being passed into the computed property and recalculate it.
         */
        'ignore_exception_when_trying_to_set_computed_property_value' => false,
    ],

    /*
     * Global transformers will take complex types and transform them into simple
     * types.
     */
    'transformers' => [
        DateTimeInterface::class => \Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer::class,
        \Illuminate\Contracts\Support\Arrayable::class => \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
        BackedEnum::class => Spatie\LaravelData\Transformers\EnumTransformer::class,
    ],

    /*
     * Global casts will cast values into complex types when creating a data
     * object from simple types.
     */
    'casts' => [
        DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
        BackedEnum::class => Spatie\LaravelData\Casts\EnumCast::class,
//        Enumerable::class => Spatie\LaravelData\Casts\EnumerableCast::class,
    ],

    /*
     * Rule inferrers can be configured here. They will automatically add
     * validation rules to properties of a data object based upon
     * the type of the property.
     */
    'rule_inferrers' => [
        Spatie\LaravelData\RuleInferrers\SometimesRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\NullableRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer::class,
    ],

    /*
     * Normalizers return an array representation of the payload, or null if
     * it cannot normalize the payload. The normalizers below are used for
     * every data object, unless overridden in a specific data object class.
     */
    'normalizers' => [
        Spatie\LaravelData\Normalizers\ModelNormalizer::class,
        // Spatie\LaravelData\Normalizers\FormRequestNormalizer::class,
        Spatie\LaravelData\Normalizers\ArrayableNormalizer::class,
        Spatie\LaravelData\Normalizers\ObjectNormalizer::class,
        Spatie\LaravelData\Normalizers\ArrayNormalizer::class,
        Spatie\LaravelData\Normalizers\JsonNormalizer::class,
    ],

    /*
     * Data objects can be wrapped into a key like 'data' when used as a resource,
     * this key can be set globally here for all data objects. You can pass in
     * `null` if you want to disable wrapping.
     */
    'wrap' => null,

    /*
     * Adds a specific caster to the Symphony VarDumper component which hides
     * some properties from data objects and collections when being dumped
     * by `dump` or `dd`. Can be 'enabled', 'disabled' or 'development'
     * which will only enable the caster locally.
     */
    'var_dumper_caster_mode' => 'development',

    /*
     * It is possible to skip the PHP reflection analysis of data objects
     * when running in production. This will speed up the package. You
     * can configure where data objects are stored and which cache
     * store should be used.
     *
     * Structures are cached forever as they'll become stale when your
     * application is deployed with changes. You can set a duration
     * in seconds if you want the cache to clear after a certain
     * timeframe.
     */
    'structure_caching' => [
        'enabled' => true,
        'directories' => [app_path('Data')],
        'cache' => [
            'store' => env('CACHE_STORE', env('CACHE_DRIVER', 'file')),
            'prefix' => 'laravel-data',
            'duration' => null,
        ],
        'reflection_discovery' => [
            'enabled' => true,
            'base_path' => base_path(),
            'root_namespace' => null,
        ],
    ],

    /*
     * A data object can be validated when created using a factory or when calling the from
     * method. By default, only when a request is passed the data is being validated. This
     * behaviour can be changed to always validate or to completely disable validation.
     */
    'validation_strategy' => \Spatie\LaravelData\Support\Creation\ValidationStrategy::OnlyRequests->value,

    /*
     * A data object can map the names of its properties when transforming (output) or when
     * creating (input). By default, the package will not map any names. You can set a
     * global strategy here, or override it on a specific data object.
     */
    'name_mapping_strategy' => [
        'input' => null,
        'output' => null,
    ],

    /*
     * When using an invalid include, exclude, only or except partial, the package will
     * throw an exception. You can disable this behaviour by setting this option to true.
     */
    'ignore_invalid_partials' => false,

    /*
     * When transforming a nested chain of data objects, the package can end up in an infinite
     * loop when including a recursive relationship. The max transformation depth can be
     * set as a safety measure to prevent this from happening. When set to null, the
     * package will not enforce a maximum depth.
     */
    'max_transformation_depth' => null,

    /*
     * When the maximum transformation depth is reached, the package will throw an exception.
     * You can disable this behaviour by setting this option to true which will return an
     * empty array.
     */
    'throw_when_max_transformation_depth_reached' => true,

    /*
     * When using the `make:data` command, the package will use these settings to generate
     * the data classes. You can override these settings by passing options to the command.
     */
    'commands' => [

        /*
         * Provides default configuration for the `make:data` command. These settings can be overridden with options
         * passed directly to the `make:data` command for generating single Data classes, or if not set they will
         * automatically fall back to these defaults. See `php artisan make:data --help` for more information
         */
        'make' => [

            /*
             * The default namespace for generated Data classes. This exists under the application's root namespace,
             * so the default 'Data` will end up as '\App\Data', and generated Data classes will be placed in the
             * app/Data/ folder. Data classes can live anywhere, but this is where `make:data` will put them.
             */
            'namespace' => 'Data',

            /*
             * This suffix will be appended to all data classes generated by make:data, so that they are less likely
             * to conflict with other related classes, controllers or models with a similar name without resorting
             * to adding an alias for the Data object. Set to a blank string (not null) to disable.
             */
            'suffix' => 'Data',
        ],
    ],

    /*
     * When using Livewire, the package allows you to enable or disable the synths
     * these synths will automatically handle the data objects and their
     * properties when used in a Livewire component.
     */
    'livewire' => [
        'enable_synths' => false,
    ],
];
```
`````

## File: introduction.md
`````markdown
---
title: Introduction
weight: 1
---

This package enables the creation of rich data objects which can be used in various ways. Using this package you only need to describe your data once:

- instead of a form request, you can use a data object
- instead of an API transformer, you can use a data object
- instead of manually writing a typescript definition, you can use... 🥁 a data object

A `laravel-data` specific object is just a regular PHP object that extends from `Data`:

```php
use Spatie\LaravelData\Data;

class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

By extending from `Data` you enable a lot of new functionality like:

- Automatically transforming data objects into resources (like the Laravel API resources)
- Transform only the requested parts of data objects with lazy properties
- Automatically creating data objects from request data and validating them
- Automatically resolve validation rules for properties within a data object
- Make it possible to construct a data object from any type you want
- Add support for automatically validating data objects when creating them
- Generate TypeScript definitions from your data objects you can use on the frontend
- Save data objects as properties of an Eloquent model
- And a lot more ...

Why would you be using this package?

- You can be sure that data is typed when it leaves your app and comes back again from the frontend which makes a lot less errors
- You don't have to write the same properties three times (in a resource, in a data transfer object and in request validation)
- You need to write a lot less of validation rules because they are obvious through PHP's type system
- You get TypeScript versions of the data objects for free

Let's dive right into it!

## Are you a visual learner?

In this talk, given at Laracon US, you'll see [an introduction to Laravel Data](https://www.youtube.com/watch?v=CrO_7Df1cBc).

## We have badges!

<section class="article_badges">
    <a href="https://github.com/spatie/laravel-data/releases"><img src="https://img.shields.io/github/release/spatie/laravel-data.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/spatie/laravel-data/blob/main/LICENSE.md"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></a>
    <a href="https://packagist.org/packages/spatie/laravel-data"><img src="https://img.shields.io/packagist/dt/spatie/laravel-data.svg?style=flat-square" alt="Total Downloads"></a>
</section>
`````

## File: questions-issues.md
`````markdown
---
title: Questions and issues
weight: 6
---

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving Laravel Data? Feel free to [create an issue on GitHub](https://github.com/spatie/laravel-data/issues), we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.
`````

## File: requirements.md
`````markdown
---
title: Requirements
weight: 3
---

This package requires:
- PHP 8.1 or higher 
- Laravel 10 or higher
`````

## File: support-us.md
`````markdown
---
title: Support us
weight: 2
---

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us). 

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).
`````

## File: third-party-packages.md
`````markdown
---
title: Third party packages
weight: 5
---

Some community members created packages that extend the functionality of Laravel Data. Here's a list of them:

- [laravel-typescript-transformer](https://github.com/spatie/laravel-typescript-transformer)
- [laravel-data-openapi-generator](https://github.com/xolvionl/laravel-data-openapi-generator)
- [laravel-data-json-schemas](https://github.com/BasilLangevin/laravel-data-json-schemas)

Created a package yourself that you want to add to this list? Send us a PR!
`````
