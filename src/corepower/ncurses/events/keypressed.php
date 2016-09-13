<?php
namespace CorePower\Ncurses\Events
{
    use CorePower\Ncurses\Containers\KeyEvent;

    trait KeyPressed
    {
        protected $onKeyPressedCallables = [];

        public function keyPressed(KeyEvent $keyEvent)
        {
            foreach ($this->onKeyPressedCallables as $callable)
            {
                if ($callable !== null)
                    $callable($keyEvent);
            }
        }

        public function onKeyPressed(callable $callable = null)
        {
            $this->onKeyPressedCallables[] = $callable;
        }
    }
}