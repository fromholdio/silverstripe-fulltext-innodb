<?php

namespace Fromholdio\FullTextInnoDB\ORM;

use SilverStripe\ORM\Connect\MySQLSchemaManager;

class MySQLFullTextInnoDBSchemaManager extends MySQLSchemaManager
{
    public function createTable($table, $fields = null, $indexes = null, $options = null, $advancedOptions = null)
    {
        $fieldSchemas = $indexSchemas = "";

        if (!empty($options[self::ID])) {
            $addOptions = $options[self::ID];
        } else {
            $addOptions = "ENGINE=InnoDB";
        }

        if (!isset($fields['ID'])) {
            $fields['ID'] = "int(11) not null auto_increment";
        }
        if ($fields) {
            foreach ($fields as $k => $v) {
                $fieldSchemas .= "\"$k\" $v,\n";
            }
        }
        if ($indexes) {
            foreach ($indexes as $k => $v) {
                $indexSchemas .= $this->getIndexSqlDefinition($k, $v) . ",\n";
            }
        }

        // Switch to "CREATE TEMPORARY TABLE" for temporary tables
        $temporary = empty($options['temporary'])
            ? ""
            : "TEMPORARY";

        $this->query("CREATE $temporary TABLE \"$table\" (
				$fieldSchemas
				$indexSchemas
				primary key (ID)
			) {$addOptions}");

        return $table;
    }

    public function alterTable(
        $tableName,
        $newFields = null,
        $newIndexes = null,
        $alteredFields = null,
        $alteredIndexes = null,
        $alteredOptions = null,
        $advancedOptions = null
    ) {
        if ($this->isView($tableName)) {
            $this->alterationMessage(
                sprintf("Table %s not changed as it is a view", $tableName),
                "changed"
            );
            return;
        }
        $alterList = array();

        if ($newFields) {
            foreach ($newFields as $k => $v) {
                $alterList[] .= "ADD \"$k\" $v";
            }
        }
        if ($newIndexes) {
            foreach ($newIndexes as $k => $v) {
                $alterList[] .= "ADD " . $this->getIndexSqlDefinition($k, $v);
            }
        }
        if ($alteredFields) {
            foreach ($alteredFields as $k => $v) {
                $alterList[] .= "CHANGE \"$k\" \"$k\" $v";
            }
        }
        if ($alteredIndexes) {
            foreach ($alteredIndexes as $k => $v) {
                $alterList[] .= "DROP INDEX \"$k\"";
                $alterList[] .= "ADD " . $this->getIndexSqlDefinition($k, $v);
            }
        }

        $dbID = self::ID;
        if ($alteredOptions && isset($alteredOptions[$dbID])) {
            $this->query(sprintf("ALTER TABLE \"%s\" %s", $tableName, $alteredOptions[$dbID]));
            $this->alterationMessage(
                sprintf("Table %s options changed: %s", $tableName, $alteredOptions[$dbID]),
                "changed"
            );
        }

        $alterations = implode(",\n", $alterList);
        $this->query("ALTER TABLE \"$tableName\" $alterations");
    }
}
