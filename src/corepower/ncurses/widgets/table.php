<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\KeyEvent;
    use CorePower\Ncurses\Containers\Point;
    use CorePower\Ncurses\Containers\Size;
    use CorePower\Ncurses\Shared\Colors;
    use CorePower\Ncurses\Shared\Cursor;

    class Table extends AFocusable
    {
        use Cursor;

        protected $columns;
        protected $columnHeaders = [];
        protected $crossColumnPositions = [];
        protected $rows = [];
        protected $drawn = false;
        protected $currentFocusLine = 0;

        public function __construct(string ... $columnHeaders)
        {
            $this->columns = count($columnHeaders);
            $this->columnHeaders = $columnHeaders;

            $columnWidth = 0;
            foreach ($this->columnHeaders as $header)
                $columnWidth += strlen($header) + 2;
            $this->size = new Size($columnWidth + $this->columns - 1, $this->getHeaderHeight() + 1);

            for ($i = 0; $i < $this->columns; $i++)
            {
                $len = strlen($this->columnHeaders[$i]) + 2;
                if ($i > 0)
                    $len += $this->crossColumnPositions[$i - 1] + 1;
                $this->crossColumnPositions[] = $len;
            }

            logstr('Got columns, width<', $this->getWidth(), '> cols<', $this->columns, '> headers<', $this->columnHeaders, '>');
        }

        public function processMessage(string $message, $obj) : int
        {
            if ($message === MSG_KEY_EVENT)
            {
                /** @var KeyEvent $obj */
                switch ($obj->getKey())
                {
                    case NCURSES_KEY_DOWN:
                        $rowCount = count($this->rows);
                        if ($this->currentFocusLine !== $rowCount - 1)
                        {
                            $oldFocusLine = $this->currentFocusLine;
                            if (++$this->currentFocusLine >= $rowCount)
                                $this->currentFocusLine = $rowCount - 1;

                            $this->ensureVisibility();
                            $this->drawRow($oldFocusLine);
                            $this->drawRow($this->currentFocusLine);
                            $this->refresh();

                            return EVENT_HANDLED;
                        }
                        break;

                    case NCURSES_KEY_UP:
                        if ($this->currentFocusLine !== 0)
                        {
                            $oldFocusLine = $this->currentFocusLine;
                            if (--$this->currentFocusLine < 0)
                                $this->currentFocusLine = 0;

                            $this->ensureVisibility();
                            $this->drawRow($oldFocusLine);
                            $this->drawRow($this->currentFocusLine);
                            $this->refresh();

                            return EVENT_HANDLED;
                        }
                        break;
                }
            }

            return parent::processMessage($message, $obj);
        }

        protected function ensureVisibility()
        {
            $parentHeight = $this->getParent()->getHeight() - ($this->getParent()->getBorder() ? 2 : 0);
            $viewport = $this->getParent()->getViewportArea();
            $linePos = $this->getTop() + $this->currentFocusLine + $this->getHeaderHeight();

            if ($linePos >= $parentHeight + $viewport->getY() - 1)
            {
                // TODO Find the row to scroll to
                $this->getParent()->scrollTo(new Point($viewport->getY() + 1, $viewport->getX()));
            }
            else if ($linePos <= $viewport->getY())
            {
                // TODO Find the row to scroll to
                $curLineDiff = ($this->currentFocusLine === 0) ? $this->getHeaderHeight() : 1;
                $this->getParent()->scrollTo(new Point($viewport->getY() - $curLineDiff, $viewport->getX()));
            }
        }

        public function addRow(string ... $cells)
        {
            $c_count = count($cells);
            $row = [];
            for ($i = 0; $i < $this->columns; $i++)
            {
                logstr('adding cell: <', ($i < $c_count ? $cells[$i] : ''), '>');
                $row[] = ($i < $c_count ? $cells[$i] : '');
            }
            $this->rows[] = $row;
            $this->getSize()->setHeight($this->getHeight() + 1);

            if ($this->drawn)
                $this->draw();
        }

        public function draw()
        {
            $this->drawHeaders();
            $this->drawRows();
            $this->drawTail();
            $this->drawn = true;
        }

        protected function drawHeaders()
        {
            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop(), $this->getLeft());

            // Draw upper line in header
            ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_ULCORNER);
            for ($i = 0; $i < $this->getWidth(); $i++)
            {
                $char = ($this->columns > 1 && in_array($i, $this->crossColumnPositions, true)) ? NCURSES_ACS_TTEE : NCURSES_ACS_HLINE;
                ncurses_waddch($this->getUnderlyingWindow(), $char);
            }
            ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_URCORNER);

            // Draw column headers
            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop() + 1, $this->getLeft());
            ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_VLINE);
            foreach ($this->columnHeaders as $header)
            {
                ncurses_waddstr($this->getUnderlyingWindow(), " $header ");
                ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_VLINE);
            }

            // Draw bottom line in header
            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop() + 2, $this->getLeft());
            ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_LTEE);

            for ($i = 0; $i < $this->getWidth(); $i++)
            {
                if ($this->columns > 1 && in_array($i, $this->crossColumnPositions, true))
                {
                    ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_PLUS);
                }
                else
                {
                    ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_HLINE);
                }
            }
            ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_RTEE);
        }

        protected function drawRows()
        {
            $currentYPos = $this->getTop() + $this->getHeaderHeight();
            for ($i = 0, $r_c = count($this->rows); $i < $r_c; $i++)
            {
                ncurses_wmove($this->getUnderlyingWindow(), $currentYPos++, $this->getLeft());
                ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_VLINE);
                $this->drawRow($i);
                ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_VLINE);
            }
        }

        protected function drawRow(int $rowNumber)
        {
            if ($rowNumber < 0 || $rowNumber >= count($this->rows))
                return;

            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop() + $this->getHeaderHeight() + $rowNumber, $this->getLeft() + 1);

            if ($rowNumber === $this->currentFocusLine && $this->hasFocus())
                Colors::setColor($this->getUnderlyingWindow(), Colors::WHITE_BLUE);

            $row = $this->rows[$rowNumber];

            for ($j = 0; $j < $this->columns; $j++)
            {
                $maxCellWidth = strlen($this->columnHeaders[$j]) + 2;
                $rowCellWidth = strlen($row[$j]);

                if ($rowCellWidth <= $maxCellWidth)
                {
                    ncurses_waddstr($this->getUnderlyingWindow(), $row[$j]);
                    ncurses_waddstr($this->getUnderlyingWindow(), str_repeat(' ', $maxCellWidth - $rowCellWidth));
                }
                else
                {
                    ncurses_waddstr($this->getUnderlyingWindow(), substr($row[$j], 0, $maxCellWidth - 1));
                    ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_RARROW);
                }

                if ($j < $this->columns - 1)
                    ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_VLINE);
            }

            if ($rowNumber === $this->currentFocusLine && $this->hasFocus())
                Colors::reset($this->getUnderlyingWindow());
        }

        protected function drawTail()
        {
            $numRows = count($this->rows);
            ncurses_wmove($this->getUnderlyingWindow(), $this->getTop() + $this->getHeaderHeight() + $numRows, $this->getLeft());
            ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_LLCORNER);

            for ($i = 0; $i < $this->getWidth(); $i++)
            {
                $char = ($this->columns > 1 && in_array($i, $this->crossColumnPositions, true)) ? NCURSES_ACS_BTEE : NCURSES_ACS_HLINE;
                ncurses_waddch($this->getUnderlyingWindow(), $char);
            }
            ncurses_waddch($this->getUnderlyingWindow(), NCURSES_ACS_LRCORNER);
        }

        protected function getHeaderHeight()
        {
            return 3;
        }

        protected function gotFocus()
        {
            $this->hideCursor();
            $this->refreshFocusLine();
        }

        protected function lostFocus()
        {
            $this->showCursor();
            $this->refreshFocusLine();
        }

        protected function refreshFocusLine()
        {
            if (count($this->rows) > 0)
            {
                $this->drawRow($this->currentFocusLine);
                $this->refresh();
            }
            $this->ensureVisibility();
        }
    }
}