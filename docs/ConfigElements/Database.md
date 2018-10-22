# Database Config Element

```xml
<database type="auto" name="default">
  <option name="optionName" value="optionValue"/>
</database>
 ```
 
## possible Types
- [auto](#type-auto) (default)
- [mysql](#type-mysql)
- [typo3](#type-typo3)

## type auto
This is no type for itself. It detects a database type according to different measures.

e.g. composer require

If no type is recognized, mysql is used.

### options
see the options section of the detected type.

## type mysql
This will use the command mysqldump and mysql to sync tables across hosts.

### options
| options        | type        | default                   |
|----------------|-------------|---------------------------|
| database       | string      | N/A                       |
| hostName       | string      | localhost                 |
| port           | int         | 3306                      |
| user           | string      | root                      |
| password       | string      | root                      |
| socket         | string      | /var/lib/mysql/mysql.sock |
| connectionType | string      | port                      |

## type typo3

This will use the Configuration inside your TYPO3 Installation. e.g. LocalConfiguration.php, AdditionalConfiguration.php

### options
| options       | type   | default                                                                                              |
|---------------|--------|------------------------------------------------------------------------------------------------------|
| TYPO3_CONTEXT | string | [phpdotenv](https://github.com/vlucas/phpdotenv) will be used to get `TYPO3_CONTEXT`(target console) |
