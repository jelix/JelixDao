# Hooks

JelixDao provides a mecanism to do things automatically when a factory does an 
insert/update/delete.

In the hook, you can check some domain logics, send an event using the event system of 
the application, or do any other processing.

A hook is a class, implementing the `\Jelix\Dao\DaoHookInterface`. So it has some methods
like `onInsert()`, `onUpdate`, etc. You do want you want into these methods.

Here an example:

```php

class MyHooks implements \Jelix\Dao\DaoHookInterface
{

    public function onInsert(string $daoName, DaoRecordInterface $record, $when) 
    {
        if ($daoName == 'user' && $when == \Jelix\Dao\DaoHookInterface::EVENT_AFTER) {
            // yes, this is not really recommended, but why not? :-)
            doSendMail('admin@example.com', 'a new user is declared into the database: '.$record->login);
        }
    }

    public function onUpdate(string $daoName, DaoRecordInterface $record, $when)
    {
    
    }

    public function onDelete(string $daoName, $keys, $when, $result = null)
    {
        if ($daoName == 'category' && $when == \Jelix\Dao\DaoHookInterface::EVENT_BEFORE) {
           // before deleting the category, check that it does not contain some products
           $products = new Products();
           if ($products->existsInCategory($keys[0])) {
                throw new \DomainException('The category is not empty');
           }
        }
    }

    public function onDeleteBy(string $daoName, DaoConditions $searchCond, $when, $result = null)
    {
    
    }

    public function onCustomMethod(string $daoName, string $methodName, string $methodType, $parameters, $when)
    {
    
    }
}

```

All methods accept the dao name for which a record is inserted/deleted/updated. An other
common parameters is the `$when` parameter. The methods are called before the SQL query, 
and after the SQL query. This `$when` parameter indicates the moment the method is called. Its
value can be `\Jelix\Dao\DaoHookInterface::EVENT_BEFORE` or `\Jelix\Dao\DaoHookInterface::EVENT_AFTER`.

To activate the hook, you can set it on the dao loader, so it will be used for any dao factory,
or on a specific factory, if the hook is dedicated to a dao.

```php

$hook = new MyHooks();

// the hook will be called for every dao changes
$loader->setHook($hook);

// or

$daoFact = $loader->get('foo');
// the hook will be called only on the "foo" dao changes
$daoFact->setHook($hook);

```

