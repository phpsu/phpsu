### Host

```xml
<host name="This is the name for this host">
  <!-- possible Elements in host -->
 <console/>
 <database/>
 <filesystem/>
</host>
```

#### Console

```xml
<console type="ssh">
  <option name="optionName" value="optionValue"/>
</console>
```

*possible Types:*
- local (default)
- ssh

##### Console Type local

This type is the Default type and is just the current working directory.
*All* Commands are run from within the current process. eg: mysqldump, rsync

| options | type   | default                     |
|---------|--------|-----------------------------|
| rootDir | string | `echo $PWD`(target console) |

##### Console Type ssh

This type connects to a Server via ssh. 
*All* Commands are run from within this connection. eg: mysqldump, rsync

| options  | type   | default                      |
|----------|--------|------------------------------|
| rootDir  | string | `echo $PWD`(target console)  |
| hostName | string | N/A                          |
| user     | string | `echo $USER`(source console) |
| port     | int    | 22                           |
