# symfttpd - [![Build Status](https://secure.travis-ci.org/benja-M-1/symfttpd.png?branch=master)](http://travis-ci.org/benja-M-1/symfttpd)

Symfttpd is a command line tool to run a web server in your php project,
aimed at lazy developers and sysadmins.

**This version of symfttpd is under development, this documentation can be outdated and contains some errors.**


`spawn` will setup and start a web server (lighttpd or nginx) with a minimal
configuration to serve one PHP project. The server will not be run as
a separate user, which is ideal for developers; also, the server logs
will be written in the symfttpd's "log" directory and will include PHP errors.


`genconf` will generate all the rules necessary to setup a vhost in lighttpd.
It leverages the `include_shell` directive which means no endless
copy/pasting and easy updates (only restarting lighttpd is necessary).


## Installation

### Locally

#### As PHAR archive

[Download](https://github.com/downloads/benja-M-1/symfttpd/symfttpd.phar) the `.phar` file.

#### As requirement of your project

Add Symfttpd as a requirement of your project with Composer:

    {
        …
        "require-dev": {
            "benjam1/symfttpd": "2.1.*@        },
        "config": {
            "bin-dir": "bin/"
        }
    }

Then you can use Symfttpd running ```bin/symfttpd```.


### Globally

You can run these commands to install symfttpd globally:

    $ sudo wget https://github.com/downloads/benja-M-1/symfttpd/symfttpd.phar -O /usr/local/bin/symfttpd
    $ sudo chmod +x /usr/local/bin/symfttpd

### Source

Clone this repository and checkout the latest tag.

    benjamin:~/dev benjamin $ git clone git://github.com/benja-M-1/symfttpd.git
    benjamin:~/dev benjamin $ cd symfttpd
    benjamin:~/dev/symfttpd benjamin $ git checkout v2.0.0-dev


Install the vendors with composer


    benjamin:~/dev/symfttpd benjamin $ curl -s http://getcomposer.org/installer | php
    benjamin:~/dev/symfttpd benjamin $ php composer.phar install


Then compile symfttpd in your project to create an executable `.phar` file

**In order to compile you have to set the `phar.readonly` setting to `Off` in you php.ini file.**

    benjamin:~/dev/project benjamin $ php box.phar build
    benjamin:~/dev/project benjamin $ php symfttpd.phar --help
    Usage:
     help [--xml] [command_name]
    
    Arguments:
     command               The command to execute
     command_name          The command name (default: 'help')

    Options:
     --xml                 To output help as XML
     --help (-h)           Display this help message.
     --quiet (-q)          Do not output any message.
     --verbose (-v)        Increase verbosity of messages.
     --version (-V)        Display this application version.
     --ansi                Force ANSI output.
     --no-ansi             Disable ANSI output.
     --no-interaction (-n) Do not ask any interactive question.

    Help:
     The help command displays help for a given command:

       php symfttpd.phar help list

     You can also output the help as XML by using the --xml option:

       php symfttpd.phar help --xml list



## Configuration

### How?

First of all you need to configure symfttpd with a `symfttpd.conf.php` file. 

    benjamin:~/dev/project benjamin $ php symfttpd.phar init
    
*The init command is available since the version 2.1 of Symfttpd.*

Symfttpd looks for the configuration file:

* in you home directory, this file must be named `.symfttpd.conf.php`
* in the root directory of your project (or in config/ for a symfony 1.x project)

By default symfttpd read the configuration from it's own `symfttpd.conf.php` file.

### What I have to configure?

The minimal information symfttpd needs is a project type and a project version.


    <?php
    // symfttpd.conf.php of a Symfony 2 project
    $options['project_type']    = "symfony";
    $options['project_version'] = "2";

This will tell Symfttpd to run a Lighttpd (using fastcgi) server in your Symfony 2 project.

*You can check the [reference](https://github.com/benja-M-1/symfttpd/blob/master/doc/Reference.md) for more options to configure.*


## spawn

If you don't want to configure a full-blown webserver, edit your host
file, edit the configuration, have a web server running even when you don't
need it, or deal with permissions, then this tool is for you.


### Quick start

After configuring symfttpd you only have to run the following:

    benjamin:~/dev/project benjamin $ php symfttpd.phar spawn -t

It will display something like that:

    Symfttpd - version v2.0.0-dev
    lighttpd started on 127.0.0.1, port 4042.

    Available applications:
     http://127.0.0.1:4042/app.php
     http://127.0.0.1:4042/app_dev.php

    Press Ctrl+C to stop serving.
    error: 2012-05-26 20:19:25: (log.c.166) server started 


### Configuration

You can alter the default configuration of the server, by using the `symfttpd.conf.php` mechanism. Check the [reference](https://github.com/benja-M-1/symfttpd/blob/master/doc/Reference.md#server-configuration) for more options to configure.


### Available options

* `--port=<port>` or `-p<port>`: Use a different port (default is `4042`)
    (useful for running multiple projects at the same time)
* `--bind=<port>` or `-b<ip>`: Listen on a specific IP (default is `127.0.0.1`)
* `--all` or `-A`: Listen on all interfaces (overrides `--bind`)
* `--tail` or `-t`: Display server logs in the console
    (like the UNIX `tail` command would do)


## genconf

If you don't want to copy/paste your server configs, handle regular expressions
when you add files, or fight rewriting issues (which can often happen
considering that most available examples are badly written),
then this tool is for you.


### Quick start

Typical Lighttpd config:

    $HTTP["host"] == "example.com" {
      include_shell "php /path/to/symfttpd.phar genconf -p /path/to/example.com/web"
    }

or if you want a different default application:

    $HTTP["host"] == "mobile.example.com" {
      include_shell "php /path/to/symfttpd.phar genconf -p /path/to/example.com/web -d mobile"
    }

### Available options

* `type`          The config file type (config, rules, all). (default: 'rules')
* `--path (-p)`   Path of the web directory (default: current directory)
* `--output (-o)` Directly output the generated configuration.
* `--port`        The port to listen (default: 4042)
* `--bind`        The address to bind (default: '127.0.0.1')


## FAQ

### How do I pronounce it?!

lighttpd being pronounced lighty, I recommend symfy.


### Is Windows supported?

No, and it probably never will be.


### Can I use genconf in production?

Yes. I'd say you _should_, since the command line options of `genconf` are
thought for that particular use. `genconf` does not run symfony or any other
external files, nor writes anything anywhere, so it is very little risk.


### Can I use spawn in production?

No!

### Can I start spawn in the background?

Yes, just add `&` after your command.

    php /path/to/symfttpd.phar spawn &

To stop a running symfttpd (backgrounding or not), just run:

    php path/to/symfttpd.phar spawn --kill

