# Configure Filesystem

Now let's imagine your application has a storage where files are stored, that are required for your application to run proper.
We most-likely will need to synchronise these files locally to analyse a bug better, to understand the mechanisms of the application or to develop new features.

For this we have included the option to sync specific directories:

````php
<?php
declare(strict_types=1);

$globalConfig = new PHPSu\Config\GlobalConfig;
$globalConfig->addFilesystem('profilePictures', 'relativePath/toProfile/Pictures');
````

The configuration above tells phpsu that there is a directory at `relativePath/toProfile/Pictures`. The path is relative to the application root. It can be synchronised from the source ApplicationInstance to a given destination.
If your Source is located at `/var/www` on `hostAlbert` and your destination is at `/home/web/www` on `hostBerta`:
 
phpsu will sync the FileSystem `profilePictures`  the absolute directory recursively `/var/www/relativePath/toProfile/Pictures/` from `hostAlbert`
 to `/home/web/www/relativePath/toProfile/Pictures/` on `hostBerta`.

## Excluding Filesystem Elements

Sometimes we want to sync only some files from a directory.
For this purpose we added the possibility to exclude certain elements.

````php
<?php
declare(strict_types=1);

$globalConfig = new PHPSu\Config\GlobalConfig;
$globalConfig->addFilesystem('profilePictures', 'relativePath/toProfile/Pictures')
    ->addExclude('*.mp4');
````

With this phpsu will sync all files **excluding** `mp4` files.
You can add multiple excludes to your Filesystem Configuration:

````php
<?php
declare(strict_types=1);

$globalConfig = new PHPSu\Config\GlobalConfig;
$globalConfig->addFilesystem('profilePictures', 'relativePath/toProfile/Pictures')
    ->addExcludes(['*.mp4', '*.avi', '*.flv', '*.wmv', '*.mov']);
````

### Exclude Pattern

Here we have an extracts of the man page of rsync. © to the [authors of rsync]
For more information on the Exclude Pattern, see the [rsync documentation].

- if the pattern starts with a `/` then it is anchored to a particular spot in the hierarchy of files,
 otherwise it is matched against the end of the pathname.
 This is similar to a leading ^ in regular expressions.
 Thus "/foo" would match a name of "foo" at either the "root of the transfer" (for a global rule)
 or in the merge-file's directory (for a per-directory rule).
 An unqualified "foo" would match a name of "foo" anywhere in the tree because the algorithm is applied recursively from the top down;
 it behaves as if each path component gets a turn at being the end of the filename.
 Even the unanchored "sub/foo" would match at any point in the hierarchy where a "foo" was found within a directory named "sub".
- if the pattern ends with a `/` then it will only match a directory, not a regular file, symlink, or device.
- rsync chooses between doing a simple string match and wildcard matching by checking if the pattern contains one of these three wildcard characters: `*`, `?`, and `[` .
- a `*` matches any path component, but it stops at slashes.
- use `**` to match anything, including slashes.
- a `?` matches any character except a slash `/`.
- a `[` introduces a character class, such as `[a-z]` or `[[:alpha:]]`.
- in a wildcard pattern, a backslash can be used to escape a wildcard character,
but it is matched literally when no wildcards are present.
This means that there is an extra level of backslash removal when a pattern contains wildcard characters compared to a pattern that has none.
e.g. if you add a wildcard to "foo\bar" (which matches the backslash) you would need to use "foo\\bar*" to avoid the "\b" becoming just "b".
- if the pattern contains a `/` (not counting a trailing /) or a `**`, then it is matched against the full pathname, including any leading directories.
 If the pattern doesn't contain a `/` or a `**`, then it is matched only against the final component of the filename.
 (Remember that the algorithm is applied recursively so "full filename" can actually be any portion of a path from the starting directory on down.)
- a trailing `dir_name/***` will match both the directory (as if "dir_name/" had been specified) and everything in the directory (as if "dir_name/**" had been specified).
This behavior was added in version 2.6.7.

[authors of rsync]: https://git.samba.org/?p=rsync.git;a=shortlog;h=HEAD
[rsync documentation]: https://download.samba.org/pub/rsync/rsync.html#targetText=INCLUDE/EXCLUDE%20PATTERN%20RULES
