<!--
id: retroactive
tags: ''
-->

# Retroactive Validation

You can use the following snippets to help in writing rules retroactively.

Determine the entity types currently having tags:

```sql
select distinct(parent_entity) as 'only_on_entities'
from field_tag;
```

Then for each entity type do something like the flowing examples for nodes and paragraphs.

```sql
select distinct tag           as tag_value,
                parent_entity as only_on_entities,
                n.type        as only_on_bundles,
                field_name    as only_on_fields
from field_tag ft
         join node n on n.nid = ft.parent_id
where deleted < 1
  and parent_entity = 'node'
order by tag_value;
```

```sql
select distinct tag           as tag_value,
                parent_entity as only_on_entities,
                p.type        as only_on_bundles,
                field_name    as only_on_fields
from field_tag ft
         join paragraphs_item p on p.id = ft.parent_id
where deleted < 1
  and parent_entity = 'paragraph'
order by tag_value;
```

Then you can analyze single tags with something like this; notice the `tag like...` addition.

```sql
select distinct tag           as tag_value,
                parent_entity as only_on_entities,
                n.type        as only_on_bundles,
                field_name    as only_on_fields
from field_tag ft
         join node n on n.nid = ft.parent_id
where deleted < 1
  and parent_entity = 'node'
  and tag like "%vimeo%";
```
