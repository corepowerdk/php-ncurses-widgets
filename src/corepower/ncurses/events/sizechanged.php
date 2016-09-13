<?php
namespace CorePower\Ncurses\Events
{
    interface ISizeChanged
    {
        public function sizeChanged();

        public function onSizeChanged(callable $callable = null);
    }

    trait SizeChanged
    {
        protected $sizeChangedCallables = [];

        public function sizeChanged()
        {
            foreach ($this->sizeChangedCallables as $callable)
            {
                if ($callable !== null)
                    $callable();
            }
        }

        public function onSizeChanged(callable $callable = null)
        {
            $this->sizeChangedCallables[] = $callable;
        }
    }
}