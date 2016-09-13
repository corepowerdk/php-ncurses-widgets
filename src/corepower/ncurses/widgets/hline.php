<?php
namespace CorePower\Ncurses\Widgets
{
    use CorePower\Ncurses\Containers\Size;

    class HLine extends AWidget
    {
        protected $autoSize = false;

        public function __construct(int $width = WIDGET_AUTO_SIZE)
        {
            if ($width === WIDGET_AUTO_SIZE)
                $this->autoSize = true;

            $this->size = new Size($width, 1);
        }

        public function getSize() : Size
        {
            if ($this->autoSize && $this->size->getWidth() === WIDGET_AUTO_SIZE)
                $this->size->setWidth($this->getParent()->getWidth() - $this->getLeft());
            return parent::getSize();
        }

        public function draw()
        {
            ncurses_whline($this->getUnderlyingWindow(), NCURSES_ACS_HLINE, $this->getWidth());
        }
    }
}
