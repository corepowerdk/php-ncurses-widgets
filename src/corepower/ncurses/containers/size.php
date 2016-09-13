<?php
namespace CorePower\Ncurses\Containers
{
    use CorePower\Ncurses\Events\SizeChanged;

    class Size
    {
        use SizeChanged;

        protected $width;
        protected $height;

        public function __construct(int $width, int $height)
        {
            $this->setWidth($width);
            $this->setHeight($height);
        }

        public function getWidth() : int
        {
            return $this->width;
        }

        public function setWidth(int $width)
        {
            if ($width < 0)
                throw new \InvalidArgumentException('Width cannot be negative');
            if ($width === $this->width)
                return;

            $this->width = $width;
            $this->sizeChanged();
        }

        public function getHeight() : int
        {
            return $this->height;
        }

        public function setHeight(int $height)
        {
            if ($height < 0)
                throw new \InvalidArgumentException('Height cannot be negative');
            if ($height === $this->height)
                return;

            $this->height = $height;
            $this->sizeChanged();
        }

        public function toPoint() : Point
        {
            return new Point($this->getWidth(), $this->getHeight());
        }

        public function equals(Size $s) : bool
        {
            return $this->getHeight() === $s->getHeight() && $this->getWidth() === $s->getWidth();
        }

        public function __toString()
        {
            return '{Size: width=' . $this->getWidth() . ', height=' . $this->getHeight() . '}';
        }
    }
}
