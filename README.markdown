# symfttpd

symfttpd is a set of tools to use symfony and lighttpd together,
aimed at lazy developers and sysadmins.



## genconf

If you don’t want to copy/paste configs, handle regexps when
you add files (considering that most examples are badly written),
then this tool is for you.


### Usage

    cd /path/to/example.com/config
    ln -s /path/to/symfttpd/genconf.php ./lighttpd.php

lighttpd config:

    $HTTP["host"] == "example.com" {
      include_shell "/path/to/example.com/config/lighttpd.php"
    }

or if you want a different default application:

    $HTTP["host"] == "mobile.example.com" {
      include_shell "/path/to/example.com/config/lighttpd.php --default=mobile"
    }

You have to restart lighttpd each time you add a file the the web/
root. Hopefully it doesn’t happen often. Also, don’t forget to run
`php symfony plugin:publish-assets` before.


### Available options

 `--default=<app>` Change the default application (default being index)
 `--only` Do not allow any other application
 `--allow=<app1,app2>` Useful with --only, allow some applications
    (useful for allowing a `_dev` alternative, for example)


### How is the /sf/ alias handled?

Since now plugins’ web dirs are handled by symbolic links,
using an alias in the server config for /sf doesn’t make sense.
There is a tool to create the symlink:

    cd /path/to/example.com
    /path/to/symfttpd/symlinksf.php

It should work with symfony from 1.0 to 1.4.



## FAQ


### How do I pronounce it?!

lighttpd being pronounced lighty, I recommend symfy.


### Is Windows supported?

No, and it probably never will be.


### Can I use it in production?

Yes. I’d say you _should_, since the command line options of `genconf.php` are
thought for that particular use. genconf does not run symfony or any other
external files, nor writes anything anywhere, so it’s pretty secure.
