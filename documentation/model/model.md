# The Model

model is actually not an accurate name because it includes not only the bare model, but is actually the entire database
abstraction layer including:

- ORM: classes that map database tables (entity, relationship)
- flow control structures: FlowControl (TODO maybe rename Flow)
- database access handler / PDO wrapper
- entity and attribute change tracking control structures
- classes and methods to define custom entity types and attributes (AttributeDefinition)
- attribute (input) validation
- database request builder:
	- where conditions
- file handler


=> overall: model contains the data/entity-oriented logic, controller contains the request-oriented logic

## Contents
### Entity (and EntityList)

### Relationship (and RelationshipList)

### Attributes trait

### AttributeDefinition

### StaticObject

### Collection

### DatabaseAccess (and Exceptions)

### Database Requests

### Flow

### FileHandler
