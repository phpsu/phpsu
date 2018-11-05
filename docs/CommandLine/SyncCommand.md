# The SyncCommand

This command is the basic command that provides the entire suit of synchronising data between two systems

## Basic Usage

```bash
  phpsu sync --help 
  ```  
This option shows all possible options and arguments and gives hints about the usages of the sub-commands.
  
```bash
phpsu sync --dry-run 
``` 

This option shows how the synchronisation is going to perform without actually synchronising anything.  

```bash
phpsu sync [direction]
``` 

The argument **[direction]** represents the core of the application. With this,
the user is able to specify from where and to where data will be synchronised.
The direction is implemented through an arrow between two configured systems.
The configuration process is being shown [here](../index.md).

Let's imagine, we have two hosts called ``production`` as live-server and ``local`` as the local system and
we'd want to synchronise the data between those two systems. The direction command would look like this:
```bash
phpsu sync production to local
``` 
The literal such as ``to`` in that command is required. ***Important***: The command does not work by just typing the two systems.
Only the literal ``to`` pre-defines the direction of synchronising and has no counterpart. It's always from system1 to system 2.
If you do not want to be bound by this specification but rather control the direction on your own,
you can use a number of literals such as:

Production to Local by: ``→`` ``:=`` ``->`` ``:-`` <br>
Local to Production by: ``←`` ``=:`` ``<-`` ``-:``

Currently it is also required, that the system-names are exactly as specified in the configuration XML.