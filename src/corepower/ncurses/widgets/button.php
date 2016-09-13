<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\KeyEvent;
    use CorePower\Ncurses\Containers\Size;
    use CorePower\Ncurses\Events\Click;
    use CorePower\Ncurses\Shared\Colors;
    use CorePower\Ncurses\Shared\Cursor;

    class Button extends AFocusable
    {
        use Click, Cursor;

        protected $text;

        public function __construct(string $text, int $width = WIDGET_AUTO_SIZE)
        {
            $this->text = $text;
            $this->size = new Size(($width === WIDGET_AUTO_SIZE ? strlen($text) : $width) + 2, 1);
        }

        public function processMessage(string $message, $obj) : int
        {
            if (parent::processMessage($message, $obj) === EVENT_HANDLED)
                return EVENT_HANDLED;

            if ($message === MSG_KEY_EVENT)
            {
                /** @var $obj KeyEvent */
                switch ($obj->getKey())
                {
                    case NCURSES_KEY_ALT_ENTER:
                    case NCURSES_KEY_ENTER:
                    case NCURSES_KEY_SPACEBAR:
                        $this->click();
                        break;
                }
            }

            return 0;
        }

        public function draw()
        {
            ncurses_wattron($this->getUnderlyingWindow(), NCURSES_A_BOLD);
            if ($this->hasFocus())
                Colors::setColor($this->getUnderlyingWindow(), Colors::WHITE_BLUE);

            ncurses_waddstr($this->getUnderlyingWindow(), '<' . str_pad($this->text, $this->getWidth() - 2, ' ', STR_PAD_BOTH) . '>');
            if ($this->hasFocus())
                Colors::reset($this->getUnderlyingWindow());
            ncurses_wattroff($this->getUnderlyingWindow(), NCURSES_A_BOLD);
        }

        protected function gotFocus()
        {
            $this->draw();
            $this->hideCursor();
            $this->refresh();
        }

        protected function lostFocus()
        {
            $this->showCursor();
            $this->draw();
            $this->refresh();
        }
    }
}
