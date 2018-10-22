# Docs

## Example Config

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpsu xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:noNamespaceSchemaLocation="http://schema.phpsu.de/5.1/phpsu.xsd">
  <config>
    <filesystem type="directory">
      <option name="directory" value="data"/>
    </filesystem>
    <database type="mysql">
      <option name="user" value="dbuser01"/>
      <option name="password" value="dbpassword01"/>
      <option name="database" value="db01"/>
    </database>
  </config>
  <hosts>
    <host name="Production">
      <console type="ssh">
        <option name="host" value="example.com"/>
        <option name="user" value="user"/>
        <option name="rootDir" value="/srv/www/project"/>
      </console>
    </host>
    <host name="Testing">
      <console type="ssh">
        <option name="host" value="example.com"/>
        <option name="user" value="testuser"/>
        <option name="port" value="2222"/>
        <option name="rootDir" value="/srv/www/projectTest"/>
      </console>
    </host>
  </hosts>
</phpsu>
```

## Elements

This is the Complete list of all Elements that can bee used:

- [phpsu](./Elements/Phpsu.md)
- [config](./Elements/Config.md)
- [hosts](./Elements/Hosts.md)
- [host](./Elements/Host.md)
- [filesystem](./ConfigElements/Filesystem.md)
- [database](./ConfigElements/Database.md)
- [option](./Elements/Option.md)

### Config Elements

For more detail about database and files see the [Config Elements](./ConfigElements.md) section.

#TODO:
Document:
- exclude
- excludePattern
- include
- includePattern
- cli interface
- dry-run
- extensibility
