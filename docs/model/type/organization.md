# Organizations

Organizations represent the legal or informal entities that other records are
associated with. A Farm is type of Organization. Modules can provide additional
Organization types.

## Type

Each Organization must have a type. All Organization types have a common set of
attributes and relationships. Specific Organization types may also add
additional attributes and relationships (collectively referred to as "fields").
Organization types are defined by modules, and are only available if their
module is enabled. The modules included with farmOS define the following
Organization types:

- Farm

## ID

Each Organization will be assigned two unique IDs in the database: a universally
unique identifier (UUID), and an internal numeric ID.

The UUID will be unique **across** farmOS databases. The internal ID will only
be unique to a **single** farmOS database. Therefore, the farmOS API uses UUIDs
to ensure that IDs pulled from multiple farmOS databases do not conflict.
Internally, farmOS modules use the internal IDs to perform CRUD operations.

## Attributes

Organizations have a number of attributes that serve to describe their meta
information. All Organizations have the same standard set of attributes. Modules
can add additional attributes.

### Standard attributes

Attributes that are common to all Organization types include:

- Name
- Status
- Notes
- Data

#### Name

Organizations must have a name that describes them. The name is used in lists of
Organizations to easily identify them at quick glance.

#### Status

Organizations can be marked as "active" or "archived" to indicate their status.
Archived Organizations will be hidden from most lists in farmOS unless they are
explicitly requested.

#### Notes

Notes can be added to an Organization to describe it in more detail. This is a
freeform text field that allows a limited set of HTML tags, including links,
lists, blockquotes, emphasis, etc.

#### Data

Organizations have a hidden "data" field on them that is only accessible via the
API. This provides a freeform plain text field that can be used to store
additional data in any format (eg: JSON, YAML, XML). One use case for this field
is to store remote system IDs that correspond to the Organization. So if the
Organization is created or managed by software outside of farmOS, it can be
identified easily. It can also be used to store additional structured metadata
that does not fit into the standard Asset attributes.

## Relationships

Organizations can be related to other records in farmOS. These relationships are
stored as reference fields on Organization records.

All Organizations have the same standard set of relationships. Modules can add
additional relationships.

Relationships that are common to all Organization types include:

- Images
- Files

#### Images

Images can be attached to Organizations. This provides a place to store photos
of the Organization.

#### Files

Files can be attached to Organizations. This provides a place to put documents
such as Shapefiles, PDFs, CSVs, or other files associated with the Organization.
