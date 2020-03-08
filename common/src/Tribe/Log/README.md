# Monolog-based logging

We've introduced a [Monolog based](https://github.com/Seldaek/monolog) logger in our common libraries.  
You can find more information about all the possibilities this opens [on the library documentaion](https://seldaek.github.io/monolog/), but this document will serve as an introduction to the essentials of its day to day use.

## When should I log?

Whenever you feel you might have to debug this in the future and could use that information.  

> Pro tip: if you, as a developer, find yourself using `var_dump` and `error_log` a lot, then you should log instead. Someone, someday, will have your same issue.

Worried about "spamming" the logs? [Read here](#logging-levels--or-stuff-does-not-appear-in-the-log).

## This will deprecate the old logger, but not yet

At first we're not replacing the "old" logger with this new one, we're just asking you **to stop using the old logger** in your code from now on and use the new, Monolog-based, one.  
The old logger still offers file-based logging and connections to the UI the new logger is not yet offering; the current implementation will allow us, in the future, to log **everything** with the Monolog-based logger, but, currently, intercepting log messages from the "old" logger requires manual activation, see the following section. 

To be clear: this is what we mean by "old" or "legacy" logger:

```php
<?php
tribe( 'logger' )->log_debug( 'Some debug information', 'The source' );
tribe( 'logger' )->log( 'Some information', Tribe__Log::DEBUG,  'The source' );
```

### Intercepting legacy logger logs with the new Monolog logger

The Monolog-based logger will handle logging coming from the legacy logger only if explicitly told so.  
 
 You can activate this function with this code:
 
 ```php
<?php
add_filter( 'tribe_log_use_action_logger', '__return_true' );
 ```

Once this is in your code any call to the legacy logger wil be redirected to the new one.

## Using the logger

The new logger listens on the `tribe_log` action.  
By default it will log to the `default` channel (see [Monolog documentation for more information about channels](https://seldaek.github.io/monolog/doc/01-usage.html#leveraging-channels)).  

So the code below will log a **debug** to the **default** channel with a source of **ea_client**:

```php
<?php
do_action( 
    'tribe_log', 
    'debug', 
    'ea_client', 
    [ 'action' => 'updated', 'post_id' => $id, 'origin' => $origin ]
);
```

The logger listening on the action will consume three parameters:

1. `level` - `debug` in the example; is the level of the log; available levels, in increasing value of urgency are: `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`. Use each level wisely.
2. `source` - `ea_client` in this example; is the source of the log; this is a human-readable value; consistency is king here.
3. `context` - the array in this example; this is an associative array that will be logged to define the context of the log. Think of this as something that will provide the details required to unpack what **was** happening when the log entry was created. Provide enough context to make it clear, but avoid bloating it.

## Where are my logs?

The initial implementation of the new logger will write, by default, to the **PHP error** log.  

We're using Monolog to allow us, and third parties, to "attach" and "deatach" loggers as required.  
By default we're formatting logs using canonical lines( read more [here](https://brandur.org/logfmt) and [here](https://blog.codeship.com/logfmt-a-log-format-thats-easy-to-read-and-write/)) to make our log entries both human-readable and machine parsable (e.g. by a tool like [this](https://www.npmjs.com/package/logfmt)).  

The output format of the example above would be this:

```
[22-Aug-2019 15:50:42 UTC] tribe-canonical-line channel=default level=debug source=ea_client action=updated post_id=23 origin=ical
```

What about legacy logs?  
Their format would not be formatted to the canonical line style:

```
[22-Aug-2019 16:03:33 UTC] tribe.default.DEBUG: The source: debug information 
```

### Logging levels ( or "stuff does not appear in the log")

By default we're only logging Warnings and above.  
This means all your `debug` level logs are  being ignored.  

In production we do not want to fill people logs with pointless information, but you can control the level of logging: any log equal or above the specified level will be logged.

You can control the logging level with the `tribe_log_level` filter:

```php
<?php
add_filter( 
    'tribe_log_level',
	static function () {
        // Only log errors or above. 
		return Monolog\Logger::ERROR;
	}
);
```

### Logging channels

The default logging channel is `default`, you've seen that in the example log lines above.  

But how can I change the channel?

Easy:

```php
<?php
tribe( 'monolog' )->set_global_channel( 'my_channel' );
do_action( 'tribe_log', 'debug', 'my_source', [ 'foo' => 'bar' ] );
tribe( 'logger' )->log_debug( 'Some debug information', 'My source' );
```

You can do the same using the legacy logger, [if enabled][0527-0003]:

```php
<?php
tribe( 'logger' )->use_log( 'my_channel' );
```

Any log produced after the call will log to the `my_channel` channel; this will apply to the legacy logger too ([if redirected][0527-0003]):

```
[22-Aug-2019 15:50:42 UTC] tribe-canonical-line channel=default level=debug source=ea_client action=updated post_id=23 origin=ical
[22-Aug-2019 15:51:13 UTC] tribe-canonical-line channel=my_channel level=debug source=my_source foo=bar
[22-Aug-2019 16:03:33 UTC] tribe.my_channel.DEBUG: My source: Some debug information 
```

## I want to use this right now to debug my code

Copy and paste this in a plugin, or must-use plugin.  
If you're using a plugin remember to activate it.

```php
<?php
/**
 *  Plugin Name: Modern Tribe Logger Control
 * Plugin Description: Control the behavior of Modern Tribe Monolog-based logger.
 */
add_filter( 
    'tribe_log_level',
	static function () {
        // Control the min level of logging.
		return Monolog\Logger::DEBUG;
	}
);

// Redirect legacy logger calls.
add_filter( 'tribe_log_use_action_logger', '__return_true' );
``` 
