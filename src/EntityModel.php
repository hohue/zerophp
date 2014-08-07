<?php
namespace ZeroPHP\ZeroPHP;

class EntityModel {
    public static function loadEntity($entity_id = null, $structure, $attributes = array()) {
        $db = \DB::table($structure['#name']);
        self::_buildLoadEntityWhere($db, $entity_id, $structure, $attributes);
        self::_buildLoadEntityOrder($db, $structure, $attributes);

        //@todo 6 Hack for old code
        // Ra soat lai code de remove doan code nay
        /*$entities = array();
        foreach ($query as $row) {
            $entities[$row->{$structure['#id']}] = $row;
        }*/

        return $db->get();
    }

    private static function _buildLoadEntityWhere(&$db, $entity_id = 0, $structure, $attributes = array()) {
        // Filter
        $zerophp =& zerophp_get_instance();
        if (isset($zerophp->request)) {
            $filter = $zerophp->request->filter();
            if ((!isset($attributes['filter']) || $attributes['filter'] == true)
                && count($filter) > 1 && !empty($filter['name']) && $filter['name'] == $structure['#name']) {
                foreach ($filter as $key => $value) {
                    if (isset($structure['#fields'][$key])) {
                        $attributes['where'][$key] = $value;
                    }
                }
            }
        }

        if ($entity_id) {
            $db->where($structure['#id'], '=', $entity_id);
        }

        if (isset($attributes['where']) && count($attributes['where'])) {
            foreach ($attributes['where'] as $key => $value) {
                $key = explode(' ', $key);
                $key[1] = isset($key[1]) ? $key[1] : '=';

                $db->where($key[0], $key[1], $value);
            }
        }
    }

    private static function _buildLoadEntityOrder($db, $structure, $attributes) {
        $order = isset($attributes['order']) ? $attributes['order'] : array();
        if (count($order)) {
            foreach ($order as $key => $value) {
                $db->orderBy($key, $value);
            }
        }

        if (!isset($order['weight']) && isset($structure['#fields']['weight'])) {
            $db->orderBy('weight', 'ASC');
        }

        if (!isset($order['updated_at']) && isset($structure['#fields']['updated_at'])) {
            $db->orderBy('updated_at', 'DESC');
        }
        elseif (!isset($order['created_at']) && isset($structure['#fields']['created_at'])) {
            $db->orderBy('created_at', 'DESC');
        }

        if (!isset($order['title']) && isset($structure['#fields']['title'])) {
            $db->orderBy('title', 'ASC');
        }
    }

    public static function createEntity($entity, $structure) {
        $db = \DB::table($structure['#name']);
        return $db->insertGetId(zerophp_object_to_array($entity));
    }

    public static function deleteEntity($entity_ids, $structure) {
        $db = \DB::table($structure['#name']);

        if (!is_array($entity_ids)) {
            $entity_ids = array($entity_ids);
        }

        if (isset($structure['#can_not_delete'])) {
            $entity_ids = array_diff($entity_ids, $structure['#can_not_delete']);
        }

        $db->whereIn($structure['#id'], $entity_ids);
        $db->delete();
    }

    public static function updateEntity($entity, $structure) {
        $db = \DB::table($structure['#name'])
            ->where($structure['#id'], '=', $entity->{$structure['#id']})
            ->update(zerophp_object_to_array($entity));

        return $entity->{$structure['#id']};
    }

    public static function saveReference($reference, $entity_id, $structure) {
        foreach (array_keys($reference) as $key) {
            // Delete all of reference
            $db = \DB::table($structure['#name'] . '_' . $structure['#fields'][$key]['#reference']['name']);
            $db->where($structure['#id'], $entity_id);
            $db->where('field', $key);
            $db->delete();

            // Update new reference
            if (count($reference[$key])) {
                $ref_obj = new $structure['#fields'][$key]['#reference']['class'];
                $ref_structure = $ref_obj->getStructure();

                $data = array();
                foreach ($reference[$key] as $value) {
                    if (is_object($value)) {
                        $value = $value->{$ref_structure['#id']};
                    }

                    $data[] = array(
                        'field' => $key,
                        $structure['#id'] => $entity_id,
                        $ref_structure['#id'] => $value,
                    );
                }

                $db->insert($data);
            }
        }
    }

    public static function loadReference($field, $entity_id, $structure, $ref_structure) {
        $db = \DB::table($structure['#name'] . '_' . $ref_structure['#name']);

        $db->where('field', $field);
        $db->where($structure['#id'], $entity_id);
        $query = $db->get();

        $reference = array();
        foreach ($query as $row) {
            $reference[$row->{$ref_structure['#id']}] = $row->{$ref_structure['#id']};
        }

        return $reference;
    }
}

// CHECKED