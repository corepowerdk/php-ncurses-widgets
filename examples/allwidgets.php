#!/usr/bin/php
<?php
declare(strict_types = 1);
require_once __DIR__ . '/../src/init.php';

use CorePower\Ncurses\Containers\KeyEvent;
use CorePower\Ncurses\Containers\Point;
use CorePower\Ncurses\Widgets\Button;
use CorePower\Ncurses\Widgets\CheckBox;
use CorePower\Ncurses\Widgets\HLine;
use CorePower\Ncurses\Widgets\Label;
use CorePower\Ncurses\Widgets\ProgressBar;
use CorePower\Ncurses\Widgets\RadioButton;
use CorePower\Ncurses\Widgets\Table;
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

// Label
$label = new Label('A label');
$label->setLocation(new Point(1, 3));

// Multiline label
$multilinelabel = new Label("A multiline\nlabel that span\nmultiple lines");
$multilinelabel->setMultiline(true);
$multilinelabel->setLocation(new Point(3, 3));

// Text field
$textfield = new TextField();
$textfield->setLocation(new Point(7, 3));

// Button
$button = new Button('A button - quit');
$button->setLocation(new Point(9, 3));
$button->onClick(function ()
{
    WindowManager::stop();
});

// Checkbox
$checkbox = new CheckBox(false, 'Some checkbox');
$checkbox->setLocation(new Point(11, 3));

// Progress bar
$progressbar = new ProgressBar(20);
$progressbar->setLocation(new Point(13, 3));
$progressbar->setValue(34);

// Radio buttons
const RADIO_GROUP = 1;
$radio1 = new RadioButton(RADIO_GROUP, 'Radio 1');
$radio1->setLocation(new Point(15, 3));
$radio1->onCheckedChanged(function () use ($progressbar)
{
    $progressbar->setValue(55);
});

$radio2 = new RadioButton(RADIO_GROUP, 'Radio 2');
$radio2->setLocation(new Point(16, 3));
$radio2->onCheckedChanged(function () use ($progressbar)
{
    $progressbar->setValue(12);
});

$radio3 = new RadioButton(RADIO_GROUP, 'Radio 3');
$radio3->setLocation(new Point(17, 3));
$radio3->onCheckedChanged(function () use ($progressbar)
{
    $progressbar->setValue(87);
});

// Hline
$hline = new HLine();
$hline->setLocation(new Point(19, 1));

// Table
$table = new Table('Column 1', 'Column 2');
$table->setLocation(new Point(21, 3));
for ($i = 0; $i < 3; $i++)
    $table->addRow('Data 1-' . $i, 'Data 2-' . $i);
$table->addRow('Very long data1', 'Very long data2');

// Add widgets to the window
$window->addWidget($label);
$window->addWidget($multilinelabel);
$window->addWidget($textfield);
$window->addWidget($button);
$window->addWidget($checkbox);
$window->addWidget($progressbar);
$window->addWidget($radio1);
$window->addWidget($radio2);
$window->addWidget($radio3);
$window->addWidget($hline);
$window->addWidget($table);


// Draw window and all widgets
WindowManager::run($window);
