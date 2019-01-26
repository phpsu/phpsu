# phpsu sync

This command is the basic command that provides the entire suit of synchronising data between two systems

## Usage

```bash
phpsu sync --help
phpsu sync -h
```  
This option shows all possible options and arguments and gives hints about the usages of the sub-commands.
  
### The Option --dry-run

```bash
phpsu sync source destination --dry-run 
``` 

This option shows how the synchronisation is going to perform without actually synchronising anything.  

### The Option --all

```bash
phpsu sync source destination --all
phpsu sync source destination -a
``` 

[//] todo add description  

### The Option --no-file

```bash
phpsu sync source destination --no-file
``` 

[//] todo add description  

### The Option --no-db

```bash
phpsu sync source destination --no-db
``` 

[//] todo add description  

### The Option --verbose

```bash
phpsu sync source destination --verbose
phpsu sync source destination -v
``` 

[//] todo add description  

### The Option --from

```bash
phpsu sync source destination --from=currentSystem
``` 

[//] todo add description  



## Examples

### Sync From Production to Local

```bash
phpsu sync production local
``` 

[//] todo add description  

### Sync From Production to Testing

```bash
phpsu sync production testing
``` 

[//] todo add description  

### Being on Production and Syncing to Testing

```bash
phpsu sync production testing --from=production
``` 

[//] todo add description  
