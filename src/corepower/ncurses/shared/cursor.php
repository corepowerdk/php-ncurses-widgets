<?php
namespace CorePower\Ncurses\Shared
{
    trait Cursor
    {
        protected $prevCursState = 1;

        protected function hideCursor()
        {
            $this->prevCursState = ncurses_curs_set(0);
        }

        protected function showCursor()
        {
            ncurses_curs_set($this->prevCursState);
        }
    }
}
