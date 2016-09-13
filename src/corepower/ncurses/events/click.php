<?php
namespace CorePower\Ncurses\Events
{
    trait Click
    {
        protected $onClickCallables = [];

        public function click()
        {
            foreach ($this->onClickCallables as $callable)
            {
                if ($callable !== null)
                    $callable();
            }
        }

        public function onClick(callable $callable = null)
        {
            $this->onClickCallables[] = $callable;
        }
    }
}