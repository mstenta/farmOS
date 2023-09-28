# CSV importers

[CSV](https://en.wikipedia.org/wiki/Comma-separated_values) files are an easy
way to import data into farmOS.

The farmOS Import CSV module (`farm_import_csv`) provides a framework for
building CSV importers using Drupal's
[Migrate API](https://www.drupal.org/docs/drupal-apis/migrate-api).

The module uses this framework to provide "default" CSV importers for each
asset, log, and taxonomy term type. These are useful if you can fit your data
into them, but in some cases a more customized CSV template and/or import logic
might be necessary.

## YML File

Modules can provide their own CSV importers by adding a single YML file to
their `config/install` directory, which will add the importer when the module
is installed.

The YML file defines all the configuration necessary for the importer,
using the Drupal [Migrate Plus](https://drupal.org/project/migrate_plus)
module's `migration` configuration entity type.

The basic template for a CSV importer is as follows (replace all
`{{ VARIABLE }}` sections with your specific configuration):

```yaml
langcode: en
status: true
dependencies: {  }
id: {{ UNIQUE_ID }}
label: '{{ LABEL }}'
migration_group: farm_import_csv
migration_tags: []
source:
  plugin: csv_file
destination:
  plugin: 'entity:{{ ENTITY_TYPE }}'
process:
  {{ MAPPING_CONFIG }}
migration_dependencies: {  }
third_party_settings:
  farm_import_csv:
    access:
      permissions:
        - {{ PERMISSION_STRING }}
    columns:
      {{ COLUMN_DESCRIPTIONS }}
```

- `{{ UNIQUE_ID }}` must be a unique machine-name for the importer, consisting
  of only alphanumeric characters and underscores.
- `{{ LABEL }}` will be the name of the importer shown in the farmOS UI.
- `{{ ENTITY_TYPE }}` should be `asset`, `log`, or `taxonomy_term`.
- `{{ MAPPING_CONFIG }}` is where all the Drupal Migrate API's `process`
  pipeline configuration is defined. This is responsible for mapping CSV column
  names to entity fields (or additional processing).
  See [Mapping columns](#mapping-columns) below for more information.
- `{{ PERMISSION_STRING }}` should be a Drupal permission that the user must
  have in order to use the importer. Multiple permissions can be included on
  separate lines.
- `{{ COLUMN_DESCRIPTIONS }}` should be an array of items with `name` and
  `description` keys to describe each CSV column.

### Example

Here is an example of an importer for "egg harvests":

`config/install/migrate_plus.migration.egg_harvest.yml`

```yaml
langcode: en
status: true
dependencies: {  }
id: egg_harvest
label: 'Egg harvest importer'
migration_group: farm_import_csv
migration_tags: []
source:
  plugin: csv_file
  constants:
    UNIT: egg(s)
    LOG_NAME_PREFIX: Collected
destination:
  plugin: 'entity:log'
process:
  # Hard-code the bundle.
  type:
    plugin: default_value
    default_value: harvest
  # Parse the log timestamp with strtotime() from Date column.
  timestamp:
    plugin: callback
    callable: strtotime
    source: Date
  # Create or load "egg(s)" unit term.
  _unit:
    plugin: entity_generate
    entity_type: taxonomy_term
    value_key: name
    bundle_key: vid
    bundle: unit
    source: constants/UNIT
  # Create a quantity from the Eggs column.
  quantity:
    - plugin: skip_on_empty
      source: Eggs
      method: process
    - plugin: static_map
      map: { }
      default_value: [ [ ] ]
    - plugin: create_quantity
      default_values:
        type: standard
        measure: count
      values:
        value: Eggs
        units: '@_unit'
  # Auto-generate the log name.
  name:
    plugin: concat
    source:
      - constants/LOG_NAME_PREFIX
      - Eggs
      - constants/UNIT
    delimiter: ' '
  # Mark the log as done.
  status:
    plugin: default_value
    default_value: done
migration_dependencies: {  }
third_party_settings:
  farm_import_csv:
    access:
      permissions:
        - create harvest log
    columns:
      - name: Date
        description: Date of egg harvest.
      - name: Eggs
        description: Number of eggs harvested.
```

This will be able to import a CSV with `Date` and `Eggs` collumns, creating a
harvest log named "Collected [num] egg(s)" for each row with the number of eggs
saved in a standard `count` quantity:

`egg-harvests.csv`

```csv
Date,Eggs
2023-09-15,12
2023-09-16,14
2023-09-17,9
```

### Mapping columns

...

## Resources

A complete overview of all the options available with Drupal's Migrate API is
outside the scope of this documentation, but the following links are a good
place to learn about what's possible.

Note that CSVs are just one type of data source for migrations. These resources
are not specific to CSV imports, but the same principles apply generally.

[UnderstandingDrupal.com](https://understanddrupal.com) offers a free course
called [31 days of Drupal migrations](https://understanddrupal.com/courses/31-days-of-migrations/)
which covers Drupal migrations in depth.
