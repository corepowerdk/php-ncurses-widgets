<?php
namespace CorePower\Ncurses\Widgets
{
    interface IFocusable extends IWidget
    {
        public function setFocus(bool $value);

        public function hasFocus() : bool;

        public function setTabIndex(int $index);

        public function getTabIndex() : int;
    }
}