<?php
namespace CorePower\Ncurses\Containers
{
    class KeyEvent
    {
        protected $key;
        protected $handled = false;

        public function __construct(int $key)
        {
            $this->key = $key;
        }

        public function getKey() : int
        {
            return $this->key;
        }

        public function setHandled(bool $handled)
        {
            $this->handled = $handled;
        }

        public function getHandled() : bool
        {
            return $this->handled;
        }

        public function __toString() : string
        {
            return '' . $this->key;
        }
    }
}