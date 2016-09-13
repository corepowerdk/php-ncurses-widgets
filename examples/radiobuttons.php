#!/usr/bin/php
<?php
declare(strict_types = 1);use CorePower\Ncurses\Containers\Point;
use CorePower\Ncurses\Widgets\HLine;
use CorePower\Ncurses\Widgets\Label;
use CorePower\Ncurses\Widgets\RadioButton;
use CorePower\Ncurses\Widgets\Window;
use CorePower\Ncurses\WindowManager;

require_once __DIR__ . '/../src/init.php';

$label = new Label();
$label->setLocation(new Point(5, 3));
$label->setMultiline(true);

$radiobutton1 = new RadioButton(1, 'Radio 1');
$radiobutton1->setLocation(new Point(1, 3));
$radiobutton1->onCheckedChanged(function () use ($label, $radiobutton1)
{
    $label->appendText("\nRadio 1 checked changed to: " . ($radiobutton1->getChecked() ? 'true' : 'false'));
});

$radiobutton2 = new RadioButton(1, 'Radio 2');
$radiobutton2->setLocation(new Point(2, 3));
$radiobutton2->onCheckedChanged(function () use ($label, $radiobutton2)
{
    $label->appendText("\nRadio 2 checked changed to: " . ($radiobutton2->getChecked() ? 'true' : 'false'));
});

$radiobutton3 = new RadioButton(1, 'Radio 3');
$radiobutton3->setLocation(new Point(3, 3));
$radiobutton3->onCheckedChanged(function () use ($label, $radiobutton3)
{
    $label->appendText("\nRadio 3 checked changed to: " . ($radiobutton3->getChecked() ? 'true' : 'false'));
});

$hline = new HLine();
$hline->setLocation(new Point(20, 3));

$window = new Window('Radio buttons');
$window->addWidget($label);
$window->addWidget($radiobutton1);
$window->addWidget($radiobutton2);
$window->addWidget($radiobutton3);
//$window->addWidget($hline);

WindowManager::run($window);