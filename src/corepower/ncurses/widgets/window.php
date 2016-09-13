<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\KeyEvent;
    use CorePower\Ncurses\Containers\Point;
    use CorePower\Ncurses\Events\KeyPressed;

    class Window extends Panel
    {
        use KeyPressed;

        public function __construct(string $title, int $top = 0, int $left = 0, int $height = WIDGET_FILL, int $width = WIDGET_FILL, bool $border = true)
        {
            parent::__construct($height, $width, $border);
            $this->title = $title;
            $this->setLocation(new Point($top, $left));
        }

        public function recreateWindow()
        {
            parent::recreateWindow();
            ncurses_keypad($this->window, true);
        }

        public function processMessage(string $message, $obj) : int
        {
            if ($message === MSG_KEY_EVENT)
            {
                /** @var KeyEvent $obj */
                $this->keyPressed($obj);
                if ($obj->getHandled())
                    return 0;
            }

            return parent::processMessage($message, $obj);
        }
    }
}
