<?php

class Model {

    public static function ExecQuery($sql, $params = null) {
        return Doo::db()->fetchAll($sql, $params);
    }

    public static function ExecNonQuery($sql, $params = null) {
        return Doo::db()->query($sql, $params);
    }

    static function SafeLiteral($inputSQL) {
        if (strlen(trim($inputSQL)) > 0) {
            return str_replace("'", "''", $inputSQL);
        } else {
            return $inputSQL;
        }
    }

    public static function SelectString($function, &$collection, $fntype = null, $alias = null, $iterations = null) {
        $alias = empty($alias) ? $function : $alias;
        $sql = "";

        if (count($collection) > 0) {
            $entries = "";
            $keys = "";
            $values = "";
            foreach ($collection as $key => $value) {
                $keys = $key . ':=';
                $values = ' :' . $key . ',';
                $entries .= $keys . $values;
            }

            $entries = substr($entries, 0, strlen($entries) - 1);

            $iterations = !empty($iterations) ? ",{$iterations}" : "";

            if (isset($fntype) && !empty($fntype)) {
                $sql = "select {$function}({$fntype}({$entries})$iterations) {$alias}";
            } else {
                $sql = "select {$function}({$entries}$iterations) {$alias}";
            }
        }

        return $sql;
    }

    public static function SelectStringVariadic($function, &$collection, &$id, $fntype, $alias = null) {
        $alias = empty($alias) ? $function : $alias;
        $sql = "";
        $id_section = "";
        $content_section = "";

        if (count($collection) > 0) {

            //retrieve the id section
            if (count($id) > 0) {
                $entries = "";
                $keys = "";
                $values = "";
                foreach ($id as $key => $value) {
//                    $keys = $key . ':=';
//                    $values = ' :' . $key . ',';
                    if (is_array($value)) {
                        $value = "'{$value['value']}'::{$value['type']}";
                    } else {
                        $value = "{$value}::integer";
                    }
                    $keys = "";
                    $values = "{$value},";
                    $entries .= $keys . $values;
                }
                //id section
                $id_section = $entries;
            }

            foreach ($collection as $entry) {
                $keys = "";
                $values = "";
                $element = "";
                $entries = "";

                if (isset($entry['*element*'])) {
                    $element = "_{$entry['*element*']}";
                    unset($entry['*element*']);
                }
                foreach ($entry as $key => $value) {
                    $keys = str_replace($element, '', $key) . ':=';
                    $values = ' :' . $key . ',';
                    $entries .= $keys . $values;
                }
                $entries = substr($entries, 0, strlen($entries) - 1);

                $content_section .= "{$fntype}({$entries}),";
            }

            $content_section = substr($content_section, 0, strlen($content_section) - 1);

            $sql = "select {$function}({$id_section}{$content_section}) {$alias}";
        }

        return $sql;
    }

    static function InsertString($table, &$collection) {
        $sql = "";
        $unsetkeys = array();

        if (count($collection) > 0) {
            $keys = "";
            $values = "";
            foreach ($collection as $key => $value) {

                if ($value === "" || $value === null) {
                    $unsetkeys[] = $key;
                    continue;
                }

                $keys .= $key . ',';
                $values .= ':' . $key . ",";
            }

            $keys = substr($keys, 0, strlen($keys) - 1);
            $values = substr($values, 0, strlen($values) - 1);

            $sql = "insert into {$table} ({$keys}) values({$values})";
        }

        foreach ($unsetkeys as $key => $value) {
            unset($collection[$value]);
        }

        return $sql;
    }

    static function UpdateString($table, &$collection, &$wherecollection, $notnumeric = null) {
        $sql = "";
        $unsetkeys = array();
        if (count($collection) > 0) {
            $keys = '';
            $values = '';
            $sql = "update {$table} set ";
            foreach ($collection as $key => $value) {

                if ($value === "" || $value === null) {
                    $unsetkeys[] = $key;
                    continue;
                }

                $keys = $key . '=';
                $values = ':' . $key . ",";

                $sql .= $keys . $values;
            }

            $sql = substr($sql, 0, strlen($sql) - 1);

            $sql .= " where ";

            foreach ($wherecollection as $key => $value) {
                $keys = '' . $key . '=';
                $values = ':' . $key . " and ";

                $sql .= $keys . $values;
            }
            $sql = substr($sql, 0, strlen($sql) - 5);
        }
        foreach ($unsetkeys as $key => $value) {
            unset($collection[$value]);
        }
        return $sql;
    }

    static function Likes($filterscollection) {
        $sql = " ";
        $keys = "";
        $values = "";
        foreach ($filterscollection as $key => $value) {
            if (strlen(trim($value)) === 0) {
                continue;
            }

            $keys = $key . '::character varying ilike ';
            $values = ":{$key} or ";

            if (!empty($values)) {
                $sql .= $keys . $values;
            }
        }
        $sql = substr($sql, 0, strlen($sql) - 4);

        return $sql;
    }

    static function Equals(&$filterscollection) {
        $sql = " ";
        $keys = "";
        $values = "";
        foreach ($filterscollection as $key => $value) {
            if (strlen(trim($value)) === 0 && $value !== NULL) {
                continue;
            }
            if ($value !== NULL) {
                $keys = 'lower(' . $key . '::character varying)=';
                $values = "lower(:{$key}) and ";
            } else {
                $keys = 'coalesce(' . $key . ',0)=';
                $values = ":{$key} and ";
                //change the value
                $filterscollection[$key] = 0;
            }

            if (!empty($values)) {
                $sql .= $keys . $values;
            }
        }
        $sql = substr($sql, 0, strlen($sql) - 4);

        return $sql;
    }

    static function Between(&$filterscollection) {
        $sql = " ";
        $keys = "";
        $values = "";
        $index = 0;
        foreach ($filterscollection as $key => $value) {
            if (strlen(trim($value)) === 0 && $value !== NULL) {
                continue;
            }

            $key = substr($key, 0, strlen($key) - 2);

            $start = "{$key}_1";
            $end = "{$key}_2";

            $keys = "{$key}::date between ";
            $values = ":{$start} and :{$end} ";

            if (!empty($values)) {
                $sql .= $keys . $values;
            }
            //only 1 between set allowed for now!
            break;
        }
        $sql = substr($sql, 0, strlen($sql) - 1);

        return $sql;
    }

    static function EqualsOr($filterscollection) {
        $sql = " ";
        $keys = "";
        $values = "";
        foreach ($filterscollection as $key => $value) {
            if (strlen(trim($value)) === 0 && $value !== NULL) {
                continue;
            }
            if ($value !== NULL) {
                $keys = 'lower(' . $key . '::character varying)=';
                $values = "lower(:{$key}) or ";
            } else {
                $keys = 'coalesce(' . $key . ',0)=';
                $values = ":{$key} and ";
                //change the value
                $filterscollection[$key] = 0;
            }

            if (!empty($values)) {
                $sql .= $keys . $values;
            }
        }
        $sql = substr($sql, 0, strlen($sql) - 4);

        return $sql;
    }

    static function NotEquals($filterscollection) {
        $sql = " ";
        $keys = "";
        $values = "";
        foreach ($filterscollection as $key => $value) {
            if (strlen(trim($value)) === 0 && $value !== NULL) {
                continue;
            }
            if ($value !== NULL) {
                $keys = 'lower(' . $key . '::character varying)<>';
                $values = "lower(:{$key}) and ";
            } else {
                $keys = 'coalesce(' . $key . ',0)<>';
                $values = ":{$key} and ";
                //change the value
                $filterscollection[$key] = 0;
            }

            if (!empty($values)) {
                $sql .= $keys . $values;
            }
        }
        $sql = substr($sql, 0, strlen($sql) - 4);

        return $sql;
    }

    static function NotEqualsOr($filterscollection) {
        $sql = " ";
        $keys = "";
        $values = "";
        foreach ($filterscollection as $key => $value) {
            if (strlen(trim($value)) === 0 && $value !== NULL) {
                continue;
            }
            if ($value !== NULL) {
                $keys = 'lower(' . $key . '::character varying)<>';
                $values = "lower(:{$key}) or ";
            } else {
                $keys = 'coalesce(' . $key . ',0)<>';
                $values = ":{$key} and ";
                //change the value
                $filterscollection[$key] = 0;
            }

            if (!empty($values)) {
                $sql .= $keys . $values;
            }
        }
        $sql = substr($sql, 0, strlen($sql) - 4);

        return $sql;
    }

}

?>