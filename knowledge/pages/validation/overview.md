# How Validation Works

## When Is a Rule Applied?

When an entity is saved, it will be validated against any rule that is determined to be applicable. What determines that is explained next.

### Rule's Tag Matches Tag Usage on Entity

If an existing tag matches a rule's `FieldTagConstraint::TAG_REGEX` or equals a rule's `FieldTagConstraint::TAG_VALUE`, then that rule will be applied.

### Rule Has Entity Limit(s) and at Least Entity/Bundle Rule Filters Match Entity

If a rule does not match the above, yet it contains any minimum requirement (`FieldTagConstraint::ENTITY_MIN, FieldTagConstraint::FIELD_MIN, FieldTagConstraint::ITEM_MIN`) AND all of the where statements (`FieldTagConstraint::FOR_ENTITIES, FieldTagConstraint::FOR_BUNDLES, FieldTagConstraint::FOR_FIELDS`) match the entity, then that rule will be applied. This second case is what allows you to require tags, since you want to catch when the usage is zero for a given situation. [See this page for details.](@required_tags)

### Rule Has Field or Item Limits and All Filters Match Entity

## How Is It Applied?

When a rule is applied to a tag, all of the where statements must be true or the rule is violated.

All usage constraints on the rule for that tag must be true or the rule is violated.
