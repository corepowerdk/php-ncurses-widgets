<?php
namespace CorePower\Ncurses\Events
{
    interface IPointChanged
    {
        public function pointChanged();

        public function onPointChanged(callable $callable = null);
    }

    trait PointChanged
    {
        protected $pointChangedCallables = [];

        public function pointChanged()
        {
            foreach ($this->pointChangedCallables as $callable)
            {
                if ($callable !== null)
                    $callable();
            }
        }

        public function onPointChanged(callable $callable = null)
        {
            $this->pointChangedCallables[] = $callable;
        }
    }
}