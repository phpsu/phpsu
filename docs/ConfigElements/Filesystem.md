# Filesystem Config Element

```xml
<filesystem type="directory" name="default">
  <option name="optionName" value="optionValue"/>
</filesystem>
 ```
 
## possible Types
- [directory](#type-directory) (default)


## type directory

With this type you select a directory and its contents, which should be synced.

### options
| options   | type   | default       |
|-----------|--------|---------------|
| directory | string | ``echo $PWD`` |
