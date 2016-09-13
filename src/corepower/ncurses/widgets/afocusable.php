<?php
namespace CorePower\Ncurses\Widgets
{
    abstract class AFocusable extends AWidget implements IFocusable
    {
        private $tabIndex = 0;
        private $focus = false;

        public function setTabIndex(int $value)
        {
            $this->tabIndex = $value;
        }

        public function getTabIndex() : int
        {
            return $this->tabIndex;
        }

        public function setFocus(bool $value = true)
        {
            $this->focus = $value;
            if ($value)
                $this->gotFocus();
            else
                $this->lostFocus();
        }

        public function hasFocus() : bool
        {
            return $this->focus;
        }

        protected abstract function gotFocus();

        protected abstract function lostFocus();
    }
}
