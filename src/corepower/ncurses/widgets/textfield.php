<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\KeyEvent;
    use CorePower\Ncurses\Containers\Point;
    use CorePower\Ncurses\Containers\Size;
    use CorePower\Ncurses\Events\KeyPressed;
    use CorePower\Ncurses\Shared\Colors;

    class TextField extends AFocusable
    {
        use KeyPressed;

        protected $text;
        protected $visibleTextPointer = 0;

        /** @var Point $cursorPoint */
        protected $cursorPoint;

        public function __construct(string $startText = '', int $width = 20)
        {
            $this->text = $startText;
            $this->size = new Size($width, 1);
        }

        public function getText() : string
        {
            return $this->text;
        }

        public function processMessage(string $message, $obj) : int
        {
            logstr('TextField processMessage: ', $message);

            if (parent::processMessage($message, $obj) === EVENT_HANDLED)
                return EVENT_HANDLED;

            if ($message !== MSG_KEY_EVENT)
                return 0;

            /** @var $obj KeyEvent */
            $key = $obj->getKey();

            switch ($key)
            {
                case NCURSES_KEY_LEFT:
                    $this->moveCursorPosition(-1);
                    logstr('key left: curX<', $this->cursorPoint->getX(), '> posX<', $this->getLeft(), '> txtPointer<', $this->visibleTextPointer, '>');
                    if ($this->visibleTextPointer > 0 && $this->cursorPoint->getX() <= $this->getLeft())
                    {
                        $this->visibleTextPointer--;
                        $this->redraw();
                        ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->cursorPoint->getX());
                    }
                    $this->refresh();
                    break;
                case NCURSES_KEY_RIGHT:
                    $text_len = strlen($this->text);
                    if ($this->cursorPoint->getX() < $this->getLeft() + $text_len)
                    {
                        $this->moveCursorPosition(1);
                        logstr('right key txtPointer<', $this->visibleTextPointer, '> curX<', $this->cursorPoint->getX(), '> txtlen<', $text_len, '> width<', $this->getWidth(), '>');
                        if ($this->visibleTextPointer <= abs($text_len - $this->getWidth()) && $this->cursorPoint->getX() >= $this->getLeft() + $this->getWidth() - 1)
                        {
                            logstr('scrolling....');
                            $this->visibleTextPointer++;
                            $this->redraw();
                            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->cursorPoint->getX());
                        }
                        $this->refresh();
                    }
                    break;
                case NCURSES_KEY_HOME:
                    $this->visibleTextPointer = 0;
                    $this->redraw();
                    ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->getLeft());
                    $this->refresh();
                    break;
                case NCURSES_KEY_END:
                    $text_len = strlen($this->text);
                    $this->visibleTextPointer = ($text_len > $this->getWidth() ? $text_len - $this->getWidth() + 1 : 0);
                    $this->redraw();
                    ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->getLeft() + min($text_len, $this->getWidth() - 1));
                    $this->refresh();
                    break;
                case NCURSES_KEY_BACKSPACE:
                case NCURSES_KEY_ALT_BACKSPACE:
                    $text_len = strlen($this->text);
                    $cursorX = $this->cursorPoint->getX();
                    if ($text_len > 0 && $cursorX <= $this->getLeft() + $text_len && $cursorX > $this->getLeft())
                    {
                        logstr('rewriting...', $this->text);
                        $firstStr = substr($this->text, 0, $this->visibleTextPointer + $this->cursorPoint->getX() - $this->getLeft() - 1);
                        $lastStr = substr($this->text, $this->visibleTextPointer + $this->cursorPoint->getX() - $this->getLeft());
                        $this->text = $firstStr . $lastStr;
                        logstr('rewrote to: ', $this->text, ' (<', $firstStr, '> <', $lastStr, '>)');
                        ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->cursorPoint->getX() - 1);
                        $this->redraw();
                        $this->refresh();
                    }
                    else
                    {
                        $this->moveCursorPosition(-1);
                    }
                    break;
                case NCURSES_KEY_DC:
                    $text_len = strlen($this->text);
                    $cursorX = $this->cursorPoint->getX();
                    if ($text_len > 0 && $cursorX <= $this->getLeft() + $text_len && $cursorX < $this->getLeft() + $text_len)
                    {
                        logstr('rewriting...', $this->text);
                        $firstStr = substr($this->text, 0, $this->visibleTextPointer + $this->cursorPoint->getX() - $this->getLeft());
                        $lastStr = substr($this->text, $this->visibleTextPointer + $this->cursorPoint->getX() - $this->getLeft() + 1);
                        $this->text = $firstStr . $lastStr;
                        logstr('rewrote to: ', $this->text, ' (<', $firstStr, '> <', $lastStr, '>)');
                        $this->redraw();
                        ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->cursorPoint->getX());
                        $this->refresh();
                    }
                    break;
                default:
                    if ($key >= 32 && $key <= 126)
                    {
                        $firstStr = substr($this->text, 0, $this->cursorPoint->getX() - $this->getLeft() + $this->visibleTextPointer);
                        $lastStr = substr($this->text, $this->cursorPoint->getX() - $this->getLeft() + $this->visibleTextPointer);
                        $this->text = $firstStr . chr($key) . $lastStr;
                        logstr('new str: <', $firstStr, '> <', $lastStr, '> <', $this->text, '>');
                        $maxX = $this->getLeft() + $this->getWidth() - 1;
                        if ($this->cursorPoint->getX() >= $maxX)
                        {
                            $newX = $maxX;
                            $this->visibleTextPointer = abs(strlen($this->text) - $this->getWidth() + 1);
                        }
                        else
                        {
                            $newX = $this->cursorPoint->getX() + 1;
                        }
                        $this->redraw();
                        ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $newX);
                        $this->refresh();
                    }
                    break;
            }

            $this->cursorPoint = Point::current($this->getUnderlyingWindow());
            $this->keyPressed($obj);

            return 0;
        }

        public function draw()
        {
            $this->cursorPoint = Point::current($this->getUnderlyingWindow());
            $this->redraw();
            $this->cursorPoint->setX($this->cursorPoint->getX() + strlen($this->text));
            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->getLeft() + $this->getWidth());
        }

        protected function redraw()
        {
            Colors::setColor($this->getUnderlyingWindow(), Colors::WHITE_BLUE);

            logstr('start index: <', $this->visibleTextPointer, '>');
            $text = substr($this->text, $this->visibleTextPointer);
            $text_c = strlen($text);
            $text = str_split($text);
            logstr('text_c <', $text_c, '> width <', $this->getWidth(), '>');
            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->getLeft());
            ncurses_wattron($this->getUnderlyingWindow(), NCURSES_A_BOLD);
            for ($i = 0; $i < $this->getWidth(); $i++)
                ncurses_waddstr($this->getUnderlyingWindow(), ($i < $text_c ? $text[$i] : ' '));
            ncurses_wattroff($this->getUnderlyingWindow(), NCURSES_A_BOLD);
            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->getLeft() + $text_c);
            Colors::reset($this->getUnderlyingWindow());
        }

        protected function gotFocus()
        {
            ncurses_wmove($this->getUnderlyingWindow(), $this->cursorPoint->getY(), $this->cursorPoint->getX());
            $this->refresh();
        }

        protected function lostFocus()
        {
        }

        protected function moveCursorPosition(int $x)
        {
            ncurses_getyx($this->getUnderlyingWindow(), $cur_y, $cur_x);
            $new_x = $cur_x + $x;
            if ($new_x < $this->getLeft())
                $new_x = $this->getLeft();
            if ($new_x >= $this->getLeft() + $this->getWidth())
                $new_x = $this->getLeft() + $this->getWidth() - 1;
            ncurses_wmove($this->getUnderlyingWindow(), $cur_y, $new_x);
        }
    }
}
