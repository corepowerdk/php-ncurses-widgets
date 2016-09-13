<?php
namespace CorePower\Ncurses\Events
{
    trait CheckedChanged
    {
        protected $onCheckedChangedCallables = [];

        public function checkedChanged()
        {
            foreach ($this->onCheckedChangedCallables as $callable)
            {
                if ($callable !== null)
                    $callable();
            }
        }

        public function onCheckedChanged(callable $callable = null)
        {
            $this->onCheckedChangedCallables[] = $callable;
        }
    }
}