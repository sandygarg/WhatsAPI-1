# WhatsAPI

**Status: development**
*You can use it just to develop on it*


## About WhatsAPI

WhatsAPI is a client library to use Whatsapp services.

This is a new project based on the original WhatsAPI:
Please see [the original project](https://github.com/venomous0x/WhatsAPI)

## Why a new project?

The original WhatsAPI library is not compatible with composer, no PSR compatible, and it's very old.
I want to develop this new library in order to make it more usable.
If you want to help, just do it :)

### The idea is: ###

Just an example:
* The client received a message.
* It's converted in a ```Node``` object, if exists could be a specific ```Node``` object, like ```Success``` node.
* One or more default listeners are attached to the ```node.received``` event. There are also specific event for each tag node. They do all the internal things, like response to system messages.
* Anyone can create a listener to do something on a certain event, like message received, presence changed, etc.

## How to start using this library

The library is not complete, you can just login and instantiate the first connection.

```php
$number   = ''; // your number
$token    = ''; // token
$nickname = ''; // your name
$password = ''; // your password

$client = new \WhatsAPI\Client\Client($number, $token, $nickname);
$client->getEventManager()->attach('*', function (\Zend\EventManager\EventInterface $e) {
        if ($e instanceof \WhatsAPI\Message\Event\NodeEvent) {
            echo $e->getName() . PHP_EOL;
            echo $e->getNode() . PHP_EOL;
            return;
        }
    });
$client->connect();
$client->loginWithPassword($password);
while (true) {
    $client->pollMessages();
}
```
