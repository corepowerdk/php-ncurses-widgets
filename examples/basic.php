#!/usr/bin/php
<?php
declare(strict_types = 1);
require_once __DIR__ . '/../src/init.php';

use CorePower\Ncurses\Containers\KeyEvent;
use CorePower\Ncurses\Containers\Point;
use CorePower\Ncurses\Widgets\Label;
use CorePower\Ncurses\Widgets\TextField;
use CorePower\Ncurses\Widgets\Window;
use CorePower\Ncurses\WindowManager;

// Window
$window = new Window('Basic test');
$window->onKeyPressed(function (KeyEvent $ke)
{
    if ($ke->getKey() === NCURSES_KEY_F1)
    {
        $ke->setHandled(true);
        WindowManager::stop();
    }
});

// Name label
$name = new Label('Enter name:');
$name->setLocation(new Point(1, 3));

// Result label
$result = new Label();
$result->setLocation(new Point(3, 3));

// Text field
$textfield = new TextField();
$textfield->setLocation(new Point(1, 15));
$textfield->onKeyPressed(function (KeyEvent $ke) use ($textfield, $result)
{
    if ($ke->getKey() === NCURSES_KEY_ALT_ENTER)
        $result->setText('Your name is: ' . $textfield->getText());
});

// Quit label
$quitLabel = new Label('Press F1 to quit');
$quitLabel->setLocation(new Point(7, 3));

// Draw
$window->addWidget($name);
$window->addWidget($result);
$window->addWidget($textfield);
$window->addWidget($quitLabel);

WindowManager::run($window);
