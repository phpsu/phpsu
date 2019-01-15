# Config Elements

## Elements

- [Filesystem Config](./ConfigElements/Filesystem.md)
- [Database Config](./ConfigElements/Database.md)

## Basic concepts

Each Config element *can* be used inside the global config section and inside each host.
Each Config element has the attribute name, which has the default value "default". 

### Concept of overwrites

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpsu xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:noNamespaceSchemaLocation="http://schema.phpsu.de/1.0/phpsu.xsd">
  <config>
    <filesystem name="dataDir">
      <option name="directory" value="data"/>
    </filesystem>
  </config>
  <hosts>
    <host name="Production">
      <console type="ssh">
        <option name="host" value="Production"/>
        <option name="rootDir" value="/rootDir"/>    
      </console>
      <filesystem name="dataDir">
        <option name="directory" value="../public/data"/>  
      </filesystem>
    </host>
  </hosts>
</phpsu>
```

This config causes the path on the host to be different from the path on other hosts. e.g. "local"
If the now sync from ``Production->local`` than it is mostly identical with the command ``rsync Production:/rootDir/../public/data/* ./data/``.
