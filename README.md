# symfttpd

symfttpd is a set of tools to use symfony and lighttpd together,
aimed at lazy developers and sysadmins.


`spawn` will setup and start a lighttpd server with a minimal
configuration to serve one symfony project. The server will not be run as
a separate user, which is ideal for developers; also, the server logs
will be written in the project's "log" directory and will include PHP errors.


`mksymlinks` will help you create all the necessary symbolic links
to setup a project. It handles:

 * symfony symlinks (configurable)
 * web/sf (was done as an alias in Apache config examples)
 * publish-assets, even for symfony 1.0 and 1.1
 * genconf (see below)

Once configured (which is straightforward), it will take only one command
to create all the symlinks.


`genconf` will generate all the rules necessary to setup a vhost in lighttpd.
It leverages the `include_shell` directive which means no endless
copy/pasting and easy updates (only restarting lighttpd is necessary).



## spawn

If you don't want to configure a full-blown webserver, edit your host
file, edit the configuration, have a web server running even when you don't
need it, or deal with permissions, then this tool it for you.


### Quick start

First, make sure that all required symbolic links are created.
You can use `mksymlinks` (see below) to help you with that.

    cd /path/to/your-project
    /path/to/symfttpd/spawn

It will display something like that:

    lighttpd started on 127.0.0.1, port 4042.

    Available applications:
     http://127.0.0.1:4042/backend.php
     http://127.0.0.1:4042/backend_dev.php
     http://127.0.0.1:4042/frontend_dev.php
     http://127.0.0.1:4042/index.php

    Press Ctrl+C to stop serving.

All done!


### Configuration

You can alter the default lighttpd.conf template and the default paths,
by using the symfttpd.conf.php mechanism.


### Available options

* `--port=<port>` or `-p<port>`: Use a different port (default is `4042`)
    (useful for running multiple projects at the same time)
* `--all` or `-A`: Listen on all interfaces (overrides `--bind`)
* `--bind=<port>` or `-b<ip>`: Listen on a specific IP (default is `127.0.0.1`)
* `--path=<path>`: Use a different project path (default is current dir)



## mksymlinks

If you don't want to spend time with repetitive symlink creation each time you set up a new project, then this tool is for you.


### Quick start

    mkdir -p ~/Dev
    cd ~/Dev # default path, you can customize it (see Configuration)
    # get all symfony branches
    svn co http://svn.symfony-project.com/branches symfony

Create a `config/symfttpd.conf.php` file with the following contents:

    <?php
    $options['want'] = '1.2'; // The version of symfony used by your project
    $options['lib_symlink'] = 'lib/vendor/symfony'; // lib/vendor/symfony will lead to the "lib" directory of symfony

    cd /path/to/your-project
    /path/to/symfttpd/mksymlinks

All done!

You should ignore all the symlinks in your version control system, but commit `config/symfttpd.conf.php` so other developers can use symfttpd instantly if they wish to do so.


### Other typical setups

Typical contents for a symfony 1.0 project:

    <?php
    $options['want'] = '1.0';
    $options['lib_symlink'] = 'lib/symfony';
    $options['data_symlink'] = 'data/symfony'; // leads to the "data" directory of symfony

Typical contents for a symfony 1.1 project:

    <?php
    $options['want'] = '1.1';
    $options['lib_symlink'] = false;
    $options['symfony_symlink'] = 'vendor/symfony'; // leads to the root directory of symfony


### Configuration

You can override many default options globally (user-level)
or locally (project-level).
For example, putting this in `~/.symfttpd.conf.php` will change the path
of symfony 1.0 to `~/symfony-1.0` for all of your projects.

    <?php
    $options['sf_path']['1.0'] = getenv('HOME').'/symfony-1.0';

If you want to know all available options and their use,
you can open `symfttpd.conf.php` in the symfttpd directory.

As a rule, user-level config is for things that only concern your computer,
while project-level config is for things that only concern your project.


### Available options

* `--path=<path>`: Use a different project path (default is current dir)



## genconf

If you don't want to copy/paste lighttpd configs, handle regexps when
you add files, or fight rewriting issues (which can often happen
considering that most available examples are badly written),
then this tool is for you. It is also used internally by `spawn`.


### Quick start

This first part is unnecessary if you used `mksymlinks`:

    cd /path/to/example.com/config
    ln -s /path/to/symfttpd/genconf ./lighttpd.php

lighttpd config:

    $HTTP["host"] == "example.com" {
      include_shell "/path/to/example.com/config/lighttpd.php"
    }

or if you want a different default application:

    $HTTP["host"] == "mobile.example.com" {
      include_shell "/path/to/example.com/config/lighttpd.php --default=mobile"
    }

If symfttpd is running in single-process mode, you have to restart lighttpd
each time you add a file the the web/ root. Hopefully, it doesn't happen often.
Also, don't forget to run `php symfony plugin:publish-assets`, or even better,
`mksymlinks` before.


### Available options

* `-d <app>` (default): Change the default application (default being `index`)
* `-o` (only): Do not allow any other application
* `-a <app1,app2>` (allow): Useful with `-o`, allow some other applications
    (useful for allowing a `_dev` alternative, for example)
* `-n <dir1,dir2>` (nophp): Deny PHP execution in the specified directories
    (default being `uploads`).

For portability reasons, only short options (one letter) are used.

### How is the /sf/ alias handled?

Since now plugins' web dirs are handled by symbolic links,
using an alias in the server config for /sf/ doesn't make sense.
You can use `mksymlinks` to create this symlink and many
others for you (including the symlink for `genconf`!).



## FAQ

### How do I pronounce it?!

lighttpd being pronounced lighty, I recommend symfy.


### Is Windows supported?

No, and it probably never will be.


### Can I use genconf in production?

Yes. I'd say you _should_, since the command line options of `genconf` are
thought for that particular use. `genconf` does not run symfony or any other
external files, nor writes anything anywhere, so it is very little risk.


### Can I use mksymlinks in production?

Yes.


### Can I use spawn in production?

No!

### Can I start spawn in the background?

Yes, just add `&` after your command.

    /path/to/symfttpd/spawn &

To stop a running symfttpd (backgrounding or not), just run:

    /path/to/symfttpd/spawn --kill

